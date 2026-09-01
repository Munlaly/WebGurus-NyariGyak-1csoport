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

        if ($user) {
            $settings = UserSetting::where('user_id', $user->id)->first();
            if ($settings && $settings->system_preferences) {
                $prefs = $settings->system_preferences;
                $theme = $prefs['theme'] ?? 'light';
                $inAppAlerts = $prefs['inAppAlerts'] ?? true;
            }
        }

        $getAlerts = function () use ($user) {
            static $alerts = null;
            if ($alerts === null && $user) {
                $alerts = app(AlertService::class)->getExpiringAlertIds($user);
            }
            return $alerts;
        };

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'username' => $user->username, 
                ] : null,
                'theme' => $theme,
                'inAppAlerts' => $inAppAlerts,
                'expiringCount' => function() use ($user, $getAlerts) {
                    if (!$user) {
                        return 0;
                    }
                    $alerts = $getAlerts();
                    return $alerts['expired']->count() + $alerts['critical']->count() + $alerts['urgent']->count();
                },
            ],
            'expiringAlerts' => function() use ($user, $getAlerts) {
                if(!$user) {
                    return [];
                }
                $alerts = $getAlerts();
                $formatAlert = function($item) {
                    return [
                        'id' => $item->id,
                        'expiration_date' => $item->expiration_date,
                        'ingredient' => $item->ingredient ? [
                            'name' => $item->ingredient->name,
                        ]: null,
                    ];
                };

                return [
                    'expired' => $alerts['expired']->values()->map($formatAlert)->toArray(),
                    'critical' => $alerts['critical']->values()->map($formatAlert)->toArray(),
                    'urgent' => $alerts['urgent']->values()->map($formatAlert)->toArray(),
                ];
            },
        ]);
    }
}
