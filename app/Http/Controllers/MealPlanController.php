<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserSetting;
use App\Models\Recipe;
use Illuminate\Support\Facades\DB;
use App\Models\MealPlan;
use App\Models\User;
use Illuminate\Support\Carbon;

class MealPlanController extends Controller
{
<<<<<<< HEAD
    private function getFilteredRecipes(int $userId, ?UserSetting $settings) {
=======
    public function generate(Request $request) {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $settings = UserSettings::where('user_id', $user->id)->first();

>>>>>>> 3e27079 (fix: finalize MealPlanController logic and types)
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

        if($settings && !empty($settings->meal_plan_preference) && !in_array('omnivore', $settings->meal_plan_preference)) {
            $preferences = $settings->meal_plan_preference;

            if(in_array('nut_free', $preferences)) {
                $validRecipes->where(function($q) {
                    $q->whereDoesntHave('ingredients', function($subQ) {
                        $subQ->where('name', 'like', '%nut%')
                              ->orWhere('name', 'like', '%peanut%')
                              ->orWhere('name', 'like', '%almond%')
                              ->orWhere('name', 'like', '%cashew%')
                              ->orWhere('name', 'like', '%walnut%')
                              ->orWhere('name', 'like', '%pecan%')
                              ->orWhere('name', 'like', '%hazelnut%')
                              ->orWhere('name', 'like', '%macadamia%')
                              ->orWhere('name', 'like', '%pistachio%');
                    })
                        ->where('title', 'not like', '% nut %')
                        ->where('title', 'not like', '%nuts%')
                        ->where('title', 'not like', '%peanut%')
                        ->where('title', 'not like', '%almond%')
                        ->where('title', 'not like', '%cashew%')
                        ->where('title', 'not like', '%walnut%')
                        ->where('title', 'not like', '%pecan%')
                        ->where('title', 'not like', '%hazelnut%')
                        ->where('title', 'not like', '%macadamia%')
                        ->where('title', 'not like', '%pistachio%')

                        ->where('instructions', 'not like', '% nut %')
                        ->where('instructions', 'not like', '%nuts%')
                        ->where('instructions', 'not like', '%peanut%')
                        ->where('instructions', 'not like', '%almond%')
                        ->where('instructions', 'not like', '%cashew%')
                        ->where('instructions', 'not like', '%walnut%')
                        ->where('instructions', 'not like', '%pecan%')
                        ->where('instructions', 'not like', '%hazelnut%')
                        ->where('instructions', 'not like', '%macadamia%')
                        ->where('instructions', 'not like', '%pistachio%');
                });
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

        $cutoffThreshold = (int) ($poolOfAllowedMeals->count() * 0.70);
        
        if(in_array('eat_healthy', $goals) && $cutoffThreshold >= 3) {
            // sort by lowest fat, keep the top 70%, and re-index the collection
            $poolOfAllowedMeals = $poolOfAllowedMeals->sortBy('fat')->take($cutoffThreshold)->values();
        }

        // PHASE 2: CALORIE & ZERO-WASTE ENGINE

        $breakfasts = $poolOfAllowedMeals->filter(fn($recipe) => is_array($recipe->meal_types) && in_array('breakfast', $recipe->meal_types));
        $lunches = $poolOfAllowedMeals->filter(fn($recipe) => is_array($recipe->meal_types) && in_array('lunch', $recipe->meal_types));
        $dinners = $poolOfAllowedMeals->filter(fn($recipe) => is_array($recipe->meal_types) && in_array('dinner', $recipe->meal_types));
        $snacks = $poolOfAllowedMeals->filter(fn($recipe) => is_array($recipe->meal_types) && in_array('snack', $recipe->meal_types));

        if($breakfasts->isEmpty() || $lunches->isEmpty() || $dinners->isEmpty()) {
            return response()->json([
                'success'=> false,
                'message' => 'Your filters are too strict! We could not find enough meals for every cathegory.',
                'summary' => [
                    'breakfasts_fount' => $breakfasts->count(),
                    'lunches_found' => $lunches->count(),
                    'dinners_found' => $dinners->count(),
                    'snacks_found' => $snacks->count(),
                ]
            ], 400);
        }

        $targetCalories = $settings->daily_calorie_target ?? 2000;

        $minCalories = $targetCalories * 0.85;
        $maxCalories = $targetCalories * 1.15;

        $weeklyPlan = [];
        $allDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        $currentDayName = Carbon::now()->format('l');
        $currentIndex = (int) array_search($currentDayName, $allDays);
        $days = array_slice($allDays, $currentIndex);

        $weeklyActiveIngredients = [];
        $weeklyIngredientQuantities = [];

        $snackChancePercentage = 40;

        foreach($days as $day) {
            $dailyMeals = null;
            $bestAttempt = null;
            $closestDifference = 99999;

            $attempts = 0;
            $maxAttempts = 150;  // don't let the server loop forever

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

            $fillerMeal = mt_rand(0, 2);
            while($attempts < $maxAttempts) {
                $b = null;
                $l = null;
                $d = null;
                $currentCalories = 0;

                if($fillerMeal === 0) {
                    $l = $lunches->random();
                    $d = $dinners->random();
                    $currentCalories = $l->calories + $d->calories + $snackCalories;

                    $neededMin = $minCalories - $currentCalories;
                    $neededMax = $maxCalories - $currentCalories;

                    $perfectMeals = $breakfasts->where('calories', '>=', $neededMin)
                                               ->where('calories', '<=', $neededMax);

                    if($perfectMeals->isNotEmpty()) {
                        $b = $perfectMeals->sortByDesc($zeroWasteScorer)->first();
                        $dailyMeals = collect(array_filter([$b, $l, $d, $snack]));
                        break;
                    }
                    $b = $breakfasts->random();
                } else if($fillerMeal === 1) {
                    $b = $breakfasts->random();
                    $d = $dinners->random();
                    $currentCalories = $b->calories + $d->calories + $snackCalories;

                    $neededMin = $minCalories - $currentCalories;
                    $neededMax = $maxCalories - $currentCalories;

                    $perfectMeals = $lunches->where('calories', '>=', $neededMin)
                                            ->where('calories', '<=', $neededMax);

                    if($perfectMeals->isNotEmpty()) {
                        $l = $perfectMeals->sortByDesc($zeroWasteScorer)->first();
                        $dailyMeals = collect(array_filter([$b, $l, $d, $snack]));
                        break;
                    }
                    $l = $lunches->random();
                } else {
                    $b = $breakfasts->random();
                    $l = $lunches->random();
                    $currentCalories = $b->calories + $l->calories + $snackCalories;

                    $neededMin = $minCalories - $currentCalories;
                    $neededMax = $maxCalories - $currentCalories;

                    $perfectMeals = $dinners->where('calories', '>=', $neededMin)
                                               ->where('calories', '<=', $neededMax);

                    if($perfectMeals->isNotEmpty()) {
                        $d = $perfectMeals->sortByDesc($zeroWasteScorer)->first();
                        $dailyMeals = collect(array_filter([$b, $l, $d, $snack]));
                        break;
                    }
                    $d = $dinners->random();
                }

                $testTotal = $b->calories + $l->calories + $d->calories + $snackCalories;
                $difference = abs($testTotal - $targetCalories);

                if($difference < $closestDifference) {
                    $closestDifference = $difference;
                    $bestAttempt = collect(array_filter([$b, $l, $d, $snack]));
                }
                $attempts++;
            }

            if(!$dailyMeals) {
                $dailyMeals = $bestAttempt;
            }

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
                'perfect_match' => $attempts < $maxAttempts
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Weekly plan succesfully generated.',
            'target_calories' => $targetCalories,
            'plan' => $weeklyPlan,
        ]);
    }

    public function regenerateMeal(Request $request) {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $settings = UserSetting::where('user_id', $user->id)->first();

        $mealType = $request->input('meal_type');

        if(!in_array($mealType, ['breakfast', 'lunch', 'dinner', 'snack'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid meal type provided.',
            ], 400);
        }

        $validRecipes = $this->getFilteredRecipes($user->id, $settings);

        if($settings && !empty($settings->meal_plan_preference) && !in_array('omnivore', $settings->meal_plan_preference)) {
            $preferences = $settings->meal_plan_preference;

            if(in_array('nut_free', $preferences)) {
                $validRecipes->where(function($q) {
                    $q->whereDoesntHave('ingredients', function($subQ) {
                        $subQ->where('name', 'like', '%nut%')
                              ->orWhere('name', 'like', '%peanut%')
                              ->orWhere('name', 'like', '%almond%')
                              ->orWhere('name', 'like', '%cashew%')
                              ->orWhere('name', 'like', '%walnut%')
                              ->orWhere('name', 'like', '%pecan%')
                              ->orWhere('name', 'like', '%hazelnut%')
                              ->orWhere('name', 'like', '%macadamia%')
                              ->orWhere('name', 'like', '%pistachio%');
                    })
                        ->where('title', 'not like', '% nut %')
                        ->where('title', 'not like', '%nuts%')
                        ->where('title', 'not like', '%peanut%')
                        ->where('title', 'not like', '%almond%')
                        ->where('title', 'not like', '%cashew%')
                        ->where('title', 'not like', '%walnut%')
                        ->where('title', 'not like', '%pecan%')
                        ->where('title', 'not like', '%hazelnut%')
                        ->where('title', 'not like', '%macadamia%')
                        ->where('title', 'not like', '%pistachio%')

                        ->where('instructions', 'not like', '% nut %')
                        ->where('instructions', 'not like', '%nuts%')
                        ->where('instructions', 'not like', '%peanut%')
                        ->where('instructions', 'not like', '%almond%')
                        ->where('instructions', 'not like', '%cashew%')
                        ->where('instructions', 'not like', '%walnut%')
                        ->where('instructions', 'not like', '%pecan%')
                        ->where('instructions', 'not like', '%hazelnut%')
                        ->where('instructions', 'not like', '%macadamia%')
                        ->where('instructions', 'not like', '%pistachio%');
                });
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
