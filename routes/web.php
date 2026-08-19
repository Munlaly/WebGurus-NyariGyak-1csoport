<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\QuizController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('welcome');

Route::get('/quiz', [QuizController::class, 'index'])->name('quiz.index');
Route::post('/quiz/save-session', [QuizController::class, 'saveSession'])->name('quiz.save-session');


Route::middleware('auth')->group(function(){
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('recipe/{id}', function($id) {
        return Inertia::render('Recipe', [
            'recipe' => [
                'id' => (int) $id,
                'title' => 'ZeroWaste Lemon Herb Chicken',
                'prepTime' => 25,
                'calories' => 320,
                'imageUrl' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuA3m7k93-d10NLrltG07GcDwJgH-4P6to5So3Lz3_TTsjVGOnBJtT0yGHI5ulYy_UhZ3pQxZ_b1KhqQmhF4u53DkAJUcEs62ZOmABKCoibISrwvIFLZT618HYrVxmuXgYA4SIZvlB5X7a5iQbiPNNK-dX_NuVfYyYoAaxLciqBQNHweTKC4UmnZQKaIgvRp6pIqmjC3ZoPpXXfjPdqL6GPAW3tSEavwDr_nrvj3FjyeAbwJ-BlNPZkH',
                'imageAlt' => 'A professional top-down food photography shot of a perfectly seared lemon herb chicken breast',
                'isZeroWaste' => true,
                'macros' => [
                    'protein' => 45,
                    'carbs' => 12,
                    'fat' => 22,
                ],
                'ingredients' => [
                    ['name' => 'Chicken Breast', 'amount' => '150g'],
                    ['name' => 'Olive Oil', 'amount' => '10g'],
                    ['name' => 'Spinach', 'amount' => '50g'],
                    ['name' => 'Lemon', 'amount' => '1'],
                ],
                'instructions' => [
                    'Pat chicken dry and season with herbs.',
                    'Heat oil in a skillet over medium heat.',
                    'Cook chicken for 6-7 mins per side until golden.',
                    'Add lemon slices and spinach in the last 2 minutes.',
                ],
            ]
        ]);
    })->name('recipe.show');
});

require __DIR__.'/auth.php';
