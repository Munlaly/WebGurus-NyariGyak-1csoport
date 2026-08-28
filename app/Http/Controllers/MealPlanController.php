<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserSetting;
use App\Models\Recipe;
use Illuminate\Support\Facades\DB;
use App\Models\MealPlan;
use Illuminate\Support\Carbon;
use App\Models\DailyPlan;
use App\Models\DietaryOption;
use App\Models\UserInventory;
use App\Models\UserProfile;

class MealPlanController extends Controller
{
    private function calculateNutritionalTargets(UserProfile $profile) {
        $weight = (float) ($profile->weight_kg ?? 70);
        $height = (float) ($profile->height_cm ?? 170);
        $age = $profile->birthdate ? Carbon::parse($profile->birthdate)->age : 30;
        $sex = strtolower($profile->sex ?? 'male');

        // calculate Basal Metabolic Rate (Mifflin-St Jeor)
        $bmr = (10 * $weight) + (6.25 * $height) - (5 * $age);
        $bmr += ($sex === 'female') ? -161 : 5;

        // apply activity multiplier
        $multipliers = [
            'sedentary' => 1.2,
            'lightly_active' => 1.375,
            'moderately_active' => 1.55,
            'very_active' => 1.725,
        ];

        $activity = strtolower($profile->baseline_activity ?? 'sedentary');
        // Total Daily Energy Expenditure
        $tdee = $bmr * ($multipliers[$activity] ?? 1.2);

        $goal = strtolower($profile->fitness_goal ?? 'maintain');

        $targetCalories = $tdee;
        $macros = ['protein' => 30, 'carbs' => 40, 'fat' => 30]; // maintain

        if(in_array($goal, ['lose_weight', 'lose weight'])) {
            $targetCalories = $tdee - 500;
            $macros = ['protein' => 40, 'carbs' => 30, 'fat' => 30];
        } elseif(in_array($goal, ['gain_muscle', 'gain muscle'])) {
            $targetCalories = $tdee + 500;
            $macros = ['protein' => 30, 'carbs' => 50, 'fat' => 20];
        }

        return [
            'calories' => (int) round($targetCalories),
            'macros' => $macros,
        ];
    }

    private function getFilteredRecipes(int $userId, ?UserSetting $settings) {
        $dislikedIngredientIds = DB::table('user_disliked_ingredients')
            ->where('user_id', $userId)
            ->pluck('ingredient_id')
            ->toArray();

        $dietaryOptions = DietaryOption::whereHas('users', function ($query) use ($userId) {
            $query->where('users.id', $userId);
        })->with('excludedCategories')->get();

        $excludedCategoryIds = $dietaryOptions->flatMap(function ($option) {
            return $option->excludedCategories->pluck('id');
        })->unique()->toArray();

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

        if(!empty($excludedCategoryIds)) {
            $validRecipes->whereDoesntHave('ingredients', function ($query) use ($excludedCategoryIds) {
                $query->whereIn('category_id', $excludedCategoryIds);
            });
        }
        return $validRecipes;
    }

