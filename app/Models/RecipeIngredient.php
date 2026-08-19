<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property float $amount
 */
class RecipeIngredient extends Pivot
{
    protected $fillable = [
        'recipe_id',
        'ingredient_id',
        'amount',
    ];
}
