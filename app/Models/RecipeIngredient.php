<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property float $amount
 */
class RecipeIngredient extends Pivot
{
    protected $table = 'recipe_ingredients';
    
    protected $fillable = [
        'recipe_id',
        'ingredient_id',
        'amount',
        'unit',
    ];

    protected $casts = [
        'amount' => 'decimal:2', 
    ];
}
