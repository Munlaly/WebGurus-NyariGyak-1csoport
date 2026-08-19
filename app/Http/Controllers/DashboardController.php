<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $recipes = Recipe::whereIn('id', range(1, 9))->get()->keyBy('id');

        $formatMeal = function (?Recipe $recipe) {
            if (!$recipe) {
                return null;
            }

            $imageUrl = 'https://placehold.co/600x400?text=No+Image';
            if ($recipe->image) {
                $imageUrl = str_starts_with($recipe->image, 'http')
                    ? $recipe->image
                    : asset('storage/' . $recipe->image);
            }

            return [
                'id' => $recipe->id,
                'title' => $recipe->name,
                'calories' => $recipe->calories ?? 0,
                'prepTime' => $recipe->prep_time_minutes ?? 0,
                'imageUrl' => $imageUrl,
                'imageAlt' => $recipe->name,
                'isPrepared' => false,
            ];
        };

        $mealsByOffset = [
            '-1' => array_values(array_filter([$formatMeal($recipes->get(1)), $formatMeal($recipes->get(2)), $formatMeal($recipes->get(3))])),
            '0'  => array_values(array_filter([$formatMeal($recipes->get(4)), $formatMeal($recipes->get(5)), $formatMeal($recipes->get(6))])),
            '1'  => array_values(array_filter([$formatMeal($recipes->get(7)), $formatMeal($recipes->get(8)), $formatMeal($recipes->get(9))])),
        ];

        return Inertia::render('Dashboard', [
            'mealsByOffset' => $mealsByOffset,
        ]);
    }

}