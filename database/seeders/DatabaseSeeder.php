<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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
        ]);

        UserProfile::create([
            'user_id' => $user->id,
            'sex' => 'male', 
            'weekly_calorie_target' => 14000, 
        ]);

        UserSetting::create([
            'user_id'=> $user->id,
            'goals' => ['lose weight'],
            'household_size' => '1',
            'prep_time_preference' => 'under 20 minutes',
        ]);
        
        $this->call ([
            CategorySeeder::class,
            RecipeSeeder::class,
        ]);
    }
}
