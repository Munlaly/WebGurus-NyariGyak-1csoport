<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use App\Models\Recipe;
use App\Models\Ingredient;
use App\Models\Category;
use App\Models\RecipeIngredient;

class RecipeSeeder extends Seeder {
    public function run() {
        $json = Storage::disk('local')->get('spoonacular_recipes.json');

        if(!$json) {
            $this->command->error('No JSON file found. Run the fetch command first!');
            return;
        }

        $recipeData = json_decode($json, true);
        $uncategorized = Category::where('name', 'Uncategorized')->first();

        foreach($recipeData as $data) {
            $recipe = Recipe::firstOrCreate(
                ['name' => $data['title']],
                [
                    'user_id' => null,
                    'instructions' => $data['instructions'],
                    'prep_time_minutes' => $data['prep_time'],
                    'is_public' => true,
                ]
            );

            if(!empty($data['ingredients'])) {
                foreach($data['ingredients'] as $ingData) {
                    $category = Category::where('name', $ingData['aisle'] ?? 'Uncategorized')->first();
                    $categoryId = $category ? $category->id : $uncategorized->id;

                    $ingredientName = strtolower(trim($ingData['name']));

                    // 1. Remove anything before a colon
                    if (strpos($ingredientName, ':') !== false) {
                        $parts = explode(':', $ingredientName);
                        $ingredientName = end($parts); 
                    }
                    // 2. Remove stray parentheses
                    $ingredientName = str_replace(['(', ')'], '', $ingredientName);
                    // 3. REGEX: Remove asterisks and any numbers following them (fixes "*1", "*2")
                    $ingredientName = preg_replace('/\*[0-9]+/', '', $ingredientName);
                    // 4. REGEX: Remove leading prepositions like "of " at the very start of the string
                    $ingredientName = preg_replace('/^of\s+/', '', $ingredientName);
                    // 5. Final trim to catch any leftover spaces
                    $ingredientName = trim($ingredientName);

                    $ingredient = Ingredient::firstOrCreate(
                        ['name' => $ingredientName],
                        [
                            'category_id' => $categoryId,
                            'base_unit' => $this->mapUnit($ingData['unit']),
                        ]
                    );

                    RecipeIngredient::updateOrCreate(
                        [
                            'recipe_id' => $recipe->id,
                            'ingredient_id' => $ingredient->id,
                        ],
                        [
                            'amount' => $ingData['amount'] ?? 0
                        ]
                    );
                }
            }
        }
        $this->command->info('Recipes and Ingredients seeded succesfully');
    }

    private function mapUnit($unit) {
        $unit = strtolower(trim($unit));

        if(in_array($unit, ['cup', 'cups', 'tbsp', 'tsp', 'tablespoon', 'teaspoon', 'ml', 'l', 'fluid ounce'])) {
            return 'ml';
        }
        if (in_array($unit, ['g', 'gram', 'grams', 'oz', 'ounce', 'ounces', 'lb', 'pound', 'pounds'])) {
            return 'g';
        }

        return 'db';
    }
}