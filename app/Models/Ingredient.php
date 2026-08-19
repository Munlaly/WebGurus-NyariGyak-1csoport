<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property-read RecipeIngredient $pivot
 */
class Ingredient extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'base_unit',
    ];
}
