<?php


use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\SettingsController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('welcome');

Route::get('/quiz', [QuizController::class, 'index'])->name('quiz.index');
Route::post('/quiz/save-session', [QuizController::class, 'saveSession'])->name('quiz.save-session');


Route::middleware('auth')->group(function(){
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

   Route::get('recipe/{recipe}', [RecipeController::class, 'show'])->name('recipe.show');
    Route::get('settings/targets', [SettingsController::class, 'targets'])->name('settings.targets');
    Route::get('settings/rules', [SettingsController::class, 'rules'])->name('settings.rules');
    Route::get('settings/system', [SettingsController::class, 'system'])->name('settings.system');
});

require __DIR__.'/auth.php';
