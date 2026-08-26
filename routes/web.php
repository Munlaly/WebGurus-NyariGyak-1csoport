<?php

use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\SettingsController;
use App\Http\Middleware\EnsureUserIsOnboarded;
use App\Http\Controllers\IngredientController;



Route::middleware('auth')->group(function(){
    Route::middleware(EnsureUserIsOnboarded::class)->group(function(){
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('recipe/{recipe}', [RecipeController::class, 'show'])->name('recipe.show');

        Route::prefix('settings')->name('settings.')->group(function () {
        
        Route::get('targets', [SettingsController::class, 'targets'])->name('targets');
        Route::put('targets', [SettingsController::class, 'updateTargets']);
        
        Route::get('rules', [SettingsController::class, 'rules'])->name('rules');
        Route::put('rules', [SettingsController::class, 'updateRules']);
        
        Route::get('system', [SettingsController::class, 'system'])->name('system');
        Route::put('system', [SettingsController::class, 'updateSystem']);
    });
    });
    

    Route::prefix('quiz')->name('quiz.')->group(function (){
        Route::get('index', [QuizController::class, 'index'])->name('index');
        Route::post('store', [QuizController::class, 'store'])->name('store');
    });

    
    Route::get('ingredients/search', [IngredientController::class, 'search'])->name('ingredients.search');

});

require __DIR__.'/auth.php';
require __DIR__.'/api.php';
