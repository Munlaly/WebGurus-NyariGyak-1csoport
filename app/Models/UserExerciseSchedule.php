<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Enums\ExerciseIntensity;

class UserExerciseSchedule extends Model
{
   protected $fillable = [
    'user_id',
    'day_of_week',
    'intensity',
   ];

   protected function casts(){
    return [
'       intensity' => ExerciseIntensity::class, 
        'day_of_week' => 'integer',
    ];
   }

   public function user(): BelongsTo{
    return $this->belongsTo(User::class);
   }
}
