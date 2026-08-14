<?php

use App\Http\Controllers\UserInventoryController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/inventory', [UserInventoryController::class, 'index']);
    Route::post('/user/inventory', [UserInventoryController::class, 'store']);
    Route::put('/user/inventory', [UserInventoryController::class, 'update']);
    Route::delete('/user/inventory', [UserInventoryController::class, 'destroy']);

});
    