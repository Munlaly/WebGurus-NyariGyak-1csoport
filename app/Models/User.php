<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\UserSetting;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\ResetPasswordNotification;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'username',
        'email',
        'password',
        'onboarded_at'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'onboarded_at' => 'datetime'
        ];
    }

    /**
     * @return HasOne<\App\Models\UserProfile, $this>
     */
   public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * @return HasOne<\App\Models\UserSetting, $this>
     */
    public function settings(): HasOne
    {
        return $this->hasOne(UserSetting::class);
    }

    /**
     * @return HasMany<\App\Models\Recipe, $this>
     */
    public function recipes(): HasMany
    {
        return $this->hasMany(Recipe::class);
    }

    /**
     * @return HasMany<\App\Models\UserInventory, $this>
     */
    public function inventories(): HasMany
    {
        return $this->hasMany(UserInventory::class);
    }

    /**
     * @return HasMany<\App\Models\DailyPlan, $this>
     */
    public function dailyPlans(): HasMany
    {
        return $this->hasMany(DailyPlan::class);
    }

    /**
     * @return BelongsToMany<\App\Models\DietaryOption, $this>
     */
    public function dietaryOptions(): BelongsToMany
    {
        return $this->belongsToMany(DietaryOption::class, 'user_dietary_options');
    }

    /**
     * @return BelongsToMany<\App\Models\Ingredient, $this>
     */
    public function dislikedIngredients(): BelongsToMany 
    {
        return $this->belongsToMany(Ingredient::class, 'user_disliked_ingredients');
    }

    /**
     * @return HasMany<\App\Models\UserExerciseSchedule, $this>
     */
    public function exerciseSchedules(): HasMany
    {
        return $this->hasMany(UserExerciseSchedule::class);
    }

    /**
     * @return BelongsToMany<\App\Models\Recipe, $this>
     */
    public function favoriteRecipes(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class, 'favorite_recipes')->withTimestamps();
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
