<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\DietaryOption;
use App\Models\Category;

class DietaryOptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define the diets, their descriptions, and the categories they MUST exclude
        $diets = [
            [
                'name' => 'Vegan',
                'description' => 'No meat, poultry, fish, dairy, eggs, or other animal products like honey.',
                'excluded_categories' => [
                    'Meat', 
                    'Seafood', 
                    'Milk, Eggs, Other Dairy', 
                    'Cheese', 
                    'Jams and Honey' 
                ]
            ],
            [
                'name' => 'Vegetarian',
                'description' => 'No meat, poultry, or fish. Dairy and eggs are allowed.',
                'excluded_categories' => [
                    'Meat', 
                    'Seafood'
                ]
            ],
            [
                'name' => 'Pescatarian',
                'description' => 'No meat or poultry, but fish and seafood are allowed.',
                'excluded_categories' => [
                    'Meat'
                ]
            ],
            [
                'name' => 'Lactose-Free',
                'description' => 'Excludes all dairy products containing lactose.',
                'excluded_categories' => [
                    'Milk, Eggs, Other Dairy', 
                    'Cheese'
                ]
            ],
            [
                'name' => 'Gluten-Free',
                'description' => 'Excludes wheat, barley, rye, and standard baked goods or pastas.',
                'excluded_categories' => [
                    'Bakery/Bread', 
                    'Pasta and Rice', 
                    'Cereal'
                ]
            ],
            [
                'name' => 'Keto',
                'description' => 'High fat, very low carbohydrate diet. Excludes grains and sugars.',
                'excluded_categories' => [
                    'Bakery/Bread', 
                    'Pasta and Rice', 
                    'Cereal', 
                    'Jams and Honey', 
                    'Sweet Snacks'
                ]
            ],
            [
                'name' => 'Omnivore',
                'description' => 'Eats both plant and animal origin foods. No restrictions.',
                'excluded_categories' => [] 
            ],
        ];

        // Fetch all category IDs mapped by their names to minimize database queries
        $categoryMap = Category::pluck('id', 'name')->toArray();

        // Insert diets and their exclusions
        foreach ($diets as $dietData) {
            // Create the dietary option
            $dietaryOption = DietaryOption::updateOrCreate(
                ['slug' => Str::slug($dietData['name'])],
                [
                    'name' => $dietData['name'],
                    'description' => $dietData['description']
                ]
            );

            // Map excluded categories to their IDs
            $exclusionInserts = [];
            foreach ($dietData['excluded_categories'] as $categoryName) {
                if (isset($categoryMap[$categoryName])) {
                    $exclusionInserts[] = [
                        'dietary_option_id' => $dietaryOption->id,
                        'category_id' => $categoryMap[$categoryName]
                    ];
                }
            }

            // Insert exclusions directly into the pivot table if there are any
            if (!empty($exclusionInserts)) {
                // Clear existing exclusions to prevent unique constraint violations on re-seeding
                DB::table('dietary_exclusions')
                    ->where('dietary_option_id', $dietaryOption->id)
                    ->delete();
                    
                DB::table('dietary_exclusions')->insert($exclusionInserts);
            }
        }
    }
}