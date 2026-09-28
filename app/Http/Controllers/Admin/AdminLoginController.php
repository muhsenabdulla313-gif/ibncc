<?php

namespace App\Http\Controllers\Admin;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;

class AdminLoginController extends Controller
{




      public function showLogin()
    {
        if (Auth::check() && Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.login');
    }

    // runs when the form is submitted
    public function login(Request $request)
    {
        // 1. validate the input
        $credentials = $request->validate([
            'email'    => 'required|email|max:100',
            'password' => 'required|string|min:6|max:64',
        ]);

        // 2. block after 5 wrong tries per minute
        $key = Str::lower($credentials['email']) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'email' => 'Too many attempts. Try again in ' . RateLimiter::availableIn($key) . ' seconds.',
            ]);
        }

        // 3. check email + password, and that the user is an admin
        $adminRoleId = Role::where('name', 'admin')->value('id');

        if (! Auth::attempt([...$credentials, 'role_id' => $adminRoleId], $request->boolean('remember'))) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages(['email' => 'Invalid email or password.']);
        }

        // 4. success: reset the counter, start a fresh session, go to the dashboard
        RateLimiter::clear($key);
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    // logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
