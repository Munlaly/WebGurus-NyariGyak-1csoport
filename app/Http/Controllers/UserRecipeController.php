<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recipe;
use Illuminate\Support\Facades\Storage;

class UserRecipeController extends Controller
{

    public function index(Request $request) {
        $user = $request->user();
        $recipes = Recipe::where('user_id', $user->id)->with('ingredients')->get();
        return response()->json($recipes);
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'instructions' => 'required|string',
            'prep_time_minutes' => 'required|integer|min:1',
            'is_public' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // 2MB max
            'calories' => 'required|integer|min:0',
            'protein' => 'required|numeric|min:0',
            'fat' => 'required|numeric|min:0',
            'carbs' => 'required|numeric|min:0',
            'meal_types' => 'required|array',

            'ingredients' => 'required|array|min:1',
            'ingredients.*.id' => 'required|integer|exists:ingredients,id',
            'ingredients.*.amount' => 'required|numeric|min:0',
            'ingredients.*.unit'=> 'nullable|string|max:50',
        ]);

        $imagePath = null;
        if($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('private_recipes', 'public');
        }

        $recipe = Recipe::create([
            'user_id' => $request->user()->id,
            'name' => $validated['name'],
            'instructions' => $validated['instructions'],
            'prep_time_minutes' => $validated['prep_time_minutes'],
            'is_public' => $validated['is_public'] ?? false,
            'image' => $imagePath,
            'calories' => $validated['calories'],
            'protein' => $validated['protein'],
            'fat' => $validated['fat'],
            'carbs' => $validated['carbs'],
            'meal_types' => $validated['meal_types'],
        ]);

        if(!empty($validated['ingredients'])) {
            $ingredientData = [];
            foreach($validated['ingredients'] as $ingredient) {
                $ingredientData[$ingredient['id']] = [
                    'amount' => $ingredient['amount'],
                    'unit' => $ingredient['unit'] ?? null,
                ];
            }
            $recipe->ingredients()->sync($ingredientData);
        }

        $recipe->load('ingredients');

        return response()->json([
            'success' => true,
            'message' => 'Recipe created successfully.',
            'data' => $recipe
        ], 201);
    }

    public function update(Request $request, $id) {
        $recipe = Recipe::where('user_id', $request->user()->id)->findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'instructions' => 'sometimes|required|string',
            'prep_time_minutes' => 'sometimes|required|integer|min:1',
            'is_public' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'calories' => 'sometimes|required|integer|min:0',
            'protein' => 'sometimes|required|numeric|min:0',
            'fat' => 'sometimes|required|numeric|min:0',
            'carbs' => 'sometimes|required|numeric|min:0',
            'meal_types' => 'sometimes|required|array',

            'ingredients' => 'sometimes|required|array|min:1',
            'ingredients.*.id' => 'required|exists:ingredients,id',
            'ingredients.*.amount' => 'required|numeric|min:0',
            'ingredients.*.unit'=> 'nullable|string|max:50',
        ]);

        if($request->hasFile('image')) {
            if($recipe->image) {
                Storage::delete($recipe->image);
            }
            $recipe->image = $request->file('image')->store('private_recipes', 'public');
        }

        $updateData = collect($validated)->except([
            'id',
            'user_id',
            'created_at',
            'image',
            'ingredients',
        ])->toArray();

        $recipe->update($updateData);

        if($request->has('ingredients')) {
            $ingredientData = [];
            foreach($validated['ingredients'] as $ingredient) {
                $ingredientData[$ingredient['id']] = [
                    'amount' => $ingredient['amount'],
                    'unit' => $ingredient['unit'] ?? null,
                ];
            }
            $recipe->ingredients()->sync($ingredientData);
        }

        $recipe->load('ingredients');

        return response()->json([
            'success' => true,
            'message' => 'Recipe updated successfully.',
            'data' => $recipe,
        ]);
    }
}
