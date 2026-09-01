<?php

namespace App\Http\Controllers;

use App\Models\ShoppingListItem;
use Illuminate\Http\Request;

class ShoppingListController extends Controller
{
    public function store(Request $request) {
        $validated = $request->validate([
            'ingredient_id' => 'required|exists:ingredients,id',
            'quantity' => 'required|numeric|min:0.1',
            'unit' => 'required|string|in:g,kg,ml,l,pcs',
        ]);

        $shoppingItem = ShoppingListItem::updateOrCreate([
            'user_id' => $request->user()->id,
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
}