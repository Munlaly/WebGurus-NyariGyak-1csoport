<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Validation\ValidationException;

class LoggedInUserController extends Controller
{
    // Show the Login page
    public function create():Response{
       return  Inertia::render('Auth/Login');
    }

    /**
     * Handle an incoming login request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */

    public function attempt(Request $request):RedirectResponse{

    $credentials = $request->validate([
        'username' => 'required|string|max:255',
        'password' => 'required|string',
    ]);

    // Check if user exists 
    if(Auth::attempt($credentials))  {
        $request->session()->regenerate();

        return redirect()->intended(route('welcome'));
    } 

    // Handle failure with generic error message
    throw ValidationException::withMessages([
            'username' => trans('auth.failed'),
        ]);

    }
}
