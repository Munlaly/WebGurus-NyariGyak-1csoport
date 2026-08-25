<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use App\Models\DietaryOption;

class SettingsController extends Controller
{
    // --- TARGETS ---
    public function targets(Request $request): Response
    {
        $settings = $request->user()->settings()->firstOrCreate([]);
        $goals = $settings->goals ?? [];

        return Inertia::render('Settings/Targets', [
            'userSettings' => [
                // Safely read the first element for the current UI, or default to 'general'
                'mainGoal' => !empty($goals) ? $goals[0] : 'general',
                'calorieTarget' => $settings->daily_calorie_target ?? 2000,
            ]
        ]);
    }

    public function updateTargets(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mainGoal' => 'required', 
            'calorieTarget' => 'required|integer|min:1000|max:10000',
        ]);

        $goalsArray = is_array($validated['mainGoal']) ? $validated['mainGoal'] : [$validated['mainGoal']];

        $request->user()->settings()->updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'goals' => $goalsArray, 
                'daily_calorie_target' => $validated['calorieTarget'] 
            ] 
        );

        return back();
    }

    // --- RULES ---
    public function rules(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Settings/DietaryRules', [
            'activeDiets' => $user->dietaryOptions()->pluck('dietary_options.id')->toArray(),
            'dislikedIngredients' => $user->dislikedIngredients()
                ->select('ingredients.id', 'ingredients.name as label')
                ->get(),
            'availableDietOptions' => DietaryOption::select('id', 'name', 'description')->get(),
        ]);
    }

    public function updateRules(Request $request): RedirectResponse
    {
        $validated = $request->validate([
        'activeDiets' => 'present|array',
        'activeDiets.*' => 'integer|exists:dietary_options,id',
        'dislikedIngredients' => 'present|array',
        'dislikedIngredients.*' => 'integer|exists:ingredients,id',
        ]);

        DB::transaction(function () use ($request, $validated) {
        $user = $request->user();
        $user->dietaryOptions()->sync($validated['activeDiets']);
        $user->dislikedIngredients()->sync($validated['dislikedIngredients']);
        });

        return back()->with('success', 'Dietary rules updated.');
    }

    // --- SYSTEM ---
    public function system(Request $request): Response
    { 
        $settings = $request->user()->settings()->firstOrCreate([]);
        $sysPrefs = $settings->system_preferences ?? [];

       return Inertia::render('Settings/System', [
            'userSettings' => [
                'theme' => $sysPrefs['theme'] ?? 'light',
                'pushNotifications' => $sysPrefs['pushNotifications'] ?? true,
                'inAppAlerts' => $sysPrefs['inAppAlerts'] ?? true,
                'emailDigests' => $sysPrefs['emailDigests'] ?? false,
            ]
        ]);
    }

    public function updateSystem(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'theme' => 'required|string|in:light,dark',
            'pushNotifications' => 'required|boolean',
            'inAppAlerts' => 'required|boolean',
            'emailDigests' => 'required|boolean',
        ]);

        $request->user()->settings()->updateOrCreate(
            ['user_id' => $request->user()->id],
            ['system_preferences' => $validated] 
        );

        return back();
    }

    // --- Biometrics ---
    public function biometrics(Request $request){
        return Inertia::render('Settings/Biometrics');
    }

    // --- Logistics ---
    public function logistics(Request $request){
        return Inertia::render('Settings/Logistics');
    }

    // --- Security ---
    public function security(Request $request){
        return Inertia::render('Settings/Security');
    }
}
