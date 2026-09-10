<?php

namespace App\Http\Controllers;

use App\Models\DietaryOption;
use Illuminate\Http\Request;
use App\Models\Recipe;
use App\Models\Ingredient;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class UserRecipeController extends Controller
{
    public function index(Request $request) {
        $user = $request->user();
        
        $myRecipes = Recipe::where('user_id', $user->id)->with('ingredients')->get();
        $favoriteRecipes = $user->favoriteRecipes()->with('ingredients')->get();
        $allIngredients = Ingredient::select('id', 'name', 'base_unit', 'emoji')->orderBy('name')->get();
        $dietaryOptions = DietaryOption::select('id', 'name', 'description')->get();

        return Inertia::render('Recipes', [
            'myRecipes' => $myRecipes,
            'favoriteRecipes' => $favoriteRecipes,
            'ingredients' => $allIngredients,
            'dietaryOptions' => $dietaryOptions,
        ]);
    }

    public function store(Request $request) {
        $validated = $this->validateRecipe($request);

        $imagePath = null;
        if($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('private_recipes', 'public');
        }

        $recipe = Recipe::create(array_merge($validated, [
            'user_id' => $request->user()->id,
            'image' => $imagePath,
            'is_public' => $request->boolean('is_public', false),
        ]));
        
        $this->syncIngredients($recipe, $request->input('ingredients', []));

        return back()->with('success', 'Recipe created successfully!');
    }

    public function update(Request $request, int $id) {
        $recipe = Recipe::where('user_id', $request->user()->id)->findOrFail($id);
        
        $validated = $this->validateRecipe($request);
        $updateData = $validated;

        if($request->hasFile('image')) {
            if($recipe->image) Storage::disk('public')->delete($recipe->image);
            $updateData['image'] = $request->file('image')->store('private_recipes', 'public');
        } else {
            unset($updateData['image']);
        }

        $recipe->update($updateData);
        $this->syncIngredients($recipe, $request->input('ingredients', []));

        return back()->with('success', 'Recipe updated successfully!');
    }

    public function destroy(Request $request, int $id) {
        $recipe = Recipe::where('user_id', $request->user()->id)->findOrFail($id);
        
        if($recipe->image) Storage::disk('public')->delete($recipe->image);
        $recipe->delete();

        return back()->with('success', 'Recipe deleted successfully.');
    }

    private function validateRecipe(Request $request) {
        return $request->validate([
            'name' => 'required|string|max:255',
            'instructions' => 'required|string',
            'prep_time_minutes' => 'required|integer|min:1',
            'calories' => 'required|integer|min:0',
            'protein' => 'required|numeric|min:0',
            'fat' => 'required|numeric|min:0',
            'carbs' => 'required|numeric|min:0',
            'meal_types' => 'required|array|min:1',
            'diets' => [
                'present',
                'array',
                function($attribute, $value, $fail) {
                    $selectedSlugs = \App\Models\DietaryOption::whereIn('id', $value)->pluck('name')->map(fn($n) => strtolower($n))->toArray();
                    $baseDiets = ['vegan', 'vegetarian', 'pescatarian', 'omnivore'];
                    $selectedBaseDiets = array_intersect($baseDiets, $selectedSlugs);

                    if (count($selectedBaseDiets) > 1) {
                        $fail('You cannot select conflicting baseline diets.');
                    }
                }
            ],
            'diets.*' => 'integer|exists:dietary_options,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.id' => 'required|exists:ingredients,id',
            'ingredients.*.amount' => 'required|numeric|min:0.1',
            'ingredients.*.unit'=> 'required|string|max:10',
            'ingredients.*.raw_amount' => 'nullable|numeric|min:0.1',
            'ingredients.*.raw_unit' => 'nullable|string|max:10',
        ]);
    }

    private function syncIngredients(Recipe $recipe, array $ingredients) {
        $ingredientData = [];
        foreach($ingredients as $ingredient) {
            $amount = $ingredient['amount'] ?? 1;
            $unit = $ingredient['unit'] ?? 'pcs';
            $rawAmount = !empty($ingredient['raw_amount']) ? $ingredient['raw_amount'] : $amount;
            $rawUnit = !empty($ingredient['raw_unit']) ? $ingredient['raw_unit'] : $unit;
            $ingredientData[$ingredient['id']] = [
                'amount' => $amount,
                'unit' => $unit,
                'raw_amount' => $rawAmount,
                'raw_unit' => $rawUnit,
            ];
        }
        $recipe->ingredients()->sync($ingredientData);
    }
}