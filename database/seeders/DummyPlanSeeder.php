<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Recipe;
use App\Models\DailyPlan;
use App\Models\MealPlan;
use Illuminate\Support\Carbon;

class DummyPlanSeeder extends Seeder
{
    public function run(): void
    {
        // Grab the first user from your database
        $user = User::first();

        if (!$user) {
            $this->command->error('No users found! Please register a user or run UserSeeder first.');
            return;
        }

        // Grab 9 random recipes to use for our meals
        $recipes = Recipe::inRandomOrder()->take(9)->get();

        if ($recipes->count() < 3) {
            $this->command->error('Not enough recipes found. Make sure RecipeSeeder ran successfully!');
            return;
        }

        // Set up our dates to perfectly match your DashboardController logic
        $dates = [
            Carbon::now()->subDay()->toDateString(), // Yesterday
            Carbon::now()->toDateString(),           // Today
            Carbon::now()->addDay()->toDateString(), // Tomorrow
        ];

        $recipeIndex = 0;

        foreach ($dates as $date) {
            // 1. Create OR Find the Daily Plan
            $dailyPlan = DailyPlan::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'date'    => $date,
                ],
                [
                    'day_type'        => 'moderate', 
                    'target_calories' => 2000,
                    'status'          => 'generated', 
                ]
            );

            // Array of meal types to match our 3 iterations
            $mealTypes = ['breakfast', 'lunch', 'dinner'];

            // 2. Attach 3 Meal Plans to it
            for ($i = 0; $i < 3; $i++) {
                if (isset($recipes[$recipeIndex])) {
                    MealPlan::firstOrCreate(
                        [
                            'daily_plan_id' => $dailyPlan->id,
                            'meal_type'     => $mealTypes[$i], 
                        ],
                        [
                            'recipe_id'     => $recipes[$recipeIndex]->id,
                            'status'        => $date === Carbon::now()->subDay()->toDateString() ? 'eaten' : 'generated',
                        ]
                    );
                    $recipeIndex++;
                }
            }
        }

        $this->command->info('Dummy Daily Plans and Meal Plans seeded successfully!');
    }
}