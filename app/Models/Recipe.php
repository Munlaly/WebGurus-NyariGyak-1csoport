<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int|null $user_id
 * @property bool $is_public
 * @property array|null $meal_types
 * @property array|null $diets
 * @property \Illuminate\Database\Eloquent\Collection<int, Ingredient> $ingredients
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
        'servings',
        'meal_types',
        'diets',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'prep_time_minutes' => 'integer',
        'calories'=> 'integer',
        'protein'=> 'decimal:2',
        'fat'=> 'decimal:2',
        'carbs' => 'decimal:2',
        'servings' => 'integer',
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
