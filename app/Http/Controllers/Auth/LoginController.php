<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Log successful login
            AuditLog::logAuth('auth.login_success', Auth::id(), $credentials['email']);

            return redirect()->intended('/dashboard');
        }

        // Log failed login attempt
        $user = User::where('email', $credentials['email'])->first();
        AuditLog::logAuth('auth.login_failed', $user?->id, $credentials['email']);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function destroy(Request $request)
    {
        $userId = Auth::id();
        $userEmail = Auth::user()?->email;

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Log logout
        AuditLog::logAuth('auth.logout', $userId, $userEmail);

        return redirect('/login');
    }
}
