<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
        'carbs'
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'prep_time_minutes' => 'integer',
        'calories'=> 'integer',
        'protein'=> 'float',
        'fat'=> 'float',
        'carbs' => 'float'
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
        ->withPivot('amount');
    }
}
