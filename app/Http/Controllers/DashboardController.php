<?php

namespace App\Http\Controllers;

use App\Models\DailyPlan;
use App\Models\UserInventory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Carbon;
use App\Services\AlertService;
use App\Models\Recipe;
use App\Enums\EntityStatus;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $favoriteRecipeIds = $user->favoriteRecipes()->pluck('recipes.id')->toArray();

        $formatMeal = function ($mealPlan) use ($favoriteRecipeIds) {
            $recipe = $mealPlan->recipe;
            if(!$recipe) {
                return null;
            }

            return [
                'id' => $recipe->id,
                'meal_plan_id' => $mealPlan->id,
                'meal_type' => $mealPlan->meal_type,
                'title' => $recipe->name,
                'calories' => $recipe->calories ?? 0,
                'prepTime' => $recipe->prep_time_minutes ?? 0,
                'imageUrl' => $this->getRecipeImageUrl($recipe->image),
                'imageAlt' => $recipe->name,
                'isPrepared' => $mealPlan->status === 'EATEN',
                'isFavorite' => in_array($recipe->id, $favoriteRecipeIds),
            ];
        };

        $today = Carbon::now();
        $todayString = $today->toDateString();
        $yesterdayString = $today->copy()->subDay()->toDateString();
        $tomorrowString = $today->copy()->addDay()->toDateString();

        $startOfWeek = $today->copy()->startOfWeek()->toDateString();
        $endOfWeek = $today->copy()->endOfWeek()->toDateString();

        $dailyPlans = DailyPlan::where('user_id', $user->id)
            ->whereBetween('date', [
                $today->copy()->subDay()->startOfDay(),
                $today->copy()->addDay()->endOfDay()
            ])
            ->with(['mealPlans.recipe.ingredients'])
            ->get();

        $weeklyPlans = DailyPlan::where('user_id', $user->id)
            ->whereBetween('date', [$startOfWeek, $endOfWeek])
            ->with('mealPlans.recipe')
            ->get();

        $weeklyAnalytics = function () use ($weeklyPlans) {
            $weeklyTargetCals = $weeklyPlans->sum('target_calories');
            $weeklyConsumedCals = 0;

            foreach ($weeklyPlans as $plan) {
                $eatenMeals = $plan->mealPlans->where('status', 'EATEN');
                foreach ($eatenMeals as $mealPlan) {
                    $recipe = $mealPlan->recipe;
                    $weeklyConsumedCals += $recipe->calories ?? 0;
                }
            }

            $percentage = $weeklyTargetCals > 0 
                ? (int) round(($weeklyConsumedCals / $weeklyTargetCals) * 100) 
                : 0;

            return [
                'targetCalories' => $weeklyTargetCals,
                'consumedCalories' => $weeklyConsumedCals,
                'percentage' => min($percentage, 100), 
            ];
        };
 
        $mealsByOffset = [
            '-1' => [],
            '0' => [],
            '1' => [],
        ];

        $hasActivePlan = DailyPlan::where('user_id', $user->id)
            ->whereBetween('date', [
                $today->copy()->startOfWeek()->startOfDay(),
                $today->copy()->endOfWeek()->endOfDay()
        ])
        ->exists();

        foreach($dailyPlans as $dailyPlan) {
            $planDate = Carbon::parse($dailyPlan->date)->toDateString();

            $offset = match ($planDate) {
                $yesterdayString => '-1',
                $todayString => '0',
                $tomorrowString => '1',
                default => null,
            };

            if($offset !== null) {
                foreach($dailyPlan->mealPlans as $mealPlan) {
                    $formatted = $formatMeal($mealPlan);
                    if($formatted) {
                        $mealsByOffset[$offset][] = $formatted;
                    }
                }
            }
        }

        return Inertia::render('Dashboard', [
            'mealsByOffset' => $mealsByOffset,
            'hasActivePlan' => $hasActivePlan,
            'weeklyAnalytics' => $weeklyAnalytics,
        ]);
    }

    public function searchRecipes(Request $request) {
        $query = $request->input('q');
        if(!$query) {
            return response()->json([]);
        }

        $recipes = Recipe::where('name', 'like', "%{$query}%")
            ->select('id', 'name', 'meal_types', 'calories')
            ->limit(10)
            ->get();

        return response()->json($recipes);
    }

    public function swapMeal(Request $request) {
        $request->validate([
            'recipe_id' => 'required|exists:recipes,id',
            'meal_type' => 'required|string',
            'date_offset' => 'required|integer',
        ]);

        $user = $request->user();
        $targetDate = Carbon::now()->addDays($request->date_offset)->toDateString();

        $dailyPlan = DailyPlan::where('user_id', $user->id)
            ->whereDate('date', $targetDate)
            ->first();

        if(!$dailyPlan) {
            return back()->withErrors(['message' => 'No meal plan found for this day.']);
        }

        $mealPlan = $dailyPlan->mealPlans()->where('meal_type', $request->meal_type)->first();

        if ($mealPlan) {
            $mealPlan->update([
                'recipe_id' => $request->recipe_id,
                'status' => EntityStatus::Draft->value ?? 'DRAFT'
            ]);
        } else {
            $dailyPlan->mealPlans()->create([
                'recipe_id' => $request->recipe_id,
                'meal_type' => $request->meal_type,
                'status' => EntityStatus::Draft->value ?? 'DRAFT'
            ]);
        }

        return redirect()->back();
    }

    public function alerts(Request $request, AlertService $alertService): Response {
        $user = $request->user();

        $expiringAlerts = $alertService->getExpiringAlertIds($user);

        return Inertia::render('Alerts', [
            'expiringAlerts' => $expiringAlerts
        ]);
    }

}