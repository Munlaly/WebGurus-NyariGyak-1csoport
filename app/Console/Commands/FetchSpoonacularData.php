<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\Recipe;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class FetchSpoonacularData extends Command
{
    protected $signature = 'spoonacular:fetch';
    protected $description = 'Sequentually fetches recipees from Spoonacular and saves unique ones to JSON and DB ';
    public function handle()
    {
        $this->info('Starting fetch...');

        $limit = 50;
        $apiKey = config('services.spoonacular.key');

        if(!$apiKey) {
            $this->error('Spoonacular API key is missing in .env');
            return self::FAILURE;    
        }

        $offset = Cache::get('spoonacular_fetch_offset', 0);
        $this->info("Current API offset: {$offset}. Fetching the next {$limit} recipes...");

        // Fetch from API
        $response = Http::get("https://api.spoonacular.com/recipes/complexSearch", [
            'apiKey' => $apiKey,
            'number' => $limit,
            'offset' => $offset,
            'addRecipeInformation' => 'true',
            'instructionsRequired' => 'true',
            'fillIngredients' => 'true',
            'addRecipeNutrition' => 'true',
        ]);

        if($response->failed()) {
            $this->error('Failed to connect to Spoonacular API');
            Log::error('Spoonacular API Error', ['response' => $response->body()]);
            return self::FAILURE;
        }

        $recipes = $response->json('results');

        if(empty($recipes)) {
            $this->warn('No recipes returned from API');
            return self::SUCCESS;
        }

        $titles = array_column($recipes, 'title');

        $existingRecipeNames = Recipe::whereIn('name', $titles)->pluck('name')->toArray();

        $newRecipes = [];
        $saveCount = 0;

        foreach($recipes as $recipeData) {
            $mappedTypes = [];
            $apiTypes = $recipeData['dishTypes'] ?? [];
            
            foreach ($apiTypes as $type) {
                $type = strtolower($type);
                
                if (in_array($type, ['breakfast', 'brunch', 'morning meal'])) {
                    $mappedTypes[] = 'breakfast';
                }
                if (in_array($type, ['main course', 'main dish', 'dinner', 'lunch'])) {
                    $mappedTypes[] = 'lunch';
                    $mappedTypes[] = 'dinner';
                }
                if(in_array($type, ['snack', 'appetizer', 'fingerfood', 'starter'])) {
                    $mappedTypes[] = 'snack';
                }
            }
            
            $mappedTypes = array_unique($mappedTypes);
            if (empty($mappedTypes)) {
                continue;
            }
            
            $finalMealTypes = array_values($mappedTypes);

            $exist = in_array($recipeData['title'], $existingRecipeNames);
            $instructionsText = null;
            if(!empty($recipeData['analyzedInstructions'])) {
                $stepDescriptions = [];
                foreach($recipeData['analyzedInstructions'] as $instructionBlock) {
                    if(!empty($instructionBlock['steps'])) {
                        foreach($instructionBlock['steps'] as $step) {
                            $stepDescriptions[] = $step['number'] . '. ' . $step['step'];
                        }
                    }
                }
                $instructionsText = implode("\n", $stepDescriptions);
            }

            $macros = [
                'calories' => null,
                'protein' => null,
                'fat' => null,
                'carbs' => null,
            ];

            if(!empty($recipeData['nutrition']['nutrients'])) {
                foreach($recipeData['nutrition']['nutrients'] as $nutrient) {
                    match($nutrient['name']) {
                        'Calories' => $macros['calories'] = $nutrient['amount'],
                        'Protein' => $macros['protein'] = $nutrient['amount'],
                        'Fat' => $macros['fat'] = $nutrient['amount'],
                        'Carbohydrates' => $macros['carbs'] = $nutrient['amount'],
                        default => null,
                    };
                }
            }

            $finalInstructions = $instructionsText
                ?? $recipeData["instructions"]
                ?? $recipeData["summary"]
                ?? 'No instructions provided';
                
            // Since the API doesn't have a nut-free dietary option it is provided manually

            $currentDiets = $recipeData['diets'] ?? [];
            $nutKeyWords = ['nut', 'nuts', 'peanut' , 'peanuts', 'almond', 'almonds', 'cashew', 'cashews', 'walnut', 'walnuts', 'pecan', 'pecans', 'hazelnut', 'hazelnuts',
                            'macademia', 'macademias', 'pistachio', 'pistachios'];

            $isNutFree = true;

            // check ingredient list
            if(!empty($recipeData['extendedIngredients'])) {
                foreach($recipeData['extendedIngredients'] as $ingredient) {
                    $ingredientName = strtolower($ingredient['name'] ?? '');
                    foreach($nutKeyWords as $nut) {
                        if(preg_match('/\b' . preg_quote($nut, '/') . '\b/i', $ingredientName)) {
                            $isNutFree = false;
                            break 2;
                        }
                    } 
                }
            }

            // check title and instructions
            if($isNutFree) {
                $recipeText = strtolower($recipeData['title'] . ' ' . $finalInstructions);
                foreach($nutKeyWords as $nut) {
                    if(preg_match('/\b' . preg_quote($nut, '/') . '\b/i', $recipeText)) {
                            $isNutFree = false;
                            break;
                        }
                }
            }

            if($isNutFree && !in_array('nut free', $currentDiets)) {
                $currentDiets[] = 'nut free';
            }

            if(!$exist) {
                // Saving to DB
                $rawImage = $recipeData['image'] ?? null;
                $cleanImage = $rawImage ? str_replace(['\\/', '\\'], ['', ''], $rawImage) : null;
                
                Recipe::create([
                    'user_id' => null, // no user for system generated recipes
                    'name' => $recipeData['title'],
                    'instructions' => $finalInstructions,
                    'prep_time_minutes' => $recipeData['readyInMinutes'] ?? null,
                    'image' => $cleanImage,
                    'is_public' => true, // public by default
                    'calories' => $macros['calories'] !== null ? (int) round($macros['calories']) : null,
                    'protein' => $macros['protein'] !== null ? (float) $macros['protein'] : null,
                    'fat' => $macros['fat'] !== null ? (float) $macros['fat'] : null,
                    'carbs' => $macros['carbs'] !== null ? (float) $macros['carbs'] : null,
                    'diets' => $currentDiets,
                ]);
                $newRecipes[] = [
                    'title' => $recipeData['title'],
                    'prep_time' => $recipeData['readyInMinutes'] ?? null,
                    'servings' => $recipeData['servings'] ?? null,
                    'macros' => $macros,
                    'diets' => $currentDiets,
                    'instructions' => $finalInstructions,
                    'meal_types' => $finalMealTypes,
                    'image' => $cleanImage,
                    'ingredients' => array_map(fn($ingr) => [
                            'name' => $ingr['nameClean'] ?? null,
                            'amount' => $ingr['amount'] ?? null,
                            'unit' => $ingr['unit'] ?? null,
                            'aisle' => $ingr['aisle'] ?? 'Uncategorized',
                    ], $recipeData['extendedIngredients'] ?? []),
                ];
                $saveCount++;
            }
        }
        
        // Append to JSON file
        if(!empty($newRecipes)) {
            $this->appendToJsonFile($newRecipes);
            $this->info("Succesfully saved {$saveCount} new unique recipes");
        } else {
            $this->info('No new data added');
        }
        Cache::put('spoonacular_fetch_offset', $offset + $limit);
        return self::SUCCESS;
    }

    private function appendToJsonFile(array $newRecipes) {
        $fileName = 'spoonacular_recipes.json';
        $disk = Storage::disk('local'); // storage/app/spoonacular_recipes.json

        $existingData = [];

        if($disk->exists($fileName)) {
            $fileContent = $disk->get($fileName);
            $existingData = json_decode($fileContent, true) ?? [];
        }

        $existinTitles = array_column($existingData, 'title');

        foreach($newRecipes as $newRecipe) {
            if(!in_array($newRecipe['title'], $existinTitles)) {
                $existingData[] = $newRecipe;
            }
        }
        
        $disk->put($fileName, json_encode($existingData, JSON_PRETTY_PRINT));
    }
}
