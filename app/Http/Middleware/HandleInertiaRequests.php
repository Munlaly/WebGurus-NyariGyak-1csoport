<?php

namespace App\Http\Middleware;

use App\Models\UserInventory;
use Illuminate\Http\Request;
use Inertia\Middleware;

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
        if ($user) {
            $settings = \App\Models\UserSetting::where('user_id', $user->id)->first();
            if ($settings && $settings->system_preferences) {
                $prefs = is_string($settings->system_preferences) 
                    ? json_decode($settings->system_preferences, true) 
                    : $settings->system_preferences;
                $theme = $prefs['theme'] ?? 'light';
                $inAppAlerts = $prefs['inAppAlerts'] ?? true;
            }

            $expiringCount = UserInventory::where('user_id', $user->id)
                ->whereNotNull('expiration_date')
                ->where('expiration_date', '<=', now()->addDays(7))
                ->count();
        }

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'username' => $user->username, 
                ] : null,
                'theme' => $theme,
                'inappAlerts' => $inAppAlerts,
                'expiringCount' => $expiringCount,
            ],
        ]);
    }
}
