<?php
namespace App\Models;

use App\Enums\BaselineActivity;
use App\Enums\FitnessGoal;
use App\Enums\UserSex;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @property int|null $weekly_calories */
class UserProfile extends Model
{
    protected $table = 'user_profiles';

    protected $fillable = [
        'user_id',
        'sex',
        'birthdate',
        'height_cm',
        'weight_kg',
        'baseline_activity',
        'fitness_goal',
        'weekly_calorie_target',
    ];

    protected $casts = [
        'sex' => UserSex::class,
        'baseline_activity' => BaselineActivity::class,
        'fitness_goal' => FitnessGoal::class,
        'birthdate' => 'date',
        'height_cm' => 'decimal:2',
        'weight_kg' => 'decimal:2',
        'weekly_calorie_target' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}