<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealPlan extends Model
{
    protected $fillable = [
        'user_id',
        'recipe_id',
        'scheduled_date',
        'meal_type',
        'status',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
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
