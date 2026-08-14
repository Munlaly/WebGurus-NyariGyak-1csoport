<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Recipe;
use App\Models\UserInventory;
class CookMealController extends Controller
{
    public function cook(Request $request, $recipeId) {
        $recipe = Recipe::with('ingredients')->findOrFail($recipeId);
        $user = $request->user();

        $userSettings = DB::table('user_settings')->where('user_id', $user->id)->first();
        $scale = $userSettings ? (int) $userSettings->household_size : 1;

        $missingIngredients = [];
        $availableIngredients = [];

        foreach($recipe->ingredients as $recipeIngredient) {
            $baseAmount = $recipeIngredient->pivot->amount ?? 1;
            $requiredAmount = $baseAmount * $scale;

            $totalAvailable = UserInventory::where('user_id', $user->id)
                ->where('ingredient_id', $recipeIngredient->id)
                ->sum('amount_left');

            $ingredientDetails = [
                    'ingredient' => $recipeIngredient->name ?? 'Unknown Ingredient',
                    'required' => $requiredAmount,
                    'available' => $totalAvailable
                ];           

            if($totalAvailable < $requiredAmount) {
                $missingIngredients[] = $ingredientDetails;
            } else {
                $availableIngredients[] = $ingredientDetails;
            }
        }
        
        if(!empty($missingIngredients)) {
            return response()->json([
                'success' => false,
                'message' => 'Not enough ingredients for this meal!',
                'summary' => [
                    'have' => $availableIngredients,
                    'missing' => $missingIngredients
                ]
            ], 400);
        }

        $usedIngredients = [];
        DB::transaction(function() use ($recipe, $user, $scale, &$usedIngredients) {
            foreach($recipe->ingredients as $recipeIngredient) {
                $baseAmount = $recipeIngredient->pivot->amount ?? 1;
                $remainingToDeduct = $baseAmount * $scale;
                $inventoryItem = UserInventory::where('user_id', $user->id)
                    ->where('ingredient_id', $recipeIngredient->id)
                    ->orderBy('expiration_date', 'asc')
                    ->lockForUpdate()
                    ->get();

                foreach($inventoryItem as $item) {
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
        });
        return response()->json([
            'success' => true,
            'message' => 'Meal cooked! Inventory has been automatically updated.',
            'details' => $usedIngredients
        ]);
    }
}