    public function generate(Request $request) {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $settings = UserSetting::where('user_id', $user->id)->first();
        $profile = UserProfile::where('user_id', $user->id)->first();

        $nutritionTargets = $profile ? $this->calculateNutritionalTargets($profile) : ['calories' => 2000, 'macros' => ['protein' => 30, 'carbs' => 40, 'fat' => 30]];
        $targetCalories = $nutritionTargets['calories'];
        $macroTargets = $nutritionTargets['macros'];

        // PHASE 1: HARD FILTERS
        $validRecipes = $this->getFilteredRecipes($user->id, $settings);
        $poolOfAllowedMeals = $validRecipes->get();

        if($poolOfAllowedMeals->count() < 3) {
            return response()->json([
                'success' => false,
                'message' => 'Your filters are too strict! We could not find enough meals.',
                'found' => $poolOfAllowedMeals->count()
            ], 400);
        }

        // PHASE 1.5: GOAL-BASED PRUNING

        $goal = $profile ? strtolower($profile->fitness_goal ?? 'maintain') : 'maintain';
        $cutoffThreshold = (int) ($poolOfAllowedMeals->count() * 0.70); // Keep the top 70 %

        if ($cutoffThreshold >= 3) {
            if(in_array($goal, ['gain_muscle', 'gain muscle'])) {
                // sort by highest protein, keep the top 70%, and re-index the collection
                $poolOfAllowedMeals = $poolOfAllowedMeals->sortByDesc('protein')->take($cutoffThreshold)->values();
            } elseif(in_array($goal, ['lose_weight', 'lose weight'])) {
                // sort by lowest fat, keep the top 70%, and re-index the collection
                $poolOfAllowedMeals = $poolOfAllowedMeals->sortBy('calories')->sortBy('fat')->take($cutoffThreshold)->values();
            }
        }

        // PHASE 2: CALORIE & ZERO-WASTE ENGINE

        $breakfasts = $poolOfAllowedMeals->filter(fn($recipe) => is_array($recipe->meal_types) && in_array('breakfast', $recipe->meal_types));
        $lunches = $poolOfAllowedMeals->filter(fn($recipe) => is_array($recipe->meal_types) && in_array('lunch', $recipe->meal_types));
        $dinners = $poolOfAllowedMeals->filter(fn($recipe) => is_array($recipe->meal_types) && in_array('dinner', $recipe->meal_types));
        $snacks = $poolOfAllowedMeals->filter(fn($recipe) => is_array($recipe->meal_types) && in_array('snack', $recipe->meal_types));

        if($breakfasts->isEmpty() || $lunches->isEmpty() || $dinners->isEmpty()) {
            return response()->json([
                'success'=> false,
                'message' => 'Your filters are too strict! We could not find enough meals for every category.',
                'summary' => [
                    'breakfasts_found' => $breakfasts->count(),
                    'lunches_found' => $lunches->count(),
                    'dinners_found' => $dinners->count(),
                    'snacks_found' => $snacks->count(),
                ]
            ], 400);
        }

        $minCalories = $targetCalories * 0.85;
        $maxCalories = $targetCalories * 1.15;

        $userInventory = UserInventory::where('user_id', $user->id)
            ->orderBy('expiration_date', 'asc')
            ->get()
            ->keyBy('ingredient_id');
        $now = Carbon::now();
        $oneWeekFromNow = $now->copy()->addDays(7);

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

            $zeroWasteScorer = function($meal) use ($weeklyActiveIngredients, $userInventory, $oneWeekFromNow) {
                $score = 0;
                if(empty($weeklyActiveIngredients) && $userInventory->isEmpty()) {
                    return mt_rand(1, 100); // Randomize the first day
                }

                /** @var \App\Models\Ingredient $ingredient */
                foreach($meal->ingredients as $ingredient) {
                    $ingId = $ingredient->id;
                    $amount = (float) ($ingredient->pivot->amount ?? 1);

                    if(in_array($ingId, $weeklyActiveIngredients)) {
                        $score += 15; // Reward for using an ingredient already in the plan
                        $score += $amount;
                    }

                    if($userInventory->has($ingId)) {
                        $inventoryItem = $userInventory->get($ingId);

                        $score += 25;
                        if(in_array($inventoryItem->status, ['OPENED', 'LOW'])) {
                            $score += 35;
                        }

                        if($inventoryItem->expiration_date) {
                            $expDate = Carbon::parse($inventoryItem->expiration_date)->startOfDay();
                            $today = Carbon::now()->startOfDay();
                            $tomorrow = Carbon::now()->addDay()->startOfDay();

                            if($expDate->isBefore($today)) {
                                $score -= 50; // expired: penalize recipes using this so it doesn't get picked
                            } elseif($expDate->equalTo($today) || $expDate->equalTo($tomorrow)) {
                                $score += 100; // critical: use today or tomorrow
                            } elseif($expDate->isBetween($tomorrow, $oneWeekFromNow)) {
                                $score += 60; // urgent: expiring soon
                            }
                        }
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
            'message' => 'Weekly plan successfully generated.',
            'target_calories' => $targetCalories,
            'macro_targets' => $macroTargets,
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
        $profile = UserProfile::where('user_id', $user->id)->first();
        $nutritionTargets = $profile ? $this->calculateNutritionalTargets($profile): ['calories' => 2000, 'macros' => ['protein' => 30, 'carbs' => 40, 'fat' => 30]];

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
    
        DB::transaction(function () use ($user, $plan, $startOfWeek, $dayMapping, $mealTypesArray, $nutritionTargets) {
            // Delete old drafts
            $oldDailyPlans = DailyPlan::where('user_id', $user->id)->where('status', 'DRAFT')->get();
            MealPlan::whereIn('daily_plan_id', $oldDailyPlans->pluck('id'))->delete();
            foreach($oldDailyPlans as $dp) {
                $dp->delete();
            }

            // Insert new plan
            foreach($plan as $dayName => $dayData) {
                $dayOffset = $dayMapping[$dayName] ?? 0;
                $scheduledDate = $startOfWeek->copy()->addDays($dayOffset)->toDateString();

                $dailyPlan = DailyPlan::create([
                    'user_id' => $user->id,
                    'date' => $scheduledDate,
                    'day_type' => 'regular',
                    'target_calories' => $nutritionTargets['calories'],
                    'target_protein_g' => (int) (($nutritionTargets['calories'] * ($nutritionTargets['macros']['protein'] / 100)) / 4),
                    'target_carbs_g' => (int) (($nutritionTargets['calories'] * ($nutritionTargets['macros']['carbs'] / 100)) / 4),
                    'target_fat_g' => (int) (($nutritionTargets['calories'] * ($nutritionTargets['macros']['fat'] / 100)) / 9),
                    'status' => 'DRAFT',
                ]);

                foreach($dayData['meals'] as $index => $meal) {
                    $mealType = $meal['meal_type'] ?? ($mealTypesArray[$index] ?? 'snack');

                    MealPlan::create([
                        'daily_plan_id' => $dailyPlan->id,
                        'recipe_id' => $meal['id'],
                        'meal_type' => $mealType,
                        'status' => 'DRAFT',
                    ]);
                }
            }
        });
        return response()->json([
            'success' => true,
            'message' => 'Weekly plan saved to your calendar successfully!'
        ]);     
    }
}
