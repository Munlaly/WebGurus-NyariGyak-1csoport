<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Carbon;
use App\Models\UserInventory;

class AlertService
{
    public function getExpiringAlertIds(User $user): array
    {
        $now = Carbon::now()->startOfDay();
        $inTwoDays = $now->copy()->addDays(2)->endOfDay();
        $inSevenDays = $now->copy()->addDays(7)->endOfDay();
        
        $inventory = UserInventory::with('ingredient')
            ->where('user_id', $user->id)
            ->whereNotNull('expiration_date')
            ->where('expiration_date', '<=', $inSevenDays)
            ->orderBy('expiration_date', 'asc')
            ->get();

        $expiringAlerts = [
            'expired' => collect(),
            'critical' => collect(),
            'urgent' => collect(),
        ];

        foreach($inventory as $item) {
            $expDate = Carbon::parse($item->expiration_date)->startOfDay();
            if ($expDate->isBefore($now)) {
                $expiringAlerts['expired']->push($item);
            } elseif ($expDate->isBetween($now, $inTwoDays)) {
                $expiringAlerts['critical']->push($item);
            } elseif ($expDate->isBetween($now, $inSevenDays)) {
                $expiringAlerts['urgent']->push($item);
            }
        }

        return $expiringAlerts;
    }
}