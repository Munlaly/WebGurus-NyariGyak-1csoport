<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        
        if(in_array('eat_healthy', $goals) && $cutoffThreshold >= 3) {
            // sort by lowest fat, keep the top 70%, and re-index the collection
            $poolOfAllowedMeals = $poolOfAllowedMeals->sortBy('fat')->take($cutoffThreshold)->values();
        }

        // PHASE 2: CALORIE COMBINER

        $breakfasts = $poolOfAllowedMeals->filter(fn($recipe) => is_array($recipe->meal_types) && in_array('breakfast', $recipe->meal_types));
        $lunches = $poolOfAllowedMeals->filter(fn($recipe) => is_array($recipe->meal_types) && in_array('lunch', $recipe->meal_types));
        $dinners = $poolOfAllowedMeals->filter(fn($recipe) => is_array($recipe->meal_types) && in_array('dinner', $recipe->meal_types));

        if($breakfasts->isEmpty() || $lunches->isEmpty() || $dinners->isEmpty()) {
            return response()->json([
                'success'=> false,
                'message' => 'Your filters are too strict! We could not find enough meals for every cathegory.',
                'summary' => [
                    'breakfasts_fount' => $breakfasts->count(),
                    'lunches_found' => $lunches->count(),
                    'dinners_found' => $dinners->count(),
                ]
            ], 400);
        }

        $targetCalories = $settings->daily_calorie_target ?? 2000;

        $minCalories = $targetCalories * 0.85;
        $maxCalories = $targetCalories * 1.15;

        $weeklyPlan = [];
        $allDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        $currentDayName = \Carbon\Carbon::now()->format('l');
        $currentIndex = array_search($currentDayName, $allDays);
        $currentIndex = $currentIndex !== false ? $currentIndex : 0;
        $days = array_slice($allDays, $currentIndex);

        foreach($days as $day) {
            $dailyMeals = null;
            $bestAttempt = null;
            $closestDifference = 99999;

            $attempts = 0;
            $maxAttempts = 150;  // don't let the server loop forever

            $fillerMeal = mt_rand(0, 2);
            while($attempts < $maxAttempts) {
                $b = null;
                $l = null;
                $d = null;
                $currentCalories = 0;

                if($fillerMeal === 0) {
                    $l = $lunches->random();
                    $d = $dinners->random();
                    $currentCalories = $l->calories + $d->calories;

                    $neededMin = $minCalories - $currentCalories;
                    $neededMax = $maxCalories - $currentCalories;

                    $perfectMeals = $breakfasts->where('calories', '>=', $neededMin)
                                               ->where('calories', '<=', $neededMax);

                    if($perfectMeals->isNotEmpty()) {
                        $b = $perfectMeals->random();
                        $dailyMeals = collect([$b, $l, $d]);
                        break;
                    }
                    $b = $breakfasts->random();
                } else if($fillerMeal === 1) {
                    $b = $breakfasts->random();
                    $d = $dinners->random();
                    $currentCalories = $b->calories + $d->calories;

                    $neededMin = $minCalories - $currentCalories;
                    $neededMax = $maxCalories - $currentCalories;

                    $perfectMeals = $lunches->where('calories', '>=', $neededMin)
                                            ->where('calories', '<=', $neededMax);

                    if($perfectMeals->isNotEmpty()) {
                        $l = $perfectMeals->random();
                        $dailyMeals = collect([$b, $l, $d]);
                        break;
                    }
                    $l = $lunches->random();
                } else {
                    $b = $breakfasts->random();
                    $l = $lunches->random();
                    $currentCalories = $b->calories + $l->calories;

                    $neededMin = $minCalories - $currentCalories;
                    $neededMax = $maxCalories - $currentCalories;

                    $perfectMeals = $dinners->where('calories', '>=', $neededMin)
                                               ->where('calories', '<=', $neededMax);

                    if($perfectMeals->isNotEmpty()) {
                        $d = $perfectMeals->random();
                        $dailyMeals = collect([$b, $l, $d]);
                        break;
                    }
                    $d = $dinners->random();
                }

                $testTotal = $b->calories + $l->calories + $d->calories;
                $difference = abs($testTotal - $targetCalories);

                if($difference < $closestDifference) {
                    $closestDifference = $difference;
                    $bestAttempt = collect([$b, $l, $d]);
                }
                $attempts++;
            }

            if(!$dailyMeals) {
                $dailyMeals = $bestAttempt;
            }

            $weeklyPlan[$day] = [
                'meals' => $dailyMeals,
                'total_calories' => $dailyMeals->sum('calories'),
                'perfect_match' => $attempts < $maxAttempts
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Weekly plan succesfully generated. ',
            'target_calories' => $targetCalories,
            'plan' => $weeklyPlan,
        ]);
    }

    public function regenerateMeal(Request $request) {
        $user = $request->user();
        $settings = UserSettings::where('user_id', $user->id)->first();

        $mealType = $request->input('meal_type');

        if(!in_array($mealType, ['breakfast', 'lunch', 'dinner'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid meal type provided.',
            ], 400);
        }

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
        $user = $request->user();
        $plan = $request->input('plan');

        if(!$plan) {
            return response()->json([
                'success'=> false,
                'message' => 'No plan data provided. '
            ], 400);
        }

        \App\Models\MealPlan::where('user_id', $user->id)
            ->where('status', 'DRAFT')
            ->delete();

        $startOfWeek = \Carbon\Carbon::now()->startOfWeek();

        $dayMapping = [
            'Monday' => 0,
            'Tuesday' => 1,
            'Wednesday' => 2,
            'Thursday' => 3,
            'Friday' => 4,
            'Saturday' => 5,
            'Sunday' => 6
        ];

        $mealTypes = ['breakfast', 'lunch', 'dinner'];

        foreach($plan as $dayName => $dayData) {
            $dayOffset = $dayMapping[$dayName] ?? 0;
            $scheduledDate = $startOfWeek->copy()->addDays($dayOffset)->toDateString();

            foreach($dayData['meals'] as $index => $meal) {
                $mealType = $mealTypes[$index] ?? 'lunch';

                \App\Models\MealPlan::create([
                    'user_id' => $user->id,
                    'recipe_id' => $meal['id'],
                    'scheduled_date' => $scheduledDate,
                    'meal_type' => $mealType,
                    'status' => 'DRAFT',
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Weekly plan saved to your clendar succesfully!'
        ]);
    }
}
