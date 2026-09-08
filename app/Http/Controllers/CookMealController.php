<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Recipe;
use App\Models\UserInventory;
use App\Models\DailyPlan;
use App\Models\ShoppingListItem;

class CookMealController extends Controller
{
    public function cook(Request $request, int $recipeId) {
        $recipe = Recipe::with('ingredients')->findOrFail($recipeId);
        $user = $request->user();

        $isConfirmed = $request->boolean('confirmed', false);
        $userSettings = DB::table('user_settings')->where('user_id', $user->id)->first();
        $scale = $userSettings ? (int) $userSettings->household_size : 1;

        return DB::transaction(function() use ($recipe, $user, $scale, $isConfirmed, $request) {
            $missingIngredients = [];
            $mismatchedUnits = [];
            $availableIngredients = [];
            $itemsToProcess = [];
            $usedIngredients = [];

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
                            'id' => $recipeIngredient->id,
                            'ingredient' => $recipeIngredient->name,
                            'recipe_amount' => $requiredAmount,
                            'recipe_unit' => $requiredUnit,
                            'user_amount' => $inventoryItems->sum('amount_left'),
                            'user_unit' => $firstItemUnit,
                        ];
                        if(!$isConfirmed) {
                            continue;
                        } else {
                            $mismatchedOverrides = $request->input('mismatch_overrides', []);
                            if(array_key_exists($recipeIngredient->id, $mismatchedOverrides)) {
                                $remainingAmount = max(0, (float) $mismatchedOverrides[$recipeIngredient->id]);
                                $firstItem = $inventoryItems->first();

                                if($remainingAmount == 0) {
                                    foreach($inventoryItems as $item) {
                                        $item->delete();
                                    }
                                    $usedIngredients[] = $recipeIngredient->id . '(Finished mismatched batch)';
                                } else {
                                    $firstItem->update(
                                        [
                                            'amount_left' => $remainingAmount,
                                            'status' => 'OPENED',
                                        ]
                                    );
                                    foreach($inventoryItems->skip(1) as $item) {
                                        $item->delete();
                                    }
                                    $usedIngredients[] = $recipeIngredient->id . '(Manually resolved mismatch)';
                                }
                            }
                            continue;
                        }
                    }
                }

                $totalAvailable = $inventoryItems->sum('amount_left');

                $ingredientDetails = [
                        'ingredient' => $recipeIngredient->name ?? 'Unknown Ingredient',
                        'required' => $requiredAmount,
                        'available' => $totalAvailable,
                        'unit' => $requiredUnit,
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

    public function addMissingToShoppingList(Request $request, int $recipeId) {
        $recipe = Recipe::with('ingredients')->findOrFail($recipeId);
        $user = $request->user();

        $userSettings = DB::table('user_settings')->where('user_id', $user->id)->first();
        $scale = $userSettings ? (int) $userSettings->household_size : 1;

        $addedCount = 0;

        foreach($recipe->ingredients as $recipeIngredient) {
            $baseAmount = $recipeIngredient->pivot->amount ?? 1;
            $requiredUnit = $recipeIngredient->pivot->unit ?? 'pcs';
            $requiredAmount = $baseAmount * $scale;

            $inventoryItems = UserInventory::where('user_id', $user->id)
                ->where('ingredient_id', $recipeIngredient->id)
                ->get();

            $totalAvailable = 0;
            $hasMismatch = false;

            if($inventoryItems->isNotEmpty()) {
                $firstItemUnit = $inventoryItems->first()->unit;
                if($firstItemUnit !== $requiredUnit) {
                    $hasMismatch = true;
                }
                $totalAvailable = $inventoryItems->sum('amount_left');
            }

            if($hasMismatch || $totalAvailable < $requiredAmount) {
                $roundedQuantity = ceil($requiredAmount);

                $shoppingListItem = ShoppingListItem::where('user_id', $user->id)
                    ->where('ingredient_id', $recipeIngredient->id)
                    ->where('is_checked', false)
                    ->first();

                if ($shoppingListItem) {
                    if ($shoppingListItem->quantity < $roundedQuantity) {
                        $shoppingListItem->update([
                            'quantity' => $roundedQuantity,
                            'unit' => $requiredUnit
                        ]);
                    }
                } else {
                    ShoppingListItem::create([
                        'user_id' => $user->id,
                        'ingredient_id' => $recipeIngredient->id,
                        'quantity' => $roundedQuantity,
                        'unit' => $requiredUnit,
                        'is_checked' => false
                    ]);
                }
                $addedCount++;
            }
        }

        if ($addedCount === 0) {
            return response()->json([
                'success' => true,
                'message' => 'You already have all the ingredients for this recipe!'
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => "Added $addedCount missing ingredients to your shopping list!"
        ]);
    }
}