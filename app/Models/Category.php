<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'parent_id',
        'name',
        'default_shelf_life_days',
        'default_calories_per_100',
        'default_protein',
        'default_fat',
        'default_carbs',
    ];
}
