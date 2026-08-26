<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\UserInventory;
use App\Models\UserSetting;
use Illuminate\Support\Carbon;

class CleanExpiredInventory extends Command
{
    protected $signature = 'inventory:clean-expired';
    protected $description = 'Clean up expired inventory items and deduct zero waste scores daily';
    public function handle()
    {
        $now = Carbon::now();

        $expiredItems = UserInventory::whereNotNull('expiration_date')
            ->whereDate('expiration_date', '<', $now->toDateString())
            ->get();

        // Fetch all expired items across all users
        if($expiredItems->isEmpty()) {
            $this->info('No expired items to clean up.');
            return Command::SUCCESS;
        }

        // Group the expired items by user ID to deduct points efficiently
        $itemsByUser = $expiredItems->groupBy('user_id');

        foreach($itemsByUser as $userId => $items) {
            $pointsToDeduct = $items->count() * 15;
            
            // Delete expired items for selected user
            UserInventory::whereIn('id', $items->pluck('id'))->delete();

            // Update zero waste score
            $settings = UserSetting::where('user_id', $userId)->first();
            if($settings) {
                $currentScore = $settings->zero_waste_score ?? 0;
                $settings->update([
                    'zero_waste_score' => max(0, $currentScore - $pointsToDeduct)
                ]);
            }
        }
        $this->info('Succesfully cleaned up expired items and updated scores.');
        return Command::SUCCESS;
    }
}
