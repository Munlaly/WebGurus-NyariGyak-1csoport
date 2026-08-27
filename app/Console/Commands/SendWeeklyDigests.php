<?php

namespace App\Console\Commands;

use App\Models\UserSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\WeeklyDigestMail;

class SendWeeklyDigests extends Command
{
    protected $signature = 'digest:send-weekly';
    protected $description = 'Send weekly zero-waste meal plan digest to users with email digests enabled';

    public function handle()
    {
        $settingsQuery = UserSetting::where('system_preferences->emailDigests', true)
            ->with('user')
            ->get();

        $count = 0;
        foreach($settingsQuery as $setting) {
            $user = $setting->user;

            if(!$user || !$user->email) {
                continue;
            }

            $stats = [
                'mealsCount' => 7,
                'wasteSavedKg' => 1.4,
                'activeIngredientsUsed' => 5,  
            ];

            Mail::to($user->email)->send(new WeeklyDigestMail($stats));
            $count++;
        }
        $this->info("Successfully sent weekly digests to {$count} users.");
    }
}
