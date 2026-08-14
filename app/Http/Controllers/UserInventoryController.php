<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserInventory;
use Illuminate\Support\Carbon;
use App\Models\UserSetting;

class UserInventoryController extends Controller
{
    public function index(Request $request) {
        $user = $request->user();
        $now = Carbon::now();
        $oneWeekFromNow = $now->copy()->addDays(7);

        $settings = UserSetting::where("user_id", $user->id)->first();

        $inventory = UserInventory::with('ingredient')
            ->where('user_id', auth()->id())
            ->orderBy('expiration_date', 'asc')
            ->get();

        $attentionNeeded = [];
        $regularInventory = [];

        foreach($inventory as $item) {
            $needsAttention = false;
            if($item->expiration_date) {
                $expDate = Carbon::parse($item->expiration_date);
                if($expDate->isBetween($now, $oneWeekFromNow)) {
                    $needsAttention = true;
                }
            }

            if($item->status === 'LOW') {
                $needsAttention = true;
            }

            if($needsAttention) {
                $attentionNeeded[] = $item;
            } else {
                $regularInventory[] = $item;
            }
        }
        
            return response()->json([
                'success' => true,
                'data' => [
                    'attention_needed' => $attentionNeeded,
                    'inventory' => $regularInventory,
                    'current_score' => $settings->zero_waste_score ?? 0
                ]
            ]);
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'ingredient_id' => 'required|exists:ingredients,id',
            'amount_left' => 'nullable|numeric',
            'status' => 'nullable|in:FULL,OPENED,LOW',
            'expiration_date' => 'nullable|date',
            'is_frozen' => 'boolean'
        ]);

        $inventoryItem = UserInventory::create([
            'user_id' => auth()->id(),
            'ingredient_id' => $validated['ingredient_id'],
            'amount_left' => $validated['amount_left'] ?? null,
            'status' => $validated['status'] ?? 'FULL',
            'expiration_date' => $validated['expiration_date'] ?? null,
            'is_frozen' => $validated['is_frozen'] ?? false,
        ]);

        return response()->json([
            'success'=> true,
            'message' => 'Item added to inventory succesfully. ',
            'data' => $inventoryItem,
        ], 201);
    }

    public function update(Request $request, $id) {
        $inventoryItem = UserInventory::where('user_id', auth()->id())->findOrFail($id);

        $validated = $request->validate([
            'amount_left' => 'nullable|numeric',
            'status' => 'nullable|in:FULL,OPENED,LOW',
            'expiration_date' => 'nullable|date',
            'is_frozen' => 'boolean'
        ]);

        $inventoryItem->update($validated);

        return response()->json([
            'success'=> true,
            'message' => 'Inventory item updated succesfully. ',
            'data' => $inventoryItem
        ]);
    }

    public function destroy(Request $request, $id) {
        $inventoryItem = UserInventory::where('user_id', auth()->id())->findOrFail($id);
        $inventoryItem->delete();

        return response()->json([
            'success' => 'true',
            'message' => 'Item removed from inventory succesfully.'
        ]);
    }
}
