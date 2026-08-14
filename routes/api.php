<?php

use App\Http\Controllers\UserInventoryController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MealPlanController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/inventory', [UserInventoryController::class, 'index']);
    Route::post('/user/inventory', [UserInventoryController::class, 'store']);
    Route::put('/user/inventory', [UserInventoryController::class, 'update']);
    Route::delete('/user/inventory', [UserInventoryController::class, 'destroy']);
    Route::post('/meal-plan/generate', [MealPlanController::class,'generate']);
    Route::post('/meal-plan/regenerate-meal', [MealPlanController::class,'regenerateMeal']);
    Route::post('/meal-plan/save', [MealPlanController::class,'savePlan']);

});
    