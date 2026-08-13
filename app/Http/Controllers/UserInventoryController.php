<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserInventory;

class UserInventoryController extends Controller
{
    public function index(Request $request) {
        $inventory = UserInventory::with('ingredient')
            ->where('user_id', $request->user()->id)
            ->orderBy('expiration_date', 'asc')
            ->get();

            return response()->json([
                'success' => true,
                'data' => $inventory
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
            'user_id' => $request->user()->id,
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
        $inventoryItem = UserInventory::where('user_id', $request->user()->id)->findOrFail($id);

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
        $inventoryItem = UserInventory::where('user_id', $request->user()->id)->findOrFail($id);
        $inventoryItem->delete();

        return response()->json([
            'success' => 'true',
            'message' => 'Item removed from inventory succesfully.'
        ]);
    }
}
