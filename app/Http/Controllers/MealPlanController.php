<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
<<<<<<< HEAD
use App\Models\UserSetting;
use App\Models\Recipe;
use Illuminate\Support\Facades\DB;
use App\Models\MealPlan;
use App\Models\User;
use Illuminate\Support\Carbon;

class MealPlanController extends Controller
{
    private function getFilteredRecipes(int $userId, ?UserSetting $settings) {
        $dislikedIngredientIds = DB::table('user_disliked_ingredients')
            ->where('user_id', $userId)
            ->pluck('ingredient_id')
            ->toArray();

            $validRecipes = Recipe::with('ingredients');

            if(!empty($dislikedIngredientIds)) {
                $validRecipes->whereDoesntHave('ingredients', function ($query) use ($dislikedIngredientIds) {
                    $query->whereIn('ingredients.id', $dislikedIngredientIds);
                });
            }

            if($settings && $settings->prep_time_preference) {
                $validRecipes->where(function($query) use ($settings) {
                    $query->where('prep_time_minutes', '<=', (int) $settings->prep_time_preference)
                        ->orWhereNull('prep_time_minutes');
                });
            }

            if($settings && !empty($settings->meal_plan_preference) && !in_array('omnivore', $settings->meal_plan_preference)) {
                $preferences = $settings->meal_plan_preference;

                if(in_array('nut_free', $preferences)) {
                    $validRecipes->whereJsonContains('diets', 'nut free');
                    $preferences = array_diff($preferences, ['nut_free']);
                }
                if(!empty($preferences)) {
                    $validRecipes->where(function($query) use ($preferences) {
                        foreach($preferences as $diet) {
                            $query->whereJsonContains('diets', $diet);
                        }
                    });
                }
            }

            return $validRecipes;
    }

