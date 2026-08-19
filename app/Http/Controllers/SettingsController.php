<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class SettingsController extends Controller
{
    // --- TARGETS ---
    public function targets(Request $request): Response
    {
        $settings = $request->user()->settings()->firstOrCreate([]);
        $goals = $settings->goals ?? [];

        return Inertia::render('Settings/Targets', [
            'userSettings' => [
                'mainGoal' => $goals['mainGoal'] ?? 'general',
                'calorieTarget' => $settings->daily_calorie_target ?? 2000,
            ]
        ]);
    }

    public function updateTargets(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mainGoal' => 'required|string|in:weightloss,weightgain,muscle,general',
            'calorieTarget' => 'required|integer|min:1000|max:10000',
        ]);

        $request->user()->settings()->updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'goals' => ['mainGoal' => $validated['mainGoal']], 
                'daily_calorie_target' => $validated['calorieTarget'] 
            ] 
        );

        return back();
    }

    // --- RULES ---
    public function rules(Request $request): Response
    {
        $settings = $request->user()->settings()->firstOrCreate([]);
        $diet = $settings->meal_plan_preference ?? [];
        $dislikes = $settings->custom_dislikes ?? [];

        return Inertia::render('Settings/Rules', [
            'userSettings' => [
                'prepTime' => $settings->prep_time_preference ?? 'normal',
                'numberOfPeople' => $settings->household_size ?? '1_person',
                'dietType' => $diet['dietType'] ?? 'omnivore',
                'avoidedIngredients' => implode(', ', $dislikes), 
            ],
        ]);
    }

    public function updateRules(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'prepTime' => 'required|string',
            'numberOfPeople' => 'required|string',
            'dietType' => 'required|string',
            'avoidedIngredients' => 'nullable|string',
        ]);

    
        $dislikesArray = $validated['avoidedIngredients'] 
            ? array_values(array_filter(array_map('trim', explode(',', $validated['avoidedIngredients']))))
            : [];

        $request->user()->settings()->updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'prep_time_preference' => $validated['prepTime'],
                'household_size' => $validated['numberOfPeople'],
                'meal_plan_preference' => ['dietType' => $validated['dietType']],
                'custom_dislikes' => $dislikesArray,
            ]
        );

        return back();
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
}