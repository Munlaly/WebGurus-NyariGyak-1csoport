<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
