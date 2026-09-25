<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class LoginController extends Controller
{
   

    // One login form for both admin and member — role decides the redirect
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
 
        if (! Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Invalid credentials']);
        }
 
        $user = Auth::user();
 
        // Guests never reach here — they have no password, they use OTP.
        if ($user->isGuest()) {
            Auth::logout();
            return back()->withErrors(['email' => 'Invalid credentials']);
        }
 
        return $user->isAdmin()
            ? redirect('/admin/dashboard')
            : redirect('/'); 
}
