<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'instructions',
        'prep_time_minutes',
        'is_public',
        'image',
    ];
}
