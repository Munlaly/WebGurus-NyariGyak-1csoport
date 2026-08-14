<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property array|null $meal_types
 * @property array|null $diets
 * @property \Illuminate\Database\Eloquent\Collection $ingredients
 */
class Recipe extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'instructions',
        'prep_time_minutes',
        'is_public',
        'image',
        'calories',
        'protein',
        'fat',
        'carbs',
        'meal_types',
        'diets',
    ];

    protected $casts = [
        'meal_types' => 'array',
        'diets' => 'array',
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function recipeIngredients(): HasMany {
        return $this->hasMany(RecipeIngredient::class);
    }

    /**
        * @return BelongsToMany<Ingredient, $this, RecipeIngredient>
    */
    public function ingredients(): BelongsToMany {
        return $this->belongsToMany(Ingredient::class, 'recipe_ingredients')
        ->using(RecipeIngredient::class)
        ->withPivot('amount','unit');
    }
}
