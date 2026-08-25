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
    private const DAY_MAP = [
        1 => 'monday', 
        2 => 'tuesday', 
        3 => 'wednesday', 
        4 => 'thursday', 
        5 => 'friday', 
        6 => 'saturday', 
        7 => 'sunday'
    ];

    // --- TARGETS ---
    public function targets(Request $request): Response
    {
        $user = $request->user();
        $profile = $user->profile()->firstOrCreate([]);
        $schedules = $user->exerciseSchedules()->get();

        $scheduleData = [
            'monday' => 'rest', 'tuesday' => 'rest', 'wednesday' => 'rest',
            'thursday' => 'rest', 'friday' => 'rest', 'saturday' => 'rest', 'sunday' => 'rest',
        ];

        foreach ($schedules as $schedule) {
            if (isset(self::DAY_MAP[$schedule->day_of_week])) {
                $dayString = self::DAY_MAP[$schedule->day_of_week];
                $scheduleData[$dayString] = $schedule->intensity;
            }
        }

        return Inertia::render('Settings/Targets', [
            'profile' => [
                'fitness_goal' => $profile->fitness_goal ?? 'maintain',
            ],
            'schedule' => $scheduleData,
        ]);
    }

    public function updateTargets(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'fitness_goal' => 'required|string|in:lose_weight,maintain,gain_muscle',
            'schedule' => 'required|array',
            'schedule.*' => 'require|string|in:rest,moderate,heavy',
        ]);

        DB::transaction(function () use ($request, $validated) {
            $user = $request->user();

            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                ['fitness_goal' => $validated['fitness_goal']]
            );

            $reverseDayMap = array_flip(self::DAY_MAP);

            foreach ($validated['schedule'] as $dayString => $intensity) {
                $dayInt = $reverseDayMap[$dayString];
                
                $user->exerciseSchedules()->updateOrCreate(
                    ['day_of_week' => $dayInt], 
                    ['intensity' => $intensity] 
                );
            }

            // TODO Recalculate weekly calorie target

        });

         return back()->with('success', 'Targets and schedule updated successfully.');
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
