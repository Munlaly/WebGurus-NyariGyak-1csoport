<?php

namespace Database\Seeders;

use App\Models\Ingredient;
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

        $user = User::where('email', 'banat@banat.banat')->first();

        if(! $user) {
            return;
        }


        // 2. These are the exact required amounts from your screenshot
        $recipeRequirements = [
            1 => 8, 2 => 3, 3 => 3, 4 => 2, 5 => 0.5, 6 => 6, 
            7 => 2, 8 => 28, 9 => 2, 10 => 8, 11 => 1, 12 => 8, 13 => 1
        ];

        $ingredients = Ingredient::whereIn('id', array_keys($recipeRequirements))
            ->get()
            ->keyBy('id');

        $inventoryData = [];


        // 3. Loop through and give the user enough of each ingredient
        $statuses = ['FULL', 'OPENED', 'LOW'];
        foreach ($recipeRequirements as $ingredientId => $requiredAmount) {
            $unit = isset($ingredients[$ingredientId]) 
                ? $ingredients[$ingredientId]->base_unit 
                : 'pcs';
            $inventoryData[] = [
                'user_id'         => $user->id,
                'ingredient_id'   => $ingredientId, 
                // We add 10 to the required amount so the user has leftovers after cooking
                'amount_left'     => $requiredAmount + 10,
                'unit'            => $unit,
                'status'          => $statuses[array_rand($statuses)], // Picks a random status cleanly
                'expiration_date' => now()->addDays(rand(-2, 10))->toDateString(), 
                'is_frozen'       => false, 
                'created_at'      => now(), 
                'updated_at'      => now()
            ];
        }

        // 4. Insert everything into the database at once
        DB::table('user_inventories')->insert($inventoryData);
    }
}