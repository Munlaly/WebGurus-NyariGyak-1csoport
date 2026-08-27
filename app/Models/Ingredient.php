<?php

namespace App\Models;

use App\Enums\BaseUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    protected function casts(): array {
        return [
            'name' => 'string',
            'base_unit' => 'string',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function recipes(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class, 'recipe_ingredients')
            ->using(RecipeIngredient::class)
            ->withPivot('amount', 'unit');
    }
}
