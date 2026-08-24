<?php
namespace App\Enums;

enum FitnessGoal: string
{
    case LoseWeight = 'lose_weight';
    case Maintain = 'maintain';
    case GainMuscle = 'gain_muscle';
}