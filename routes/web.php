<?php


use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\RecipeController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('welcome');

Route::get('/quiz', [QuizController::class, 'index'])->name('quiz.index');
Route::post('/quiz/save-session', [QuizController::class, 'saveSession'])->name('quiz.save-session');


Route::middleware('auth')->group(function(){
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

   Route::get('recipe/{recipe}', [RecipeController::class, 'show'])->name('recipe.show');
});

require __DIR__.'/auth.php';
