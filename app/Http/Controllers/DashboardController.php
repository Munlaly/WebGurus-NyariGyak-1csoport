<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\DailyPlan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Carbon;

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

            $imageUrl = 'https://placehold.co/600x400?text=No+Image';
            if ($recipe->image) {
                $imageUrl = str_starts_with($recipe->image, 'http')
                    ? $recipe->image
                    : asset('storage/' . $recipe->image);
            }
            
            if(str_contains($imageUrl, 'spoonacular.com')) {
                $imageUrl = str_replace(['-312x231.jpg', '-240x150.jpg', '-90x90.jpg'], '-636x393.jpg', $imageUrl);
            }

            return [
                'id' => $recipe->id,
                'meal_plan_id' => $mealPlan->id,
                'title' => $recipe->name,
                'calories' => $recipe->calories ?? 0,
                'prepTime' => $recipe->prep_time_minutes ?? 0,
                'imageUrl' => $imageUrl,
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

}