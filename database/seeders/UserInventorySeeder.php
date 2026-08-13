<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
<<<<<<< HEAD
use App\Models\User;
=======
>>>>>>> 8c9d93d (Add UserInventoryController, seeder, and routes for managing user inventory. Update User and UserInventory models, and modify migrations for users and personal access tokens.)

class UserInventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Clear the table first so we don't duplicate data on re-runs
        DB::table('user_inventories')->truncate();

<<<<<<< HEAD
        $user = User::where('email', 'test@example.com')->first();

        if(! $user) {
            return;
        }


=======
>>>>>>> 8c9d93d (Add UserInventoryController, seeder, and routes for managing user inventory. Update User and UserInventory models, and modify migrations for users and personal access tokens.)
        // 2. These are the exact required amounts from your screenshot
        $recipeRequirements = [
            1 => 8, 2 => 3, 3 => 3, 4 => 2, 5 => 0.5, 6 => 6, 
            7 => 2, 8 => 28, 9 => 2, 10 => 8, 11 => 1, 12 => 8, 13 => 1
        ];

        $inventoryData = [];

        // 3. Loop through and give the user enough of each ingredient
        foreach ($recipeRequirements as $ingredientId => $requiredAmount) {
            $inventoryData[] = [
<<<<<<< HEAD
                'user_id' => $user->id,
=======
                'user_id' => 1, 
>>>>>>> 8c9d93d (Add UserInventoryController, seeder, and routes for managing user inventory. Update User and UserInventory models, and modify migrations for users and personal access tokens.)
                'ingredient_id' => $ingredientId, 
                // We add 10 to the required amount so the user has leftovers after cooking
                'amount_left' => $requiredAmount + 10, 
                'status' => 'FULL', 
                'expiration_date' => now()->addDays(14)->toDateString(), 
                'is_frozen' => false, 
                'created_at' => now(), 
                'updated_at' => now()
            ];
        }

        // 4. Insert everything into the database at once
        DB::table('user_inventories')->insert($inventoryData);
    }
}