<?php

namespace App\Services;

use App\Models\UserProfile;
use Illuminate\Support\Carbon;
use App\Models\DailyPlan;
use App\Enums\ExerciseIntensity;

class NutritionService
{
    public function calculateNutritionalTargets(UserProfile $profile, ExerciseIntensity $dayIntensity = ExerciseIntensity::Moderate) {
        $goal = $profile->fitness_goal->value ?? 'maintain';
        $macros = ['protein' => 30, 'carbs' => 40, 'fat' => 30];

        // adjust macros based on goal
        if(in_array($goal, ['lose_weight', 'lose weight'])) {
            $macros = ['protein' => 40, 'carbs' => 30, 'fat' => 30];
        } elseif(in_array($goal, ['gain_muscle', 'gain muscle'])) {
            $macros = ['protein' => 30, 'carbs' => 50, 'fat' => 20];
        }

        $weight = (float) ($profile->weight_kg ?? 70);
        $height = (float) ($profile->height_cm ?? 170);
        $age = $profile->birthdate ? Carbon::parse($profile->birthdate)->age : 30;
        $sex = $profile->sex->value ?? 'male';
        $activity = $profile->baseline_activity->value ?? 'sedentary';

        // Base BMR Calculation
        $bmr = (10 * $weight) + (6.25 * $height) - (5 * $age);
        $bmr += ($sex === 'female') ? -161 : 5;

        $multipliers = [
            'sedentary' => 1.2,
            'lightly_active' => 1.375,
            'moderately_active' => 1.55,
            'very_active' => 1.725,
        ];

        // Base TDEE
        $tdee = $bmr * $multipliers[$activity];

        // Add Exercise Output FIRST
        if ($dayIntensity === ExerciseIntensity::Moderate) {
            $tdee += (int) round($weight * 4.5);
        } elseif ($dayIntensity === ExerciseIntensity::Heavy) {
            $tdee += (int) round($weight * 7.5);
        }

        // Apply Goal Deficit/Surplus LAST
        $targetCalories = $tdee;
        if(in_array($goal, ['lose_weight', 'lose weight'])) {
            $targetCalories = $tdee - 500;
        } elseif(in_array($goal, ['gain_muscle', 'gain muscle'])) {
            $targetCalories = $tdee + 500;
        }
        
        return [
            'calories' => (int) round($targetCalories),
            'macros' => $macros,
        ];

    }

    public function updateProfileWeeklyCalories(UserProfile $profile): void {
        $targets = $this->calculateNutritionalTargets($profile);
        $profile->weekly_calorie_target = $targets['calories'] * 7;
        $profile->save();

        $schedules = $profile->user->exerciseSchedules()->pluck('intensity', 'day_of_week')->toArray();


        // Apply changes to future plans as well as to today's plan
        $futurePlans = DailyPlan::where('user_id', $profile->user_id)
            ->whereDate('date', '>=', Carbon::now()->toDateString())
            ->get();

        foreach ($futurePlans as $plan) {
            $dayNum = Carbon::parse($plan->date)->dayOfWeekIso;

            $intensity = $schedules[$dayNum] ?? ExerciseIntensity::Rest;

            $dailyNutrition = $this->calculateNutritionalTargets($profile, $intensity);
            $dailyCals = $dailyNutrition['calories'];

            // Convert macro percentages to exact grams based on the adjusted daily calories
            $proteinGrams = (int) round(($dailyCals * ($targets['macros']['protein'] / 100)) / 4);
            $carbsGrams   = (int) round(($dailyCals * ($targets['macros']['carbs'] / 100)) / 4);
            $fatGrams     = (int) round(($dailyCals * ($targets['macros']['fat'] / 100)) / 9);

            $plan->update([
                'day_type' => $intensity,
                'target_calories'  => $dailyCals,
                'target_protein_g' => $proteinGrams,
                'target_carbs_g'   => $carbsGrams,
                'target_fat_g'     => $fatGrams,
            ]);
        }
    }
}