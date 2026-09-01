<?php

namespace App\Http\Controllers;

use App\Models\DailyPlan;
use App\Models\UserInventory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Carbon;
use App\Services\AlertService;

use function Symfony\Component\String\b;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $formatMeal = function ($mealPlan) {
            $recipe = $mealPlan->recipe;
            if(!$recipe) {
                return null;
            }

            return [
                'id' => $recipe->id,
                'meal_plan_id' => $mealPlan->id,
                'title' => $recipe->name,
                'calories' => $recipe->calories ?? 0,
                'prepTime' => $recipe->prep_time_minutes ?? 0,
                'imageUrl' => $this->getRecipeImageUrl($recipe->image),
                'imageAlt' => $recipe->name,
                'isPrepared' => $mealPlan->status === 'EATEN',
            ];
        };

        $dailyPlans = DailyPlan::where('user_id', $user->id)
            ->with(['mealPlans.recipe.ingredients'])
            ->get();
 
        $mealsByOffset = [
            '-1' => [],
            '0' => [],
            '1' => [],
        ];

        $todayString = Carbon::now()->toDateString();
        $yesterdayString = Carbon::now()->subDay()->toDateString();
        $tomorrowString = Carbon::now()->addDay()->toDateString();

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
        ]);
    }

    public function alerts(Request $request, AlertService $alertService): Response {
        $user = $request->user();

        $expiringAlerts = $alertService->getExpiringAlertIds($user);

        return Inertia::render('Alerts', [
            'expiringAlerts' => $expiringAlerts
        ]);
    }

}