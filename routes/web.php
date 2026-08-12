<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('welcome');

// Temporay only for testing
Route::get('/dashboard', function(){
    return Inertia::render('Dashboard');
})->name('/dashboard');

require __DIR__.'/auth.php';
