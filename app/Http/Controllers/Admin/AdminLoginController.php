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

    private function guard()
    {
        return Auth::guard('admin');
    }

    public function showLogin()
    {
        if ($this->guard()->check() && $this->guard()->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email|max:100',
            'password' => 'required|string|min:6|max:64',
        ]);

        $key = Str::lower($credentials['email']) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'email' => 'Too many attempts. Try again in ' . RateLimiter::availableIn($key) . ' seconds.',
            ]);
        }

        $adminRoleId = Role::where('name', 'admin')->value('id');

        if (!$this->guard()->attempt([...$credentials, 'role_id' => $adminRoleId], $request->boolean('remember'))) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages(['email' => 'Invalid email or password.']);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        $this->guard()->logout();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
