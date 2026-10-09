<?php

namespace App\Http\Controllers;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
class MyAccountController extends Controller
{
    public function show()
    {
        if (! auth()->check()) {
            return redirect('/#login');          // your JS opens the login modal on #login
        }

        $user = auth()->user()->load('profile');

        return view('trading.my_account', [
            'user'    => $user,
            'profile' => $user->profile,
        ]);
    }

    // POST /trading/my_account/update
   public function update(Request $request)
{
    $user = $request->user();
    abort_unless($user, 401);

    // same normalising as RegisterController
    $request->merge([
        'phone' => substr(preg_replace('/\D+/', '', (string) $request->input('phone')), -10),
        'email' => mb_strtolower(trim((string) $request->input('email'))) ?: null,
    ]);

    $data = $request->validate([
        'name'  => 'required|string|max:100',
        'phone' => ['required', 'digits:10', Rule::unique('users', 'phone')->ignore($user->id)],
        'email' => ['nullable', 'email', 'max:100', Rule::unique('users', 'email')->ignore($user->id)],
        'dob'   => 'nullable|date|before:today',
    ], [
        'phone.digits' => 'Enter a valid 10-digit phone number.',
        'phone.unique' => 'This phone number is already registered.',
        'email.unique' => 'This email is already registered.',
    ]);

    $phone = $data['phone'];
    $email = $data['email'] ?? null;

    $phoneChanged = $phone !== $user->phone;
    $emailChanged = $email && $email !== $user->email;

    // flags are set by /register/otp/verify (same cache key format)
    if ($phoneChanged && ! Cache::get("reg_ok:phone:{$phone}")) {
        throw ValidationException::withMessages(['phone' => 'Please verify your new phone number.']);
    }
    if ($emailChanged && ! Cache::get("reg_ok:email:{$email}")) {
        throw ValidationException::withMessages(['email' => 'Please verify your new email address.']);
    }

    $user->update([
        'name'              => $data['name'],
        'phone'             => $phone,
        'phone_verified_at' => $phoneChanged ? now() : $user->phone_verified_at,
        'email'             => $email,
        'email_verified_at' => $emailChanged ? now() : ($email ? $user->email_verified_at : null),
    ]);

    $user->profile()->updateOrCreate([], ['dob' => $data['dob'] ?? null]);

    if ($phoneChanged) { Cache::forget("reg_ok:phone:{$phone}"); }
    if ($emailChanged) { Cache::forget("reg_ok:email:{$email}"); }

    return response()->json(['message' => 'Saved']);
}
}
