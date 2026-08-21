<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DietaryOption extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'slug',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_dietary_options');
    }

    public function excludedCategories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'dietary_exclusions');
    }
}