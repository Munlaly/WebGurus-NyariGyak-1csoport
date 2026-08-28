<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Models\UserInventory;
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
     * @see https://inertiajs.com/shared-data0
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => function () use ($request) {
                $user = $request->user();
                
                if (!$user) {
                    return [
                        'user' => null,
                        'theme' => 'light',
                    ];
                }

                /** @var \App\Models\UserSetting|null $settings */
                $settings = $user->settings;
                $sysPrefs = $settings ? $settings->system_preferences : [];

                return [
                    'user' => $user,
                    'theme' => $sysPrefs['theme'] ?? 'light',
                ];
            },
            'expiringAlerts' => function () use($request) {
                $user = $request->user();
                if(!$user) {
                    return ['expired' => [], 'critical' => [], 'urgent' => []];
                }

                /** @var \App\Models\UserSetting|null $settings */
                $settings = $user->settings;
                $inAppAlertsEnabled = $settings->system_preferences['inAppAlerts'] ?? true;

                if(!$inAppAlertsEnabled) {
                    return ['expired' => [], 'critical' => [], 'urgent' => []];
                }

                $now = Carbon::now()->startOfDay();
                $today = $now->copy();
                $tomorrow = $now->copy()->addDay();
                $oneWeekFromNow = $now->copy()->addDays(7);

                $inventory = UserInventory::where('user_id', $user->id)
                    ->with('ingredient')
                    ->whereNotNull('expiration_date')
                    ->get();

                $expired = [];
                $critical = [];
                $urgent = [];

                foreach($inventory as $item) {
                    $expDate = Carbon::parse($item->expiration_date)->startOfDay();
                    if($expDate->isBefore($today)) {
                        $expired[] = $item;
                    } elseif($expDate->equalTo($today) || $expDate->equalTo($tomorrow)) {
                        $critical[] = $item;
                    } elseif($expDate->isBetween($tomorrow, $oneWeekFromNow, false)) {
                        $urgent[] = $item;
                    }
                }

                return [
                    'expired' => $expired,
                    'critical' => $critical,
                    'urgent' => $urgent,
                ];
            }
        ];
    }
}