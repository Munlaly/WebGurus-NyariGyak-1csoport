<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class QuizController extends Controller
{
    //
    public function index(){
        return Inertia::render('Quiz');
    }

    public function saveSession(Request $request){
        $validated = $request->validate([
            'goals' => 'array',
            'meal_plan_preferences' => 'array',
            'household_size' => 'string|nullable',
            'prep_time_preference' => 'string|nullable',
            'budget_or_comfort' => 'string|nullable',
            'daily_calorie_target' => 'numeric|min:1300|max:4000',
            'disliked_ingredients' => 'array',
            'custom_dislikes' => 'array',
        ]);

        session(['quiz_preferences' => $validated]);

        return redirect()->route('index');
    }
}
