<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserInventory extends Model
{
    protected $fillable = [
        'user_id',
        'ingredient_id',
        'amount_left', 
        'status',
        'expiration_date',
        'is_frozen',
        'last_audited_at',
    ];

    protected $casts = [
        'is_frozen' => 'boolean',
        'expiration_date' => 'date',
        'last_audited_at' => 'datetime',
    ];
}
