<?php

namespace App\Http\Middleware;

use App\Models\UserInventory;
use App\Models\UserSetting;
use Carbon\Carbon;
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
        $expiringCount = 0;
        $expiringAlerts = [];
        if ($user) {
            $settings = UserSetting::where('user_id', $user->id)->first();
            if ($settings && $settings->system_preferences) {
                $prefs = $settings->system_preferences;
                $theme = $prefs['theme'] ?? 'light';
                $inAppAlerts = $prefs['inAppAlerts'] ?? true;
            }

            $alertService = app(AlertService::class);
            $expiringAlerts = $alertService->getExpiringAlertIds($user);

            $expiringAlerts = [
                'expired' => $expiringAlerts['expired']->values(),
                'critical' => $expiringAlerts['critical']->values(),
                'urgent' => $expiringAlerts['urgent']->values(),
            ];

            $expiringCount = $expiringAlerts['expired']->count() + 
                             $expiringAlerts['critical']->count() + 
                             $expiringAlerts['urgent']->count();
        }

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'username' => $user->username, 
                ] : null,
                'theme' => $theme,
                'inAppAlerts' => $inAppAlerts,
                'expiringCount' => $expiringCount,
            ],
            'expiringAlerts' => $expiringAlerts,
        ]);
    }
}
