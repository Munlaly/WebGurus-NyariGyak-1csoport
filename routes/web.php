<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('welcome', function () {
    return Inertia::render('Welcome');
});

require __DIR__.'/auth.php';
