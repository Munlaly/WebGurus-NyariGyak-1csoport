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
}
