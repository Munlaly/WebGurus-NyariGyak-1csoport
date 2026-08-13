<?php

use App\Http\Controllers\UserInventoryController;
<<<<<<< HEAD
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MealPlanController;
use App\Http\Controllers\UserRecipeController;
use App\Http\Controllers\SettingsController;
=======
use App\Http\Controllers\CookMealController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MealPlanController;
use App\Http\Controllers\UserRecipeController;
>>>>>>> 8c9d93d (Add UserInventoryController, seeder, and routes for managing user inventory. Update User and UserInventory models, and modify migrations for users and personal access tokens.)

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/inventory', [UserInventoryController::class, 'index']);
    Route::post('/user/inventory', [UserInventoryController::class, 'store']);
    Route::put('/user/inventory', [UserInventoryController::class, 'update']);
    Route::delete('/user/inventory', [UserInventoryController::class, 'destroy']);
<<<<<<< HEAD
=======
    Route::post('recipes/{id}/cook', [CookMealController::class, 'cook']);
>>>>>>> 8c9d93d (Add UserInventoryController, seeder, and routes for managing user inventory. Update User and UserInventory models, and modify migrations for users and personal access tokens.)
    Route::post('/meal-plan/generate', [MealPlanController::class,'generate']);
    Route::post('/meal-plan/regenerate-meal', [MealPlanController::class,'regenerateMeal']);
    Route::post('/meal-plan/save', [MealPlanController::class,'savePlan']);
    Route::post('/user/recipes', [UserRecipeController::class, 'store']);
    Route::put('/user/recipes/{id}', [UserRecipeController::class, 'update']);
<<<<<<< HEAD
    Route::get('/user/recipes', [UserRecipeController::class, 'index']);
    Route::get('user/settings', [SettingsController::class, 'show']);
    Route::put('user/settings', [SettingsController::class, 'update']);
});
=======

});
    
>>>>>>> 8c9d93d (Add UserInventoryController, seeder, and routes for managing user inventory. Update User and UserInventory models, and modify migrations for users and personal access tokens.)
