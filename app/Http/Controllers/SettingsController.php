<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserSettings;
use Illuminate\Support\Facades\DB;
class SettingsController extends Controller
{
    public function show(Request $request) {
        $user = $request->user();
        $settings = UserSettings::firstOrCreate(
            ['user_id' => $user->id],
            [
                'daily_calorie_target' => 2000,
                'prep_time_preference' => 45,
                'goals' => [],
                'meal_plan_preference' => [],
            ]
        );

        $dislikedIngredients = DB::table('user_disliked_ingredients')
            ->join('ingredients', 'user_disliked_ingredients.ingredient_id', '=', 'ingredients.id')
            ->where('user_disliked_ingredients.user_id', $user->id)
            ->select('ingredients.id', 'ingredients.name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'settings' => $settings,
                'disliked_ingredients' => $dislikedIngredients
            ]
        ]);
    }

    public function update(Request $request) {
        $user = $request->user();
        $validated = $request->validate([
            'daily_calorie_target' => 'sometimes|integer|min:1300|max:4000',
            'prep_time_preference' => 'sometimes|integer|in:20,45,999',
            'goals' => 'sometimes|array',
            'goals.*' => 'string|in:lose_weight,gain_weight,build_muscle,eat_healthy,zero_waste',

            'meal_plan_preference'=> 'sometimes|array',
            'meal_plan_preference.*' => 'string|in:omnivore,vegetarian,vegan,keto,gluten_free,dairy_free,nut_free,pescatarian',

            'disliked_ingredient_ids' => 'sometimes|array',
            'disliked_ingredient_ids.*' => 'integer|exists:ingredients,id',
        ]);

        $settingsData = collect($validated)->except('disliked_ingredient_ids')->toArray();
        $settings = UserSettings::updateOrCreate(
            ['user_id' => $user->id],
            $settingsData,
        );

        if($request->has('disliked_ingredient_ids')) {
            DB::table('user_disliked_ingredients')->where('user_id', $user->id)->delete();

            $inserts = array_map(function($ingredientId) use ($user) {
                return [
                    'user_id' => $user->id,
                    'ingredient_id' => $ingredientId,
                ];
            }, $validated['disliked_ingredient_ids']);

            if(!empty($inserts)) {
                DB::table('user_disliked_ingredients')->insert($inserts);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully.',
            'data' => $settings,
        ]);
    }
}
