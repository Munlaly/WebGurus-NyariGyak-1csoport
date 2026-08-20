<?php

use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\SettingsController;


Route::get('/quiz', [QuizController::class, 'index'])->name('quiz.index');
Route::post('/quiz/save-session', [QuizController::class, 'saveSession'])->name('quiz.save-session');


Route::middleware('auth')->group(function(){
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

require __DIR__.'/auth.php';
