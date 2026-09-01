<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;


class AuthenticatedSessionController extends Controller
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

    public function attempt(LoginRequest $request):RedirectResponse{

        // Validates, rate-limits, and attempts authentication
        $request->authenticate();

        // Regenerate session to prevent fixation
        $request->session()->regenerate();

        // Check if the user abandoned the quiz previously
        if (is_null($request->user()->onboarded_at)) {
            return redirect()->route('quiz.index');
        }

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        // Log out of the guard
        Auth::guard('web')->logout();

        // Invalidate the session data
        $request->session()->invalidate();

        // Regenerate the CSRF token
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