    public function generate(Request $request) {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $settings = UserSetting::where('user_id', $user->id)->first();

        // PHASE 1: HARD FILTERS

        $validRecipes = $this->getFilteredRecipes($user->id, $settings);
=======
use App\Models\UserSettings;
use App\Models\Recipe;
use Illuminate\Support\Facades\DB;

class MealPlanController extends Controller
{
    public function generate(Request $request) {
        $user = $request->user();

        $settings = UserSettings::where('user_id', $user->id)->first();

        $dislikedIngredientIds = DB::table('user_disliked_ingredients')
            ->where('user_id', $user->id)
            ->pluck('ingredient_id')
            ->toArray();

        // PHASE 1: HARD FILTERS

        $validRecipes = Recipe::query();

        if(!empty($dislikedIngredientIds)) {
            $validRecipes->whereDoesntHave('ingredients', function($query) use ($dislikedIngredientIds) {
                $query->whereIn('ingredients.id', $dislikedIngredientIds);
            });
        }

        if($settings && $settings->prep_time_preference) {
            $validRecipes->where(function($query) use ($settings) {
                $query->where('prep_time_minutes', '<=', (int) $settings->prep_time_preference)
                      ->orWhereNull('prep_time_minutes');
            });
        }
>>>>>>> b5e6b4f ("Add meal plan generator")

        $poolOfAllowedMeals = $validRecipes->get();

        if($poolOfAllowedMeals->count() < 3) {
            return response()->json([
                'success' => false,
                'message' => 'Your filters are too strict! We could not find enough meals.',
                'found' => $poolOfAllowedMeals->count()
            ], 400);
        }

        // PHASE 1.5: GOAL-BASED PRUNING

        $goals = $settings->goals ?? [];
        $cutoffThreshold = (int) ($poolOfAllowedMeals->count() * 0.70); // Keep the top 70 %

        if (in_array('build_muscle', $goals) && $cutoffThreshold >= 3) {
            // sort by highest protein, keep the top 70%, and re-index the collection
            $poolOfAllowedMeals = $poolOfAllowedMeals->sortByDesc('protein')->take($cutoffThreshold)->values();
        }
<<<<<<< HEAD

        $cutoffThreshold = (int) ($poolOfAllowedMeals->count() * 0.70);
=======
>>>>>>> b5e6b4f ("Add meal plan generator")
        
        if(in_array('eat_healthy', $goals) && $cutoffThreshold >= 3) {
            // sort by lowest fat, keep the top 70%, and re-index the collection
            $poolOfAllowedMeals = $poolOfAllowedMeals->sortBy('fat')->take($cutoffThreshold)->values();
        }

<<<<<<< HEAD
        // PHASE 2: CALORIE & ZERO-WASTE ENGINE
=======
        // PHASE 2: CALORIE COMBINER
>>>>>>> b5e6b4f ("Add meal plan generator")

        $breakfasts = $poolOfAllowedMeals->filter(fn($recipe) => is_array($recipe->meal_types) && in_array('breakfast', $recipe->meal_types));
        $lunches = $poolOfAllowedMeals->filter(fn($recipe) => is_array($recipe->meal_types) && in_array('lunch', $recipe->meal_types));
        $dinners = $poolOfAllowedMeals->filter(fn($recipe) => is_array($recipe->meal_types) && in_array('dinner', $recipe->meal_types));
<<<<<<< HEAD
        $snacks = $poolOfAllowedMeals->filter(fn($recipe) => is_array($recipe->meal_types) && in_array('snack', $recipe->meal_types));
=======
>>>>>>> b5e6b4f ("Add meal plan generator")

        if($breakfasts->isEmpty() || $lunches->isEmpty() || $dinners->isEmpty()) {
            return response()->json([
                'success'=> false,
                'message' => 'Your filters are too strict! We could not find enough meals for every cathegory.',
                'summary' => [
                    'breakfasts_fount' => $breakfasts->count(),
                    'lunches_found' => $lunches->count(),
                    'dinners_found' => $dinners->count(),
<<<<<<< HEAD
                    'snacks_found' => $snacks->count(),
=======
>>>>>>> b5e6b4f ("Add meal plan generator")
                ]
            ], 400);
        }

        $targetCalories = $settings->daily_calorie_target ?? 2000;

        $minCalories = $targetCalories * 0.85;
        $maxCalories = $targetCalories * 1.15;

        $weeklyPlan = [];
        $allDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

<<<<<<< HEAD
        $currentDayName = Carbon::now()->format('l');
        $currentIndex = (int) array_search($currentDayName, $allDays);
        $days = array_slice($allDays, $currentIndex);

        $weeklyActiveIngredients = [];
        $weeklyIngredientQuantities = [];

        $snackChancePercentage = 40;

=======
        $currentDayName = \Carbon\Carbon::now()->format('l');
        $currentIndex = array_search($currentDayName, $allDays);
        $currentIndex = $currentIndex !== false ? $currentIndex : 0;
        $days = array_slice($allDays, $currentIndex);

>>>>>>> b5e6b4f ("Add meal plan generator")
        foreach($days as $day) {
            $dailyMeals = null;
            $bestAttempt = null;
            $closestDifference = 99999;

            $attempts = 0;
            $maxAttempts = 150;  // don't let the server loop forever

<<<<<<< HEAD
            $zeroWasteScorer = function($meal) use ($weeklyActiveIngredients) {
                if(empty($weeklyActiveIngredients)) {
                    return mt_rand(1, 100); // Randomize the first day
                }

                $score = 0;
                /** @var \App\Models\Ingredient $ingredient */
                foreach($meal->ingredients as $ingredient) {
                    if(in_array($ingredient->id, $weeklyActiveIngredients)) {
                        $score += 15; // Reward for using an ingredient already in the plan
                
                        $amount = (float) ($ingredient->pivot->amount ?? 1);
                        $score += $amount;
                    }
                }
                return $score;
            };


            $includeSnack = $snacks->isNotEmpty() && (mt_rand(1, 100) <= $snackChancePercentage);
            $snack = $includeSnack ? $snacks->sortByDesc($zeroWasteScorer)->first() : null;
            $snackCalories = $snack ? (int) $snack->calories : 0;

=======
>>>>>>> b5e6b4f ("Add meal plan generator")
            $fillerMeal = mt_rand(0, 2);
            while($attempts < $maxAttempts) {
                $b = null;
                $l = null;
                $d = null;
                $currentCalories = 0;

                if($fillerMeal === 0) {
                    $l = $lunches->random();
                    $d = $dinners->random();
<<<<<<< HEAD
                    $currentCalories = $l->calories + $d->calories + $snackCalories;
=======
                    $currentCalories = $l->calories + $d->calories;
>>>>>>> b5e6b4f ("Add meal plan generator")

                    $neededMin = $minCalories - $currentCalories;
                    $neededMax = $maxCalories - $currentCalories;

                    $perfectMeals = $breakfasts->where('calories', '>=', $neededMin)
                                               ->where('calories', '<=', $neededMax);

                    if($perfectMeals->isNotEmpty()) {
<<<<<<< HEAD
                        $b = $perfectMeals->sortByDesc($zeroWasteScorer)->first();
                        $dailyMeals = collect(array_filter([$b, $l, $d, $snack]));
=======
                        $b = $perfectMeals->random();
                        $dailyMeals = collect([$b, $l, $d]);
>>>>>>> b5e6b4f ("Add meal plan generator")
                        break;
                    }
                    $b = $breakfasts->random();
                } else if($fillerMeal === 1) {
                    $b = $breakfasts->random();
                    $d = $dinners->random();
<<<<<<< HEAD
                    $currentCalories = $b->calories + $d->calories + $snackCalories;
=======
                    $currentCalories = $b->calories + $d->calories;
>>>>>>> b5e6b4f ("Add meal plan generator")

                    $neededMin = $minCalories - $currentCalories;
                    $neededMax = $maxCalories - $currentCalories;

                    $perfectMeals = $lunches->where('calories', '>=', $neededMin)
                                            ->where('calories', '<=', $neededMax);

                    if($perfectMeals->isNotEmpty()) {
<<<<<<< HEAD
                        $l = $perfectMeals->sortByDesc($zeroWasteScorer)->first();
                        $dailyMeals = collect(array_filter([$b, $l, $d, $snack]));
=======
                        $l = $perfectMeals->random();
                        $dailyMeals = collect([$b, $l, $d]);
>>>>>>> b5e6b4f ("Add meal plan generator")
                        break;
                    }
                    $l = $lunches->random();
                } else {
                    $b = $breakfasts->random();
                    $l = $lunches->random();
<<<<<<< HEAD
                    $currentCalories = $b->calories + $l->calories + $snackCalories;
=======
                    $currentCalories = $b->calories + $l->calories;
>>>>>>> b5e6b4f ("Add meal plan generator")

                    $neededMin = $minCalories - $currentCalories;
                    $neededMax = $maxCalories - $currentCalories;

                    $perfectMeals = $dinners->where('calories', '>=', $neededMin)
                                               ->where('calories', '<=', $neededMax);

                    if($perfectMeals->isNotEmpty()) {
<<<<<<< HEAD
                        $d = $perfectMeals->sortByDesc($zeroWasteScorer)->first();
                        $dailyMeals = collect(array_filter([$b, $l, $d, $snack]));
=======
                        $d = $perfectMeals->random();
                        $dailyMeals = collect([$b, $l, $d]);
>>>>>>> b5e6b4f ("Add meal plan generator")
                        break;
                    }
                    $d = $dinners->random();
                }

<<<<<<< HEAD
                $testTotal = $b->calories + $l->calories + $d->calories + $snackCalories;
=======
                $testTotal = $b->calories + $l->calories + $d->calories;
>>>>>>> b5e6b4f ("Add meal plan generator")
                $difference = abs($testTotal - $targetCalories);

                if($difference < $closestDifference) {
                    $closestDifference = $difference;
<<<<<<< HEAD
                    $bestAttempt = collect(array_filter([$b, $l, $d, $snack]));
=======
                    $bestAttempt = collect([$b, $l, $d]);
>>>>>>> b5e6b4f ("Add meal plan generator")
                }
                $attempts++;
            }

            if(!$dailyMeals) {
                $dailyMeals = $bestAttempt;
            }

<<<<<<< HEAD
            foreach($dailyMeals as $meal) {
                /** @var \App\Models\Ingredient $ingredient */
                foreach($meal->ingredients as $ingredient) {
                    $ingId = $ingredient->id;
                    $amount = (float) ($ingredient->pivot->amount ?? 0);

                    if(!in_array($ingId, $weeklyActiveIngredients)) {
                        $weeklyActiveIngredients[] = $ingId;
                        $weeklyIngredientQuantities[$ingId] = 0;
                    }
                    $weeklyIngredientQuantities[$ingId] += $amount;
                }
            }

            $weeklyActiveIngredients = array_unique($weeklyActiveIngredients);

            $weeklyPlan[$day] = [
                'meals' => $dailyMeals->values(),
                'total_calories' => $dailyMeals->sum('calories'),
                'has_snack' => $includeSnack,
=======
            $weeklyPlan[$day] = [
                'meals' => $dailyMeals,
                'total_calories' => $dailyMeals->sum('calories'),
>>>>>>> b5e6b4f ("Add meal plan generator")
                'perfect_match' => $attempts < $maxAttempts
            ];
        }

        return response()->json([
            'success' => true,
<<<<<<< HEAD
            'message' => 'Weekly plan succesfully generated.',
=======
            'message' => 'Weekly plan succesfully generated. ',
>>>>>>> b5e6b4f ("Add meal plan generator")
            'target_calories' => $targetCalories,
            'plan' => $weeklyPlan,
        ]);
    }

    public function regenerateMeal(Request $request) {
<<<<<<< HEAD
        /** @var \App\Models\User $user */
        $user = $request->user();
        $settings = UserSetting::where('user_id', $user->id)->first();

        $mealType = $request->input('meal_type');

        if(!in_array($mealType, ['breakfast', 'lunch', 'dinner', 'snack'])) {
=======
        $user = $request->user();
        $settings = UserSettings::where('user_id', $user->id)->first();

        $mealType = $request->input('meal_type');

        if(!in_array($mealType, ['breakfast', 'lunch', 'dinner'])) {
>>>>>>> b5e6b4f ("Add meal plan generator")
            return response()->json([
                'success' => false,
                'message' => 'Invalid meal type provided.',
            ], 400);
        }

<<<<<<< HEAD
        $validRecipes = $this->getFilteredRecipes($user->id, $settings);
=======
        $dislikedIngredientIds = DB::table('user_disliked_ingredients')
            ->where('user_id', $user->id)
            ->pluck('ingredient_id')
            ->toArray();

        $validRecipes = Recipe::query();

        if(!empty($dislikedIngredientIds)) {
            $validRecipes->whereDoesntHave('ingredients', function($query) use ($dislikedIngredientIds) {
                $query->whereIn('ingredients.id', $dislikedIngredientIds);
            });
        }

        if($settings && $settings->prep_time_preference) {
            $validRecipes->where(function($query) use ($settings) {
                $query->where('prep_time_minutes', '<=', (int) $settings->prep_time_preference)
                      ->orWhereNull('prep_time_minutes');
            });
        }
>>>>>>> b5e6b4f ("Add meal plan generator")

        $poolOfAllowedMeals = $validRecipes->get();

        $filteredPool = $poolOfAllowedMeals->filter(fn($recipe) 
            => is_array($recipe->meal_types) && in_array($mealType, $recipe->meal_types));

        if($filteredPool->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No available meals found for this category matching your filters',
                'found' => $poolOfAllowedMeals->count()
            ], 400);
        }

        $newMeal = $filteredPool->random();
        return response()->json([
            'success'=> true,
            'recipe' => $newMeal
        ]);
    }

    public function savePlan(Request $request) {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $validated = $request->validate([
            'plan' => 'required|array',
            'plan.*.meals' => 'required|array',
            'plan.*.meals.*.id' => 'required|integer|exists:recipes,id',

        ]);

        $plan = $validated['plan'];
        $startOfWeek = Carbon::now()->startOfWeek();

        $dayMapping = [
            'Monday' => 0,
            'Tuesday' => 1,
            'Wednesday' => 2,
            'Thursday' => 3,
            'Friday' => 4,
            'Saturday' => 5,
            'Sunday' => 6
        ];

        $mealTypesArray = ['breakfast', 'lunch', 'dinner', 'snack'];
    
        DB::transaction(function () use ($user, $plan, $startOfWeek, $dayMapping, $mealTypesArray) {
            // Delete old drafts
            MealPlan::where('user_id', $user->id)
                ->where('status', 'DRAFT')
                ->delete();

            // Insert new plan
            foreach($plan as $dayName => $dayData) {
                $dayOffset = $dayMapping[$dayName] ?? 0;
                $scheduledDate = $startOfWeek->copy()->addDays($dayOffset)->toDateString();

                foreach($dayData['meals'] as $index => $meal) {
                    $mealType = $meal['meal_type'] ?? ($mealTypesArray[$index] ?? 'snack');

                    MealPlan::create([
                        'user_id' => $user->id,
                        'recipe_id' => $meal['id'],
                        'scheduled_date' => $scheduledDate,
                        'meal_type' => $mealType,
                        'status' => 'DRAFT',
                    ]);
                }
            }
        });
        return response()->json([
            'success' => true,
            'message' => 'Weekly plan saved to your clendar succesfully!'
        ]);     
    }
}
