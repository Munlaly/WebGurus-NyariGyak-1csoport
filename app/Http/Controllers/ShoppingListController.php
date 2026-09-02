<?php

namespace App\Http\Controllers;

use App\Models\ShoppingListItem;
use App\Models\UserInventory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShoppingListController extends Controller
{
    public function index(Request $request) {
        $user = $request->user();
        $items = ShoppingListItem::with('ingredient.category')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

            return Inertia::render('ShoppingList', [
                'items' => $items,
            ]);
    }
    public function store(Request $request) {
        $validated = $request->validate([
            'ingredient_id' => 'required|exists:ingredients,id',
            'quantity' => 'required|numeric|min:0.1',
            'unit' => 'required|string|in:g,kg,ml,l,pcs',
        ]);
        $user = $request->user();

        $shoppingItem = ShoppingListItem::updateOrCreate([
            'user_id' => $user->id,
            'ingredient_id' => $validated['ingredient_id'],
            'is_checked' => false,
        ], 
        [
            'quantity' => $validated['quantity'],
            'unit' => $validated['unit'],
        ]);

        $shoppingItem->load('ingredient');

        $amount = $shoppingItem->quantity ?? 0;
        $unit = $shoppingItem->ingredient->unit ?? '';
        $itemName = $shoppingItem->ingredient->name ?? 'item';
        $amountText = trim("{$amount} {$unit}");

        return back()->with('success', "Added {$amountText} of {$itemName} to your shopping list successfully.");
    }

    public function update(Request $request, ShoppingListItem $item) {
        $user = $request->user();
        if($item->user_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'is_checked' => 'sometimes|boolean',
            'quantity' => 'sometimes|numeric|min:0.1',
            'unit' => 'sometimes|string|in:g,kg,ml,l,pcs',
        ]);

        $item->update($validated);

        return back()->with('success');
    }

    public function destroy(Request $request, ShoppingListItem $item) {
        $user = $request->user();
        if($item->user_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $item->delete();

        return back()->with('success', 'Shopping list item removed successfully.');
    }

    public function finish(Request $request) {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:shopping_list_items,id',
            'items.*.expiration_date' => 'required|date',
        ]);
    
        $user = $request->user();
        $inputCollection = collect($validated['items']);
        $itemIds = $inputCollection->pluck('id');

        $shoppingItems = ShoppingListItem::whereIn('id', $itemIds)
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('id');

        if($shoppingItems->isEmpty()) {
            return back()->with('error', 'No valid items found.');
        }

        foreach($validated['items'] as $inputItem) {
            $item = $shoppingItems->get($inputItem['id']);
            if($item) {
                UserInventory::create([
                    'user_id' => $user->id,
                    'ingredient_id' => $item->ingredient_id,
                    'amount_left' => $item->quantity,
                    'unit' => $item->unit,
                    'status' => 'FULL',
                    'expiration_date' => Carbon::parse($inputItem['expiration_date'])->format('Y-m-d'),
                    'is_frozen' => false,
                ]);
            }
        }
        ShoppingListItem::whereIn('id', $shoppingItems->pluck('id'))->delete();
        return back()->with('success', 'Checked items transferred to your inventory!');
    }
}