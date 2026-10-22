<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
class RegisterController extends Controller
{
    // keep in sync with the rite <select> in the register modal

    private const OTP_MINUTES      = 5;
    private const OTP_MAX_TRIES    = 5;
    private const VERIFIED_MINUTES = 30;
    private const LOGIN_MAX_TRIES  = 5;

    // POST /login
    public function login(Request $request)
    {
        $data = $request->validate([
            'phone'    => 'required|string|max:20',
            'password' => 'required|string',
        ]);

        $phone = $this->normalize('phone', $data['phone']);
        $key   = 'login:' . $phone . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, self::LOGIN_MAX_TRIES)) {
            $secs = RateLimiter::availableIn($key);
            $this->reject('phone', "Too many attempts. Try again in {$secs} seconds.");
        }

        $user = User::with('role')->where('phone', $phone)->first();

        if (! $user
            || ($user->role?->name === 'guest')
            || ! $user->password
            || ! Hash::check($data['password'], $user->password)
        ) {
            RateLimiter::hit($key, 60);
            $this->reject('phone', 'Invalid phone number or password.');
        }

        RateLimiter::clear($key);

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        if ($request->expectsJson()) {
            return response()->json([
                'message'  => 'Logged in.',
                'redirect' => url('/'),
            ]);
        }

        return redirect()->route('trading');
    }

    // POST /register/otp/send   { type: phone|email, value }


public function sendOtp(Request $r)
{
    // a logged-in user may re-verify their own email; nobody can use someone else's
    $data  = $r->validate([
        'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore(Auth::id())],
    ]);
    $email = strtolower(trim($data['email']));
    $code  = (string) random_int(100000, 999999);

    Cache::put('reg_otp:' . md5($email), $code, now()->addMinutes(10));

    try {
        Mail::raw("Your IBNCC verification code is {$code}. It expires in 10 minutes. Do not share it with anyone.", function ($m) use ($email) {
            $m->to($email)->subject('Your IBNCC verification code');
        });
    } catch (\Throwable $e) {
        report($e);
        return response()->json(['message' => 'Could not send email. Please try again.'], 500);
    }

    return response()->json(['message' => 'OTP sent to your email.']);
}

public function verifyOtp(Request $r)
{
    $data  = $r->validate([
        'email' => 'required|email',
        'code'  => 'required|digits:6',
    ]);
    $email = strtolower(trim($data['email']));
    $key   = 'reg_otp:' . md5($email);

    if (Cache::get($key) !== $data['code']) {
        return response()->json(['message' => 'Invalid or expired OTP.'], 422);
    }

    Cache::forget($key);

    // Logged in (profile page): save the email as verified on this account
    if ($user = Auth::user()) {
        if (User::where('email', $email)->where('id', '!=', $user->id)->exists()) {
            return response()->json(['message' => 'This email is already registered.'], 422);
        }
        $user->forceFill(['email' => $email, 'email_verified_at' => now()])->save();

        return response()->json(['message' => 'Email verified.', 'email' => $email, 'verified' => true]);
    }

    // Registration: unchanged behaviour
    Cache::put('reg_email_verified:' . md5($email), true, now()->addMinutes(30));

    return response()->json(['message' => 'Email verified.']);
}
public function store(Request $request)
{
    $request->merge([
        'phone' => $this->normalize('phone', (string) $request->input('phone')),
        'email' => $this->normalize('email', (string) $request->input('email')),
    ]);

    $data = $request->validate([
        'name'      => 'required|string|max:100',
        'parish_id' => 'required|exists:parishes,id',
        'password'  => 'required|string|min:6|max:64',
        'confirm'   => 'required|same:password',
        'phone'     => 'required|digits:10',
        'email'     => 'required|email|max:255',
    ], [
        'confirm.same'       => 'Passwords do not match.',
        'phone.digits'       => 'Enter a valid 10-digit phone number.',
        'parish_id.required' => 'Select your rite, diocese, forane and parish.',
        'parish_id.exists'   => 'Select a valid parish.',
    ]);

    $phone = $data['phone'];
    $email = $data['email'];

    if ($this->isTaken('phone', $phone)) {
        $this->reject('phone', 'This phone number is already registered.');
    }
    if ($this->isTaken('email', $email)) {
        $this->reject('email', 'This email is already registered.');
    }

    // same key that verifyOtp() sets
    $verifiedKey = 'reg_email_verified:' . md5($email);
    if (! Cache::get($verifiedKey)) {
        $this->reject('email', 'Please verify your email.');
    }

    $attributes = [
        'role_id'           => Role::where('name', 'member')->value('id'),
        'name'              => $data['name'],
        'parish_id'         => $data['parish_id'],
        'phone'             => $phone,
        'email'             => $email,
        'email_verified_at' => now(),
        'password'          => $data['password'],   // hashed by the model cast
    ];

    $guest = User::where('phone', $phone)->first();
    $user  = $guest ? tap($guest)->update($attributes) : User::create($attributes);

    Cache::forget($verifiedKey);

    Auth::login($user);
    $request->session()->regenerate();

    return response()->json([
        'message'  => 'Account created.',
        'redirect' => url('/'),
    ], 201);
}

    private function normalize(string $type, string $value): string
    {
        if ($type === 'email') {
            return Str::lower(trim($value));
        }

        $digits = preg_replace('/\D+/', '', $value);

        return strlen($digits) > 10 ? substr($digits, -10) : $digits;
    }

    private function isTaken(string $type, string $value): bool
    {
        $query = User::where($type, $value);

        if ($type === 'phone') {
            $query->whereHas('role', fn ($r) => $r->where('name', '!=', 'guest'));
        }

        return $query->exists();
    }

    private function otpKey(string $type, string $value): string
    {
        return "reg_otp:{$type}:{$value}";
    }

    private function verifiedKey(string $type, string $value): string
    {
        return "reg_ok:{$type}:{$value}";
    }

    private function hashOtp(string $otp): string
    {
        return hash_hmac('sha256', $otp, config('app.key'));
    }

    private function reject(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }
 public function logout(Request $request)
{
    Auth::guard('web')->logout();
    $request->session()->regenerateToken();

    return redirect('/');
}
}
