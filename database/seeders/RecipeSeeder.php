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
        if(!Storage::disk('local')->exists('spoonacular_recipes.json')){
            $this->command->error('No JSON file found. Run the fetch command first!');
            return;
        }
        $json = Storage::disk('local')->get('spoonacular_recipes.json');

        
        $recipeData = json_decode($json, true);
        $uncategorized = Category::where('name', 'Uncategorized')->first();

        foreach($recipeData as $data) {
            $rawImage = $data['image'] ?? null;
            $cleanImage = $rawImage ? str_replace(['\\/', '\\'], ['', ''], $rawImage) : null;

            $calories = $data['macros']['calories'] ?? null;
            $fat = $data['macros']['fat'] ?? null;
            $carbs = $data['macros']['carbs'] ?? null;
            $protein = $data['macros']['protein'] ?? null;

            $spoonacularDiets = $data['diets'] ?? [];
            $standardizedDiets = [];
            foreach($spoonacularDiets as $diet) {
                $diet = strtolower($diet);

                if(str_contains($diet, 'vegan')) {
                    $standardizedDiets[] = 'vegan';
                    $standardizedDiets[] = 'vegetarian';
                    $standardizedDiets[] = 'dairy_free';
                }
                if(str_contains($diet, 'vegetarian')) {
                    $standardizedDiets[] = 'vegetarian';
                }
                if(str_contains($diet, 'dairy free')) {
                    $standardizedDiets[] = 'dairy_free';
                }
                if(str_contains($diet, 'gluten free')) {
                    $standardizedDiets[] = 'gluten_free';
                }
                if(str_contains($diet,'keto') || str_contains($diet, 'ketogenic')) {
                    $standardizedDiets[] = 'keto';
                }
                if (str_contains($diet, 'pescatarian')) {
                    $standardizedDiets[] = 'pescatarian';
                }
            }
            

            $recipe = Recipe::firstOrCreate(
                ['name' => $data['title']],
                [
                    'user_id' => null,
                    'instructions' => $data['instructions'],
                    'prep_time_minutes' => $data['prep_time'],
                    'image' => $cleanImage,
                    'is_public' => true,
                    'calories' => $calories !== null ? (int) round($calories): null,
                    'protein'=> $protein,
                    'fat' => $fat,
                    'carbs'=> $carbs,
                    'meal_types' => $data['meal_types'] ?? [],
                    'diets' => array_unique($standardizedDiets),
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

                    $rawUnit = $ingData['unit'] ?? null;    

                    $ingredient = Ingredient::firstOrCreate(
                        ['name' => $ingredientName],
                        [
                            'category_id' => $categoryId,
                            'base_unit' => !empty($rawUnit) ? strtolower(trim($rawUnit)) : 'pcs',
                        ]
                    );

                    RecipeIngredient::updateOrCreate(
                        [
                            'recipe_id' => $recipe->id,
                            'ingredient_id' => $ingredient->id,
                        ],
                        [
                            'amount' => $ingData['amount'] ?? 0,
                            'unit'   => !empty($rawUnit) ? trim($rawUnit) : 'pcs',
                        ]
                    );
                }
            }
        }
        $this->command->info('Recipes and Ingredients seeded succesfully');
    }
}