<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSettings extends Model
{
    protected $table = 'user_settings';

    protected $fillable = [
        'user_id',
        'goals',
        'meal_plan_preference',
        'household_size',
        'prep_time_preference',
        'daily_calorie_target', 
        'custom_dislikes',
        'budget_or_comfort',
        'system_preferences',  
    ];

    protected $casts = [
        'goals' => 'array',
        'meal_plan_preference' => 'array',
        'custom_dislikes' => 'array',
        'system_preferences' => 'array', 
        'daily_calorie_target' => 'integer',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}