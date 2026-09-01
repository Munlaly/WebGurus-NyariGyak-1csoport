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

        ShoppingListItem::updateOrCreate([
            'user_id' => $request->user()->id,
            'ingredient_id' => $validated['ingredient_id'],
            'is_checked' => false,
        ], 
        [
            'quantity' => $validated['quantity'],
            'unit' => $validated['unit'],
        ]);

        return back()->with('success', 'Item successfully added to your shopping list.');
    }
}