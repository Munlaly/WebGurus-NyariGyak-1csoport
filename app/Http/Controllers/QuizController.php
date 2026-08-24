<?php

namespace App\Http\Controllers;

use App\Models\DietaryOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class QuizController extends Controller
{
    public function index(Request $request)
    {
        // Prevent re-entry 
        if ($request->user()->onboarded_at !== null) {
            return redirect()->route('dashboard'); 
        }

        return Inertia::render('QuizMain', [
           
            'dietaryOptions' => DietaryOption::select('id', 'name', 'description')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        // Prevent resubmissions for post request
        if ($user->onboarded_at !== null) {
            return redirect()->route('dashboard'); 
        }

        // Validate the incoming payload 
        $validated = $request->validate([
            'fitness_goal' => 'required|in:lose_weight,maintain,gain_muscle',
            'sex' => 'required|in:male,female',
            'birthdate' => 'required|date|before:-1 year|after:-100 years',
            'height_cm' => 'required|numeric|min:1',
            'weight_kg' => 'required|numeric|min:1',
            'baseline_activity' => 'required|in:sedentary,lightly_active,moderately_active,very_active',
            'household_size' => 'required|integer|min:1',
            'prep_time_preference' => 'required|integer|min:1',
            
            // Arrays
            'meal_plan_preferences' => 'present|array',
            'meal_plan_preferences.*' => 'integer|exists:dietary_options,id',
            'disliked_ingredients' => 'present|array',
            'disliked_ingredients.*' => 'integer|exists:ingredients,id',
            
            // Nested Schedule
            'exercise_schedule' => 'required|array',
            'exercise_schedule.monday' => 'required|in:rest,moderate,heavy',
            'exercise_schedule.tuesday' => 'required|in:rest,moderate,heavy',
            'exercise_schedule.wednesday' => 'required|in:rest,moderate,heavy',
            'exercise_schedule.thursday' => 'required|in:rest,moderate,heavy',
            'exercise_schedule.friday' => 'required|in:rest,moderate,heavy',
            'exercise_schedule.saturday' => 'required|in:rest,moderate,heavy',
            'exercise_schedule.sunday' => 'required|in:rest,moderate,heavy',
        ]);

      // Transaction to prevent corrupted db state
        DB::transaction(function () use ($user, $validated) {
            
            // Save Profile Data
            $user->profile()->create([
                'sex' => $validated['sex'],
                'birthdate' => $validated['birthdate'],
                'height_cm' => $validated['height_cm'],
                'weight_kg' => $validated['weight_kg'],
                'baseline_activity' => $validated['baseline_activity'],
                'fitness_goal' => $validated['fitness_goal'],
            ]);

            // Save App Settings
            $user->settings()->create([
                'household_size' => $validated['household_size'],
                'prep_time_preference' => $validated['prep_time_preference'],
            ]);

            // Map string days to integer values for the DB
            $dayMapping = [
                'monday' => 1,
                'tuesday' => 2,
                'wednesday' => 3,
                'thursday' => 4,
                'friday' => 5,
                'saturday' => 6,
                'sunday' => 7,
            ];

            // Transform the data into individual records
            $exerciseRecords = [];
            foreach ($validated['exercise_schedule'] as $day => $intensity) {
                $exerciseRecords[] = [
                    'day_of_week' => $dayMapping[$day],
                    'intensity' => $intensity,
                ];
            }

            // Save Exercise Schedule using createMany
            $user->exerciseSchedules()->createMany($exerciseRecords);

            // Sync Many-to-Many Relationships
            $user->dietaryOptions()->sync($validated['meal_plan_preferences']);
            $user->dislikedIngredients()->sync($validated['disliked_ingredients']);

            // Mark as Onboarded so cannot fill the quiz again
            $user->update(['onboarded_at' => now()]);
        });

        
        return redirect()->route('dashboard');
    }
}