<?php

namespace App\Services;

use App\Models\UserProfile;
use Illuminate\Support\Carbon;

class NutritionService
{
    public function calculateNutritionalTargets(UserProfile $profile) {
        $goal = $profile->fitness_goal->value ?? 'maintain';
        $macros = ['protein' => 30, 'carbs' => 40, 'fat' => 30];

        // adjust macros based on goal
        if(in_array($goal, ['lose_weight', 'lose weight'])) {
            $macros = ['protein' => 40, 'carbs' => 30, 'fat' => 30];
        } elseif(in_array($goal, ['gain_muscle', 'gain muscle'])) {
            $macros = ['protein' => 30, 'carbs' => 50, 'fat' => 20];
        }

        if (!empty($profile->weekly_calorie_target)) {
            return [
                'calories' => (int) round($profile->weekly_calorie_target / 7),
                'macros' => $macros,
            ];
        }
    
        $weight = (float) ($profile->weight_kg ?? 70);
        $height = (float) ($profile->height_cm ?? 170);
        $age = $profile->birthdate ? Carbon::parse($profile->birthdate)->age : 30;

        $sex = $profile->sex->value ?? 'male';
        $activity = $profile->baseline_activity->value ?? 'sedentary';

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

        $tdee = $bmr * $multipliers[$activity];
        
        $targetCalories = $tdee;
        $macros = ['protein' => 30, 'carbs' => 40, 'fat' => 30]; // maintain

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
        $profile->weekly_calorie_target = null;

        $targets = $this->calculateNutritionalTargets($profile);
        $profile->weekly_calorie_target = $targets['calories'] * 7;
        $profile->save();
    }
}