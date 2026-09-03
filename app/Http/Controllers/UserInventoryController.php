<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserInventory;
use Illuminate\Support\Carbon;
use App\Models\UserSetting;
use Inertia\Inertia;

class UserInventoryController extends Controller
{
    public function index(Request $request) {
        $user = $request->user();
        $now = Carbon::now();

        $settings = UserSetting::where("user_id", $user->id)->first();
        $inventory = UserInventory::with('ingredient.category')
            ->where('user_id', $request->user()->id)
            ->orderBy('expiration_date', 'asc')
            ->get();

        $attentionNeeded = [];

        foreach($inventory as $item) {
            $isExpiring = false;
            if($item->expiration_date) {
                $expDate = Carbon::parse($item->expiration_date)->startOfDay();
                $targetDate = $now->copy()->addDays(7)->startOfDay();
                $isExpiring = $expDate->isPast() || $expDate->isBefore($targetDate);
            }

            if($item->status === 'LOW' || $isExpiring) {
                $attentionNeeded[] = $item;
            }
        }
        
        return Inertia::render('Inventory', [
            'attentionNeeded' => $attentionNeeded,
            'inventory' => $inventory,
            'currentScore' => $settings->zero_waste_score ?? 0,
        ]);
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'ingredient_id' => 'required|exists:ingredients,id',
            'amount_left' => 'nullable|numeric',
            'unit' => 'required|string|in:g,kg,ml,l,pcs',
            'status' => 'nullable|in:FULL,OPENED,LOW',
            'expiration_date' => 'required|date',
            'is_frozen' => 'boolean'
        ]);

        UserInventory::create([
            'user_id' => $request->user()->id,
            'ingredient_id' => $validated['ingredient_id'],
            'amount_left' => $validated['amount_left'] ?? null,
            'unit' => $validated['unit'],
            'status' => $validated['status'] ?? 'FULL',
            'expiration_date' => !empty($validated['expiration_date']) 
                ? Carbon::parse($validated['expiration_date'])->format('Y-m-d') 
                : null,
            'is_frozen' => $validated['is_frozen'] ?? false,
        ]);

        return back()->with('success', 'Item added to inventory.');
    }

    public function update(Request $request, UserInventory $inventory) {
        if($inventory->user_id !== $request->user()->id) {
            abort(403);
        }
        $validated = $request->validate([
            'amount_left' => 'nullable|numeric',
            'unit' => 'required|string|in:g,kg,ml,l,pcs',
            'status' => 'nullable|in:FULL,OPENED,LOW',
            'expiration_date' => 'nullable|date',
            'is_frozen' => 'boolean'
        ]);

        if(!empty($validated['expiration_date'])) {
            $validated['expiration_date'] = Carbon::parse($validated['expiration_date'])->format('Y-m-d');
        }
        $inventory->update($validated);
        $inventory->load('ingredient');

        $amount = $inventory->amount_left ?? 0;
        $unit = $inventory->unit ?? $inventory->ingredient->base_unit ?? '';
        $itemName = $inventory->ingredient->name ?? 'item';
        $amountText = trim("{$amount} {$unit}");

        return back()->with('success', "Updated {$itemName} quantity to {$amountText} successfully.");
    }

    public function decrease(Request $request, UserInventory $inventory) {
        if($inventory->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'amount_to_remove' => 'required|numeric|min:0.1',
        ]);

        $current = $inventory->amount_left ?? 0;
        $newAmount = max(0, $current - $validated['amount_to_remove']);

        $inventory->load('ingredient');
        $unit = $inventory->ingredient->base_unit ?? '';
        $itemName = $inventory->ingredient->name ?? 'item';
        $removeAmountText = trim("{$validated['amount_to_remove']} {$unit}");
        $newAmountText = trim("{$newAmount} {$unit}");

         if($newAmount <= 0) {
            $inventory->delete();
            return back()->with('success', "You've completely used up {$itemName}.");
        }

        $inventory->update([
            'amount_left' => $newAmount,
        ]);

        return back()->with('success', "Removed {$removeAmountText} of {$itemName}. New balance: {$newAmountText}.");
    }

    public function destroy(Request $request, UserInventory $inventory) {
        if($inventory->user_id !== $request->user()->id) {
            abort(403);
        }

        $inventory->load('ingredient');

        $amount  = $inventory->amount_left ?? '';
        $unit = $inventory->ingredient->base_unit ?? '';
        $itemName = $inventory->ingredient->name ?? 'item';
        $amountText = trim("{$amount} {$unit}");

        $inventory->delete();
        return back()->with('success', "{$amountText} of {$itemName} has been removed from your inventory successfully.");
    }
}
