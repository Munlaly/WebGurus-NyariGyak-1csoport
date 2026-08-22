<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyPlan extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'day_type',
        'target_calories',
        'status',
        'target_protein_g',
        'target_carbs_g',
        'target_fat_g',
    ];

    protected $casts = [
        'date' => 'date',
        'target_calories' => 'integer',
        'target_protein_g' => 'integer',
        'target_carbs_g' => 'integer',
        'target_fat_g' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mealPlans(): HasMany
    {
        return $this->hasMany(MealPlan::class);
    }
}