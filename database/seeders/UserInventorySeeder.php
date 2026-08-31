<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserInventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Clear the table first so we don't duplicate data on re-runs
        DB::table('user_inventories')->truncate();

        $user = User::where('email', 'test@example.com')->first();

        if(! $user) {
            return;
        }


        // 2. These are the exact required amounts from your screenshot
        $recipeRequirements = [
            1 => 8, 2 => 3, 3 => 3, 4 => 2, 5 => 0.5, 6 => 6, 
            7 => 2, 8 => 28, 9 => 2, 10 => 8, 11 => 1, 12 => 8, 13 => 1
        ];

        $inventoryData = [];


        // 3. Loop through and give the user enough of each ingredient
        foreach ($recipeRequirements as $ingredientId => $requiredAmount) {
            $random = rand(-2, 10);
            $inventoryData[] = [
                'user_id' => $user->id,
                'ingredient_id' => $ingredientId, 
                // We add 10 to the required amount so the user has leftovers after cooking
                'amount_left' => $requiredAmount + 10, 
                'status' => 'FULL', 
                'expiration_date' => now()->addDays($random)->toDateString(), 
                'is_frozen' => false, 
                'created_at' => now(), 
                'updated_at' => now()
            ];
        }

        // 4. Insert everything into the database at once
        DB::table('user_inventories')->insert($inventoryData);
    }
}