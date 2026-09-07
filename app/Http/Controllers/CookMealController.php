<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Recipe;
use App\Models\UserInventory;
use App\Models\DailyPlan;

class CookMealController extends Controller
{
    public function cook(Request $request, int $recipeId) {
        $recipe = Recipe::with('ingredients')->findOrFail($recipeId);
        $user = $request->user();

        $isConfirmed = $request->boolean('confirmed', false);
        $userSettings = DB::table('user_settings')->where('user_id', $user->id)->first();
        $scale = $userSettings ? (int) $userSettings->household_size : 1;

        return DB::transaction(function() use ($recipe, $user, $scale, $isConfirmed) {
            $missingIngredients = [];
            $mismatchedUnits = [];
            $availableIngredients = [];
            $itemsToProcess = [];

            foreach($recipe->ingredients as $recipeIngredient) {
                /** @var \App\Models\Ingredient $recipeIngredient */
                $baseAmount = $recipeIngredient->pivot->amount ?? 1;
                $requiredUnit = $recipeIngredient->pivot->unit ?? 'pcs';
                $requiredAmount = $baseAmount * $scale;

                $inventoryItems = UserInventory::where('user_id', $user->id)
                    ->where('ingredient_id', $recipeIngredient->id)
                    ->orderBy('expiration_date', 'asc')
                    ->lockForUpdate()
                    ->get();

                if($inventoryItems->isNotEmpty()) {
                    $firstItemUnit = $inventoryItems->first()->unit;
                    if($firstItemUnit !== $requiredUnit) {
                        $mismatchedUnits[] = [
                            'ingredient' => $recipeIngredient->name,
                            'recipe_requires' => $requiredAmount . ' ' . $requiredUnit,
                            'user_has' => $inventoryItems->sum('amount_left') . ' ' . $firstItemUnit,
                        ];
                        if(!$isConfirmed) {
                            continue;
                        }
                    }
                }

                $totalAvailable = $inventoryItems->sum('amount_left');

                $ingredientDetails = [
                        'ingredient' => $recipeIngredient->name ?? 'Unknown Ingredient',
                        'required' => $requiredAmount,
                        'available' => $totalAvailable
                    ];           

                if($totalAvailable < $requiredAmount) {
                    $missingIngredients[] = $ingredientDetails;
                    if(!$isConfirmed) {
                        continue;
                    }
                }
                
                $availableIngredients[] = $ingredientDetails;
                $itemsToProcess[] = [
                    'items' => $inventoryItems,
                    'remainingToDeduct' => min($requiredAmount, $totalAvailable),
                ];
            }

            if((!empty($missingIngredients) || !empty($mismatchedUnits)) && !$isConfirmed) {
                return response()->json([
                    'success' => false,
                    'requires_confirmation' => true,
                    'message' => 'Some ingredients have shortages or unit mismatches. Do you still want to cook?',
                    'summary' => [
                        'have' => $availableIngredients,
                        'missing' => $missingIngredients,
                        'mismatched' => $mismatchedUnits,
                    ]
                ], 200);
            }

            $usedIngredients = [];
            foreach($itemsToProcess as $processData) {
                $remainingToDeduct = $processData['remainingToDeduct'];

                foreach($processData['items'] as $item) {
                    /** @var UserInventory $item */
                    if ($remainingToDeduct <= 0) {
                        break; 
                    }

                    if ($item->amount_left <= $remainingToDeduct) {
                        $remainingToDeduct -= $item->amount_left;
                        $item->delete();
                        $usedIngredients[] = $item->ingredient_id . ' (Finished batch)';
                    } else {
                        $item->update([
                            'amount_left' => $item->amount_left - $remainingToDeduct,
                            'status' => 'OPENED'
                        ]);
                        $remainingToDeduct = 0; 
                        $usedIngredients[] = $item->ingredient_id . ' (Reduced batch)';
                    }
                }
            }

            $dailyPlan = DailyPlan::where('user_id', $user->id)
                ->whereDate('date', now()->toDateString())
                ->first();

            if(!$dailyPlan) {
                return response()->json([
                    'success' => false,
                    'message' => 'No meal plan found for today, so this meal cannot be marked as cooked.',
                ], 404);
            }

            $dailyPlan->mealPlans()
                ->where('recipe_id', $recipe->id)
                ->update(['status' => 'EATEN']);

            return response()->json([
                'success' => true,
                'message' => 'Meal is cooked! Inventory automatically updated!',
                'details' => $usedIngredients,
            ]);
        });
    }

    public function toggleFavorite(Request $request, int $recipeId) {
        $request->user()->favoriteRecipes()->toggle($recipeId);
        return response()->json([
            'success' => true,
            'message' => 'Favorite toggled succesfully.',
        ]);
    }
}