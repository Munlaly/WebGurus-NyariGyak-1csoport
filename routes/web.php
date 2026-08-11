<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('welcome', function () {
    return Inertia::render('Welcome');
});

Route::get('register', function(){
    return Inertia:: render('Register');
});
