<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use App\Models\Recipe;
use App\Models\Ingredient;
use App\Models\Category;
use App\Models\RecipeIngredient;
use App\Services\IngredientService;

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
                $ingredientService = app(IngredientService::class);
                foreach($data['ingredients'] as $ingData) {
                    $category = Category::where('name', $ingData['aisle'] ?? 'Uncategorized')->first();
                    $categoryId = $category ? $category->id : $uncategorized->id;

                    $rawName = $ingData['nameClean'] ?? $ingData['name'] ?? '';
                    $cleanName = $ingredientService->sanitizeName($rawName);
                    
                    if (str_word_count($cleanName) > 6) {
                        continue;
                    }

                    $rawUnit = $ingData['unit'] ?? '';
                    $rawAmount = (float) ($ingData['amount'] ?? 0);

                    $baseMetricUnit = $ingredientService->getBaseMetricUnit($rawUnit);
                    $metricAmount = $ingredientService->convertToBaseAmount($rawAmount, $rawUnit);

                    $ingredient = Ingredient::firstOrCreate(
                        ['name' => $cleanName],
                        [
                            'category_id' => $categoryId,
                            'base_unit' => $baseMetricUnit,
                        ]
                    );

                    RecipeIngredient::updateOrCreate(
                        [
                            'recipe_id' => $recipe->id,
                            'ingredient_id' => $ingredient->id,
                        ],
                        [
                            'amount' => $metricAmount,
                            'unit'   => $baseMetricUnit,
                            'raw_amount' => $rawAmount,
                            'raw_unit' => $rawUnit,
                        ],
                    );
                }
            }
        }
        $this->command->info('Recipes and Ingredients seeded succesfully');
    }
}