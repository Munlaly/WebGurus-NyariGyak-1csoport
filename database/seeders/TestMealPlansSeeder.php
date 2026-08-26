<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MealPlan;
use App\Models\User;
use App\Models\Recipe;
use Illuminate\Support\Carbon;

class TestMealPlansSeeder extends Seeder
{
    public function run(): void
    {
        // Grab the first user or create a test user
        $user = User::first();
        if (!$user) {
            return;
        }

        // Grab a few recipes to use
        $recipes = Recipe::take(3)->get();
        if ($recipes->isEmpty()) {
            return;
        }

        $today = Carbon::now()->toDateString();
        $yesterday = Carbon::now()->subDay()->toDateString();
        $tomorrow = Carbon::now()->addDay()->toDateString();

        $dates = [$yesterday, $today, $tomorrow];
        $mealTypes = ['breakfast', 'lunch', 'dinner'];

        foreach ($dates as $index => $date) {
            $recipe = $recipes[$index % $recipes->count()];

            MealPlan::create([
                'user_id' => $user->id,
                'recipe_id' => $recipe->id,
                'scheduled_date' => $date,
                'meal_type' => $mealTypes[$index],
                'status' => 'DRAFT',
            ]);
        }
    }
}