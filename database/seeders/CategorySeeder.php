<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run()
    {
        // Map Spoonacular "aisles" for default values (Nutritional data removed)
        $categories = [
            ['name' => 'Produce', 'default_shelf_life_days' => 7],
            ['name' => 'Meat', 'default_shelf_life_days' => 3],
            ['name' => 'Seafood', 'default_shelf_life_days' => 2],
            ['name' => 'Milk, Eggs, Other Dairy', 'default_shelf_life_days' => 14],
            ['name' => 'Spices and Seasonings', 'default_shelf_life_days' => 365],
            ['name' => 'Bakery/Bread', 'default_shelf_life_days' => 7],
            ['name' => 'Pasta and Rice', 'default_shelf_life_days' => 365],
            ['name' => 'Baking', 'default_shelf_life_days' => 365],
            ['name' => 'Cereal', 'default_shelf_life_days' => 365],
            ['name' => 'Nut butters, Jams, and Honey', 'default_shelf_life_days' => 180],
            ['name' => 'Canned and Jarred', 'default_shelf_life_days' => 730],
            ['name' => 'Frozen', 'default_shelf_life_days' => 180],
            ['name' => 'Oil, Vinegar, Salad Dressing', 'default_shelf_life_days' => 365],
            ['name' => 'Condiments', 'default_shelf_life_days' => 180],
            ['name' => 'Savory Snacks', 'default_shelf_life_days' => 180],
            ['name' => 'Sweet Snacks', 'default_shelf_life_days' => 180],
            ['name' => 'Beverages', 'default_shelf_life_days' => 365],
            ['name' => 'Tea and Coffee', 'default_shelf_life_days' => 730],
            ['name' => 'Alcoholic Beverages', 'default_shelf_life_days' => 730],
            ['name' => 'Health Foods', 'default_shelf_life_days' => 365],
            ['name' => 'Cheese', 'default_shelf_life_days' => 30],
            ['name' => 'Ethnic Foods', 'default_shelf_life_days' => 180],
            ['name' => 'Uncategorized', 'default_shelf_life_days' => 7]
        ];

        foreach ($categories as $categoryData) {
            Category::updateOrCreate(
                ['name' => $categoryData['name']],
                $categoryData
            );
        }
    }
}