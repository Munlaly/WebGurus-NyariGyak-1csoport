<?php

namespace App\Http\Middleware;

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
        $theme = 'light';
        if ($request->user()) {
            $settings = \App\Models\UserSetting::where('user_id', $request->user()->id)->first();
            if ($settings && $settings->system_preferences) {
                $prefs = is_string($settings->system_preferences) 
                    ? json_decode($settings->system_preferences, true) 
                    : $settings->system_preferences;
                $theme = $prefs['theme'] ?? 'light';
            }
        }

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'username' => $request->user()->username, 
                ] : null,
                'theme' => $theme,
            ],
        ]);
    }
}
