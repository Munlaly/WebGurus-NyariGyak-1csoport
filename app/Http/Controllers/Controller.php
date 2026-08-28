<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function getRecipeImageUrl(?string $image): string {
        $imageUrl = 'https://placehold.co/600x400?text=No+Image';        
        if($image) {
            $imageUrl = str_starts_with($image, 'http')
                ? $image
                : asset('storage/' . $image);
        }
        if(str_contains($imageUrl, 'spoonacular.com')) {
            $imageUrl = str_replace(['-312x231.jpg', '-240x150.jpg', '-90x90.jpg'], '-636x393.jpg', $imageUrl);
        }

        return $imageUrl;
    }
}
