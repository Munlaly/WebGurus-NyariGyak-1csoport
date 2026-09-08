<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserSetting;
use App\Models\Ingredient;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'username' => 'Test User',
                'password' => Hash::make('password'),
                'onboarded_at' => now(),
            ]
        );

        $user->tokens()->delete(); // Clear old ones if running multiple times
        $user->createToken('vue-test-token')->plainTextToken;

        UserProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'sex' => 'male',
                'birthdate' => '2006-06-12',
                'height_cm' => 180,
                'weight_kg' => 73,
                'baseline_activity' => 'sedentary',
                'fitness_goal' => 'lose_weight',
                'weekly_calorie_target' => 12000,
            ],
        );

        UserSetting::updateOrCreate(
            ['user_id' => $user->id],
            [
                'household_size' => 1, 
                'prep_time_preference' => 45,
                'zero_waste_score' => 0,
                
            ]
        );

        $dislikedIngredients = Ingredient::whereIn('name', ['onion', 'garlic'])->get();

        foreach ($dislikedIngredients as $ingredient) {
            DB::table('user_disliked_ingredients')->updateOrInsert([
                'user_id' => $user->id,
                'ingredient_id' => $ingredient->id
            ]);
        }
    }
}
