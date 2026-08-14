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
        'custom_dislikes',
        'budget_or_comfort',
        'daily_calorie_target',
        'zero_waste_score',
    ];

    protected $casts = [
        'goals' => 'array',
        'meal_plan_preference'=> 'array',
        'household_size' => 'integer',
        'prep_time_preference'=> 'integer',
        'custom_dislikes' => 'array',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
