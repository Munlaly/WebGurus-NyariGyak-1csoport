<?php

use App\Http\Controllers\UserInventoryController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MealPlanController;
use App\Http\Controllers\UserRecipeController;
use App\Http\Controllers\SettingsController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/inventory', [UserInventoryController::class, 'index']);
    Route::post('/user/inventory', [UserInventoryController::class, 'store']);
    Route::put('/user/inventory', [UserInventoryController::class, 'update']);
    Route::delete('/user/inventory', [UserInventoryController::class, 'destroy']);
    Route::post('/meal-plan/generate', [MealPlanController::class,'generate']);
    Route::post('/meal-plan/regenerate-meal', [MealPlanController::class,'regenerateMeal']);
    Route::post('/meal-plan/save', [MealPlanController::class,'savePlan']);
    Route::post('/user/recipes', [UserRecipeController::class, 'store']);
    Route::put('/user/recipes/{id}', [UserRecipeController::class, 'update']);
    Route::get('/user/recipes', [UserRecipeController::class, 'index']);
    Route::get('user/settings', [SettingsController::class, 'show']);
    Route::put('user/settings', [SettingsController::class, 'update']);
});