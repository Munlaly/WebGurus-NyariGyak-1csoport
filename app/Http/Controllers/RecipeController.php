<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recipe;
use App\Models\Ingredient;
use Inertia\Inertia;
use Inertia\Response;

class RecipeController extends Controller
{
    public function show(Request $request, Recipe $recipe): Response
    {
        $userId = $request->user()?->id;
        $isAuthor = $userId && $recipe->user_id === $userId;
        $isAccessible = $recipe->is_public || is_null($recipe->user_id) || $isAuthor;

        if (! $isAccessible) {
            abort(404); 
        }
        // get all related ingredients
        $recipe->load(['ingredients' => function ($query) {
            $query->withPivot('raw_amount', 'raw_unit');
        }]);

        $instructionsArray = array_values(array_filter(array_map(
            fn($step) => preg_replace('/^\d+\.\s*/', '', trim($step)), 
            explode("\n", $recipe->instructions)
        )));

        $formattedIngredients = $recipe->ingredients->map(function ($ingredient) {
            /** @var \App\Models\Ingredient $ingredient */
            return [
                'name' => $ingredient->name,
                'amount' => (float) $ingredient->pivot->raw_amount,
                'unit' => $ingredient->pivot->raw_unit 
            ];
        });

        return Inertia::render('Recipe', [
            'recipe' => [
                'id' => $recipe->id,
                'title' => $recipe->name,
                'prepTime' => $recipe->prep_time_minutes,
                'calories' => $recipe->calories,
                'imageUrl' => $recipe->image ? $this->getRecipeImageUrl($recipe->image) : null,
                'imageAlt' => $recipe->name,
                'macros' => [
                    'protein' => (float) $recipe->protein,
                    'carbs' => (float) $recipe->carbs,
                    'fat' => (float) $recipe->fat,
                ],
                'ingredients' => $formattedIngredients,
                'instructions' => $instructionsArray,
                'is_custom' => $isAuthor,
                'meal_types' =>$recipe->meal_types, 
            ]
        ]);
    }
}