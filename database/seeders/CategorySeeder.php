<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run()
    {
        // Map Spoonacular "aisles" for default values
        $categories = [
            [
                'name' => 'Produce',
                'default_shelf_life_days' => 7,
                'default_calories_per_100' => 30,
                'default_protein' => 1.0,
                'default_fat' => 0.2,
                'default_carbs' => 6.0,
            ],
            [
                'name' => 'Meat',
                'default_shelf_life_days' => 3,
                'default_calories_per_100' => 250,
                'default_protein' => 26.0,
                'default_fat' => 15.0,
                'default_carbs' => 0.0,
            ],
            [
                'name' => 'Seafood',
                'default_shelf_life_days' => 2,
                'default_calories_per_100' => 100,
                'default_protein' => 20.0,
                'default_fat' => 2.0,
                'default_carbs' => 0.0,
            ],
            [
                'name' => 'Milk, Eggs, Other Dairy',
                'default_shelf_life_days' => 14,
                'default_calories_per_100' => 150,
                'default_protein' => 8.0,
                'default_fat' => 8.0,
                'default_carbs' => 12.0,
            ],
            [
                'name' => 'Spices and Seasonings',
                'default_shelf_life_days' => 365,
                'default_calories_per_100' => 0,
                'default_protein' => 0.0,
                'default_fat' => 0.0,
                'default_carbs' => 0.0,
            ],
            [
                'name' => 'Bakery/Bread',
                'default_shelf_life_days' => 7,
                'default_calories_per_100' => 260,
                'default_protein' => 9.0,
                'default_fat' => 3.0,
                'default_carbs' => 50.0,
            ],

            [
                'name' => 'Pasta and Rice',
                'default_shelf_life_days' => 365,
                'default_calories_per_100' => 350,
                'default_protein' => 12.0,
                'default_fat' => 1.5,
                'default_carbs' => 75.0,
            ],
            [
                'name' => 'Baking',
                'default_shelf_life_days' => 365,
                'default_calories_per_100' => 350,
                'default_protein' => 5.0,
                'default_fat' => 10.0,
                'default_carbs' => 60.0,
            ],
            [
                'name' => 'Cereal',
                'default_shelf_life_days' => 365,
                'default_calories_per_100' => 380,
                'default_protein' => 8.0,
                'default_fat' => 5.0,
                'default_carbs' => 75.0,
            ],
            [
                'name' => 'Nut butters, Jams, and Honey',
                'default_shelf_life_days' => 180,
                'default_calories_per_100' => 500,
                'default_protein' => 15.0,
                'default_fat' => 40.0,
                'default_carbs' => 35.0,
            ],

            [
                'name' => 'Canned and Jarred',
                'default_shelf_life_days' => 730,
                'default_calories_per_100' => 100,
                'default_protein' => 2.0,
                'default_fat' => 1.0,
                'default_carbs' => 20.0,
            ],
            [
                'name' => 'Frozen',
                'default_shelf_life_days' => 180,
                'default_calories_per_100' => 100,
                'default_protein' => 5.0,
                'default_fat' => 2.0,
                'default_carbs' => 15.0,
            ],
            [
                'name' => 'Oil, Vinegar, Salad Dressing',
                'default_shelf_life_days' => 365,
                'default_calories_per_100' => 800,
                'default_protein' => 0.0,
                'default_fat' => 90.0,
                'default_carbs' => 0.0,
            ],
            [
                'name' => 'Condiments',
                'default_shelf_life_days' => 180,
                'default_calories_per_100' => 100,
                'default_protein' => 1.0,
                'default_fat' => 5.0,
                'default_carbs' => 15.0,
            ],

            [
                'name' => 'Savory Snacks',
                'default_shelf_life_days' => 180,
                'default_calories_per_100' => 500,
                'default_protein' => 5.0,
                'default_fat' => 30.0,
                'default_carbs' => 50.0,
            ],
            [
                'name' => 'Sweet Snacks',
                'default_shelf_life_days' => 180,
                'default_calories_per_100' => 450,
                'default_protein' => 5.0,
                'default_fat' => 20.0,
                'default_carbs' => 65.0,
            ],
            [
                'name' => 'Beverages',
                'default_shelf_life_days' => 365,
                'default_calories_per_100' => 40,
                'default_protein' => 0.0,
                'default_fat' => 0.0,
                'default_carbs' => 10.0,
            ],
            [
                'name' => 'Tea and Coffee',
                'default_shelf_life_days' => 730,
                'default_calories_per_100' => 0,
                'default_protein' => 0.0,
                'default_fat' => 0.0,
                'default_carbs' => 0.0,
            ],
            [
                'name' => 'Alcoholic Beverages',
                'default_shelf_life_days' => 730,
                'default_calories_per_100' => 200,
                'default_protein' => 0.0,
                'default_fat' => 0.0,
                'default_carbs' => 15.0,
            ],
            
            [
                'name' => 'Health Foods',
                'default_shelf_life_days' => 365,
                'default_calories_per_100' => 350,
                'default_protein' => 70.0,
                'default_fat' => 5.0,
                'default_carbs' => 10.0,
            ],
            [
                'name' => 'Cheese',
                'default_shelf_life_days' => 30,
                'default_calories_per_100' => 400,
                'default_protein' => 25.0,
                'default_fat' => 33.0,
                'default_carbs' => 1.5,
            ],
            [
                'name' => 'Ethnic Foods',
                'default_shelf_life_days' => 180,
                'default_calories_per_100' => 250,
                'default_protein' => 5.0,
                'default_fat' => 15.0,
                'default_carbs' => 20.0,
            ],
            [
                'name' => 'Uncategorized',
                'default_shelf_life_days' => 7,
                'default_calories_per_100' => 150,
                'default_protein' => 5.0,
                'default_fat' => 5.0,
                'default_carbs' => 15.0,
            ]
        ];

        foreach ($categories as $categoryData) {
            Category::updateOrCreate(
                ['name' => $categoryData['name']],
                $categoryData
            );
        }
    }
}