<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
<<<<<<< HEAD
use App\Models\User;
use App\Models\UserSettings;
use Illuminate\Support\Facades\Hash;
=======
>>>>>>> d608328 (Add UserInventoryController, seeder, and routes for managing user inventory. Update User and UserInventory models, and modify migrations for users and personal access tokens.)

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'username' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'daily_calorie_target' => 2000,
        ]);

        UserSettings::create([
            'user_id'=> $user->id,
            'goals' => ['lose weight'],
            'meal_plan_preference' => ['vegan'],
            'household_size' => '1 person',
            'prep_time_preference' => 'under 20 minutes',
            'budget_or_comfort' => 'comfort_first',
        ]);
        
        $this->call ([
            CategorySeeder::class,
            RecipeSeeder::class,
            UserSeeder::class,
            UserInventorySeeder::class,
        ]);
    }
}
