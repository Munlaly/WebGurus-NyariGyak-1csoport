<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('welcome');




Route::middleware('auth')->group(function(){
    Route::get('dashboard', function(){
        return Inertia::render('Dashboard');
    })->name('dashboard');
});

require __DIR__.'/auth.php';
