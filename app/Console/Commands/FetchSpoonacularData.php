<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\Recipe;
use Illuminate\Support\Facades\Log;

##[Signature('app:fetch-spoonacular-data')]
##[Description('Command description')]
class FetchSpoonacularData extends Command
{
    /**
     * Execute the console command.
     */

    protected $signature = 'spoonacular:fetch';
    protected $description = 'Sequentually fetches recipees from Spoonacular and saves unique ones to JSON and DB ';
    public function handle()
    {
        $this->info('Starting fetch...');

        $limit = 50;
        $apiKey = env('SPOONACULAR_API_KEY');

        if(!$apiKey) {
            $this->error('Spoonacular API key is missing in .env');
            return Command::FAILURE;    
        }

        $offset = Recipe::whereNull('user_id')->count();
        $this->info("Current database count: {$offset}. Fetching the next {$limit} recipes...");

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
            return Command::FAILURE;
        }

        $recipes = $response->json('results');

        if(empty($recipes)) {
            $this->warn('No recipes returned from API');
            return Command::SUCCESS;
        }

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
            }
            
            $mappedTypes = array_unique($mappedTypes);
            if (empty($mappedTypes)) {
                continue; 
            }
            
            $finalMealTypes = array_values($mappedTypes);

            $exist = Recipe::where('name', $recipeData['title'])->exists();
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
                

            if(!$exist) {
                // Saving to DB
                Recipe::create([
                    'user_id' => null, // no user for system generated recipes
                    'name' => $recipeData['title'],
                    'instructions' => $finalInstructions,
                    'prep_time_minutes' => $recipeData['readyInMinutes'] ?? null,
                    'is_public' => true, // public by default
                ]);
                $newRecipes[] = [
                    'title' => $recipeData['title'],
                    'prep_time' => $recipeData['readyInMinutes'] ?? null,
                    'servings' => $recipeData['servings'] ?? null,
                    'macros' => $macros,
                    'diets' => $recipeData['diets'] ?? null,
                    'instructions' => $finalInstructions,
                    'meal_types' => $finalMealTypes,
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
        return Command::SUCCESS;
    }

    private function appendToJsonFile(array $newRecipes) {
        $fileName = 'spoonacular_recipes.json';
        $disk = Storage::disk('local'); // storage/app/spoonacular_recipes.json

        $existingData = [];

        if($disk->exists($fileName)) {
            $fileContent = $disk->get($fileName);
            $existingData = json_decode($fileContent, true) ?? [];
        }

        $mergeData = array_merge($existingData, $newRecipes);
        
        $disk->put($fileName, json_encode($mergeData, JSON_PRETTY_PRINT));
    }
}
