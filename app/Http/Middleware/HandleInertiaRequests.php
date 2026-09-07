<?php

namespace App\Http\Middleware;

use App\Models\UserSetting;
use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Services\AlertService;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        if (app()->environment('testing')) {
            return 'testing';
        }
        
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $theme = 'light';
        $inAppAlerts = true;
        $unitSystem = 'metric';
        $alertsCache = null;

        $topbarData = [
            'macros' => null,
            'mealsCooked' => ['current' => 0, 'total' => 0],
        ];

        if ($user) {
            $settings = UserSetting::where('user_id', $user->id)->first();
            if ($settings && $settings->system_preferences) {
                $prefs = $settings->system_preferences;
                $theme = $prefs['theme'] ?? 'light';
                $inAppAlerts = $prefs['inAppAlerts'] ?? true;
                $unitSystem = $prefs['unitSystem'] ?? 'metric';
            }

            $todayPlan = $user->dailyPlans()
                ->whereDate('date', now()->toDateString())
                ->with('mealPlans.recipe') 
                ->first();

            $currentCals = 0;
            $currentProtein = 0;
            $currentCarbs = 0;
            $currentFat = 0;

            $targetCals = 0;
            $targetProtein = 0;
            $targetCarbs = 0;
            $targetFat = 0;

            $mealsCooked = 0;
            $mealsTotal = 0;

            if ($todayPlan) {
                $targetCals = $todayPlan->target_calories ?? 0;
                $targetProtein = $todayPlan->target_protein_g ?? 0;
                $targetCarbs = $todayPlan->target_carbs_g ?? 0;
                $targetFat = $todayPlan->target_fat_g ?? 0;

                $mealsTotal = $todayPlan->mealPlans->count();
                $eatenMeals = $todayPlan->mealPlans->where('status', 'EATEN');
                $mealsCooked = $eatenMeals->count();

                foreach ($eatenMeals as $mealPlan) {
                    if ($recipe = $mealPlan->recipe) {
                        $currentCals += $recipe->calories ?? 0;
                        $currentProtein += $recipe->protein ?? 0;
                        $currentCarbs += $recipe->carbs ?? 0;
                        $currentFat += $recipe->fat ?? 0;
                    }
                }
            }

            $topbarData['macros'] = [
                'calories' => ['current' => $currentCals, 'target' => $targetCals],
                'protein' => ['current' => $currentProtein, 'target' => $targetProtein],
                'carbs' => ['current' => $currentCarbs, 'target' => $targetCarbs],
                'fat' => ['current' => $currentFat, 'target' => $targetFat],
            ];

            $topbarData['mealsCooked'] = [
                'current' => $mealsCooked,
                'total' => $mealsTotal,
            ];
        }

        $getAlerts = function () use ($user) {
            static $alerts = null;
            if ($alerts === null && $user) {
                $alerts = app(AlertService::class)->getExpiringAlertIds($user);
            }
            return $alerts;
        };

        $resolveAlerts = function () use (&$alertsCache, $getAlerts) {
            return $alertsCache ??= $getAlerts();
        };

        return array_merge(parent::share($request), [
            'topbarData' =>$topbarData,
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'username' => $user->username, 
                ] : null,
                'theme' => $theme,
                'inAppAlerts' => $inAppAlerts,
                'unitSystem' => $unitSystem,
                'expiringCount' => function() use ($user, $resolveAlerts) {
                    if (!$user) {
                        return 0;
                    }
                    $alerts = $resolveAlerts();
                    return $alerts['expired']->count() + $alerts['critical']->count() + $alerts['urgent']->count();
                },
            ],
            'expiringAlerts' => fn() => $resolveAlerts(),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ]);
    }
}
