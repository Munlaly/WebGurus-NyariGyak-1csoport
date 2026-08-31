<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $meal_type
 * @property \App\Models\Recipe $recipe
 */
class MealPlan extends Model
{
    protected $fillable = [
        'daily_plan_id',
        'recipe_id',
        'meal_type',
        'status',
    ];

    protected $casts = [
    ];

    public function dailyPlan(): BelongsTo
    {
        return $this->belongsTo(DailyPlan::class);
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }
}
