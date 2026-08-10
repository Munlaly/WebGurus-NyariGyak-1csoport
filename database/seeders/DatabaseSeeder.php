<?php

namespace Database\Seeders;

use App\Models\User;
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
        User::create([
            'username' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'diet_preference' => 'normal',
            'daily_calorie_target' => 2000,
        ]);
        
        $this->call ([
            CategorySeeder::class,
            RecipeSeeder::class,
        ]);
    }
}
