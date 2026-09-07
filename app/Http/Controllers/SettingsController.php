<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use App\Models\DietaryOption;
use App\Services\NutritionService;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Password;


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
        /** @var \Illuminate\Database\Eloquent\Collection<\App\Models\UserExerciseSchedule> $schedules */
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

    public function updateTargets(Request $request, NutritionService $nutritionService): RedirectResponse
    {
        $validated = $request->validate([
            'fitness_goal' => 'required|string|in:lose_weight,maintain,gain_muscle',
            'schedule' => 'required|array',
            'schedule.*' => 'required|string|in:rest,moderate,heavy',
        ]);

        DB::transaction(function () use ($request, $validated, $nutritionService) {
            $user = $request->user();

            /** @var \App\Models\UserProfile $profile */
            $profile = $user->profile()->updateOrCreate(
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

            $nutritionService->updateProfileWeeklyCalories($profile);

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
                'inAppAlerts' => $sysPrefs['inAppAlerts'] ?? true,
                'emailDigests' => $sysPrefs['emailDigests'] ?? false,
            ]
        ]);
    }

    public function updateSystem(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'theme' => 'required|string|in:light,dark',
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
        $user = $request->user();

        /** @var \App\Models\UserProfile $profile */
        $profile = $user->profile()->firstOrCreate([]);

        return Inertia::render('Settings/Biometrics', [
            'profile' => [
                'sex' => $profile->sex ?? 'male',
                'birthdate' => $profile->birthdate,
                'height_cm' => $profile->height_cm ? (float) $profile->height_cm : null,
                'weight_kg' => $profile->weight_kg ? (float) $profile->weight_kg : null,
                'baseline_activity' => $profile->baseline_activity ?? 'sedentary',
            ]
        ]);
    }

    public function updateBiometrics(Request $request, NutritionService $nutritionService): RedirectResponse
    {
        $validated = $request->validate([
            'sex' => 'required|string|in:male,female',
            'birthdate' => 'required|date|before:today',
            'height_cm' => 'required|numeric|min:50|max:300',
            'weight_kg' => 'required|numeric|min:30|max:500',
            'baseline_activity' => 'required|string|in:sedentary,lightly_active,moderately_active,very_active',
        ]);

        DB::transaction(function() use ($request, $validated, $nutritionService) {
            $user = $request->user();

            /** @var \App\Models\UserProfile $profile */
            $profile = $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                $validated
            );

            $nutritionService->updateProfileWeeklyCalories($profile);
        });

        return back()->with('success', 'Biometrics updated. Caloric targets recalculated.');
    }

    // --- Logistics ---
    public function logistics(Request $request){
        $user = $request->user();

        /** @var \App\Models\UserSetting $settings */
        $settings = $user->settings()->firstOrCreate([]);

        return Inertia::render('Settings/Logistics', [
            'settings' => [
                'household_size' => (int) ($settings->household_size ?? 1),
                'prep_time_preference' => $settings->prep_time_preference ? (int) $settings->prep_time_preference : 45,
            ]
        ]);
    }

    public function updateLogistics(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'household_size' => 'required|integer|min:1|max:20',
            'prep_time_preference' => 'required|integer|in:15,30,45,60',
        ]);

        $user = $request->user();

        $user->settings()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'household_size' => $validated['household_size'],
                'prep_time_preference' => $validated['prep_time_preference'],
            ]
        );

        return back()->with('success', 'Kitchen logistics updated successfully.');
    }

    // --- Security ---
    public function security(Request $request){
        return Inertia::render('Settings/Security', [
            'user' => [
                'username' => $request->user()->username, 
                'email' => $request->user()->email,       
            ],
            'status' => session('status'),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)], 
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ]); 

        $user->fill($validated);

        if ($user->isDirty('email')) {

            $user->email_verified_at = null; 
            
           // TODO add email verification
        }

        $user->save();

        return back()->with('success', 'Profile details updated.');
    }

    public function sendPasswordResetLink(Request $request): RedirectResponse
    {
        $status = Password::sendResetLink(
            ['email' => $request->user()->email]
        );

        return $status == Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }
}
