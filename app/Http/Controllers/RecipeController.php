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
        $recipe->load('ingredients');

        $instructionsArray = array_values(array_filter(array_map(
            fn($step) => preg_replace('/^\d+\.\s*/', '', trim($step)), 
            explode("\n", $recipe->instructions)
        )));

        $formattedIngredients = $recipe->ingredients->map(fn(Ingredient $ingredient) => [
            'name' => $ingredient->name,
            'amount' => (float) $ingredient->pivot->amount,
            'unit' => $ingredient->pivot->unit
        ]);

        $imageUrl = 'https://placehold.co/600x400?text=No+Image';

        if ($recipe->image) {
            // If it's an external Spoonacular link, use it directly. 
            // Otherwise, treat it as a local upload and wrap it in asset().
            $imageUrl = str_starts_with($recipe->image, 'http') 
                ? $recipe->image 
                : asset('storage/' . $recipe->image);
        }

        return Inertia::render('Recipe', [
            'recipe' => [
                'id' => $recipe->id,
                'title' => $recipe->name,
                'prepTime' => $recipe->prep_time_minutes,
                'calories' => $recipe->calories,
                'imageUrl' => $imageUrl,
                'imageAlt' => $recipe->name,
                'macros' => [
                    'protein' => (float) $recipe->protein,
                    'carbs' => (float) $recipe->carbs,
                    'fat' => (float) $recipe->fat,
                ],
                'ingredients' => $formattedIngredients,
                'instructions' => $instructionsArray,
            ]
        ]);
    }
}