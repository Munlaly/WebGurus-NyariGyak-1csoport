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
use App\Http\Controllers\UserInventoryController;



Route::middleware('auth')->group(function(){
    Route::middleware(EnsureUserIsOnboarded::class)->group(function(){
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('recipe/{recipe}', [RecipeController::class, 'show'])->name('recipe.show');
        Route::get('alerts', [DashboardController::class, 'alerts'])->name('alerts'); 
        Route::delete('inventory/{id}', [UserInventoryController::class, 'destroy'])->name('inventory.destroy');

        Route::prefix('settings')->name('settings.')->group(function () {
        
        Route::get('targets', [SettingsController::class, 'targets'])->name('targets');
        Route::put('targets', [SettingsController::class, 'updateTargets']);
        
        Route::get('rules', [SettingsController::class, 'rules'])->name('rules');
        Route::put('rules', [SettingsController::class, 'updateRules']);
        
        Route::get('system', [SettingsController::class, 'system'])->name('system');
        Route::put('system', [SettingsController::class, 'updateSystem']);

        Route::get('biometrics', [SettingsController::class, 'biometrics'])->name('biometrics');
        Route::put('biometrics', [SettingsController::class, 'updateBiometrics']);

        Route::get('logistics', [SettingsController::class, 'logistics'])->name('logistics');
        Route::put('logistics', [SettingsController::class, 'updateLogistics']);

        Route::get('security', [SettingsController::class, 'security'])->name('security');
        Route::patch('security/profile', [SettingsController::class, 'updateProfile'])->name('profile.update');
        Route::put('security/password', [SettingsController::class, 'updatePassword'])->name('password.update');
        });
    });
    

    Route::prefix('quiz')->name('quiz.')->group(function (){
        Route::get('index', [QuizController::class, 'index'])->name('index');
        Route::post('store', [QuizController::class, 'store'])->name('store');
    });

    
    Route::get('ingredients/search', [IngredientController::class, 'search'])->name('ingredients.search');

});

require __DIR__.'/auth.php';
