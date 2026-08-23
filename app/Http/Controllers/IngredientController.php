<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use Illuminate\Http\Request;

class IngredientController extends Controller
{
    //
    public function search(Request $request){
        $query = $request->input('q');

        if(empty($query)){
            return response()->json[[]];
        }

        $ingredients = Ingredient::select('id', 'name')
        ->where('name', 'like', "%{$query}%")
        ->limit(10)
        ->get();

        return response()->json($ingredients);
    }
}
