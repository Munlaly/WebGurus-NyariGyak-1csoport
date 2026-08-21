<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSetting extends Model
{
    protected $table = 'user_settings';

    protected $fillable = [
        'user_id',
        'goals',
        'household_size',
        'prep_time_preference',
        'system_preferences',
    ];

    protected $casts = [
        'goals' => 'array',
        'household_size' => 'integer',
        'system_preferences' => 'array',
    ];

    public function user(): BelongsTo{
        return $this->belongsTo(User::class);
    }
}