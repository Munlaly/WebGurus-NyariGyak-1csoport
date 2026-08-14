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
    ];

    protected $casts = [
        'goals' => 'array',
        'meal_plan_preference'=> 'array',
        'household_size' => 'array',
        'prep_time_preference'=> 'array',
        'custom_dislikes' => 'array',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
