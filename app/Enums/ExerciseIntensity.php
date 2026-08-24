<?php
namespace App\Enums;

enum ExerciseIntensity: string 
{
    case Rest = 'rest';
    case Moderate = 'moderate';
    case Heavy = 'heavy';
}