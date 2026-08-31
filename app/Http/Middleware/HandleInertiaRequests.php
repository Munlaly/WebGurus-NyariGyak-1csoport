<?php

namespace App\Http\Middleware;

use App\Models\UserInventory;
use App\Models\UserSetting;
use Carbon\Carbon;
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
        $expiringAlerts = [];
        if ($user) {
            $settings = UserSetting::where('user_id', $user->id)->first();
            if ($settings && $settings->system_preferences) {
                $prefs = $settings->system_preferences;
                $theme = $prefs['theme'] ?? 'light';
                $inAppAlerts = $prefs['inAppAlerts'] ?? true;
            }

            $now = now();
            $today = $now->copy()->startOfDay();

            $userInventory = UserInventory::with('ingredient')
                ->where('user_id', $user->id)
                ->whereNotNull('expiration_date')
                ->where('expiration_date', '<=', $now->copy()->addDays(7))
                ->get();

            $expired = $userInventory->filter(function ($item) use ($today) {
                return Carbon::parse($item->expiration_date)->lt($today);
            })->values();
            $critical = $userInventory->filter(function ($item) use ($today) {
                $date = Carbon::parse($item->expiration_date);
                return $date->gte($today) && $date->lte($today->copy()->addDays(2));
            })->values();
            $urgent = $userInventory->filter(function ($item) use ($today) {
                $date = Carbon::parse($item->expiration_date);
                return $date->gt($today->copy()->addDays(2)) && $date->lte($today->copy()->addDays(7));
            })->values();

            $expiringAlerts = [
                'expired' => $expired,
                'critical' => $critical,
                'urgent' => $urgent,
            ];

            $expiringCount = $userInventory->count();
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
