<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Display Login Page
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('cleaner.index');
        }
        return view('auth.login');
    }

    /**
     * Handle Login Submission
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Support logging in via 'email' or 'admin' ID
        $loginField = filter_var($credentials['email'], FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        $authData = [
            $loginField => $credentials['email'],
            'password'  => $credentials['password']
        ];

        if (Auth::attempt($authData, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('cleaner.index'));
        }

        // Try direct email match fallback
        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']], $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('cleaner.index'));
        }

        return back()->withErrors([
            'login_error' => 'Invalid ID/Email or Password. Access denied.',
        ])->onlyInput('email');
    }

    /**
     * Handle Logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
