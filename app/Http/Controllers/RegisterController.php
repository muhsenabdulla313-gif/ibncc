<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
class RegisterController extends Controller
{
   // keep in sync with the rite <select> in the register modal (dioceses are too many to list here)
    private const RITES = ['syro-malabar', 'syro-malankara', 'latin'];
 
    private const OTP_MINUTES      = 5;   // OTP lifetime
    private const OTP_MAX_TRIES    = 5;   // wrong guesses before the OTP is thrown away
    private const VERIFIED_MINUTES = 30;  // how long "verified" is remembered before Sign Up
 
    // POST /register/otp/send   { type: phone|email, value }
    public function sendOtp(Request $request)
    {
        $data = $request->validate([
            'type'  => 'required|in:phone,email',
            'value' => 'required|string|max:100',
        ]);
 
        $type  = $data['type'];
        $value = $this->normalize($type, $data['value']);
 
        if ($type === 'phone' && ! preg_match('/^\d{10}$/', $value)) {
            $this->reject('value', 'Enter a valid 10-digit phone number.');
        }
        if ($type === 'email' && ! filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->reject('value', 'Enter a valid email address.');
        }
        if ($this->isTaken($type, $value)) {
            $this->reject('value', $type === 'phone'
                ? 'This phone number is already registered.'
                : 'This email is already registered.');
        }
 
        $otp = (string) random_int(100000, 999999);
 
        // only a hash of the OTP is stored, in the cache (no table needed)
        Cache::put($this->otpKey($type, $value), ['hash' => $this->hashOtp($otp), 'tries' => 0], now()->addMinutes(self::OTP_MINUTES));
        Cache::forget($this->verifiedKey($type, $value));
 
        if ($type === 'email') {
            Mail::raw(
                "Your IBNCC verification code is {$otp}. It is valid for " . self::OTP_MINUTES . ' minutes.',
                fn ($m) => $m->to($value)->subject('IBNCC verification code')
            );
        } else {
            // TODO: send $otp by SMS with your SMS gateway
        }
 
        // APP_ENV=local only: return the OTP so the "demo OTP" line in the modal can show it
        return response()->json(['message' => 'OTP sent'] + (app()->environment('local') ? ['debug_otp' => $otp] : []));
    }
 
    // POST /register/otp/verify   { type, value, otp }
    public function verifyOtp(Request $request)
    {
        $data = $request->validate([
            'type'  => 'required|in:phone,email',
            'value' => 'required|string|max:100',
            'otp'   => 'required|digits:6',
        ]);
 
        $type  = $data['type'];
        $value = $this->normalize($type, $data['value']);
        $key   = $this->otpKey($type, $value);
        $entry = Cache::get($key);
 
        if (! $entry) {
            $this->reject('otp', 'OTP expired. Please request a new one.');
        }
 
        if ($entry['tries'] >= self::OTP_MAX_TRIES) {
            Cache::forget($key);
            $this->reject('otp', 'Too many wrong attempts. Please request a new OTP.');
        }
 
        if (! hash_equals($entry['hash'], $this->hashOtp($data['otp']))) {
            $entry['tries']++;
            Cache::put($key, $entry, now()->addMinutes(self::OTP_MINUTES));
            $this->reject('otp', 'Incorrect OTP.');
        }
 
        Cache::forget($key);
        Cache::put($this->verifiedKey($type, $value), true, now()->addMinutes(self::VERIFIED_MINUTES));
 
        return response()->json(['message' => 'Verified']);
    }
 
    // POST /register   ← the Sign Up button; this is where the row is stored in `users`
    public function store(Request $request)
    {
        // clean phone / email before validating
        $request->merge(['phone' => $this->normalize('phone', (string) $request->input('phone'))]);
        if ($request->filled('email')) {
            $request->merge(['email' => $this->normalize('email', $request->input('email'))]);
        }
 
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'parish'   => 'required|string|max:100',
            'diocese'  => 'required|string|max:100',
            'rite'     => ['required', Rule::in(self::RITES)],
            'password' => 'required|string|min:6|max:64',
            'confirm'  => 'required|same:password',
            'phone'    => 'required|digits:10',
            'email'    => 'nullable|email|max:100',
        ], [
            'confirm.same'    => 'Passwords do not match.',
            'phone.digits'    => 'Enter a valid 10-digit phone number.',
            'rite.in'         => 'Select a valid rite.',
        ]);
 
        $phone = $data['phone'];
        $email = $data['email'] ?? null;
 
        // taken? (a guest row with the same phone is allowed — it gets upgraded below)
        if ($this->isTaken('phone', $phone)) {
            $this->reject('phone', 'This phone number is already registered.');
        }
        if ($email && $this->isTaken('email', $email)) {
            $this->reject('email', 'This email is already registered.');
        }
 
        // OTP must have been confirmed in the modal
        if (! Cache::get($this->verifiedKey('phone', $phone))) {
            $this->reject('phone', 'Please verify your phone number.');
        }
        if ($email && ! Cache::get($this->verifiedKey('email', $email))) {
            $this->reject('email', 'Please verify your email or leave it empty.');
        }
 
        $attributes = [
            'role_id'           => Role::where('name', 'member')->value('id'),
            'name'              => $data['name'],
            'parish'            => $data['parish'],
            'diocese'           => $data['diocese'],
            'rite'              => $data['rite'],
            'phone'             => $phone,
            'phone_verified_at' => now(),
            'email'             => $email,
            'email_verified_at' => $email ? now() : null,
            'password'          => $data['password'],   // hashed by the model cast
            'otp_code'          => null,
            'otp_expires_at'    => null,
        ];
 
        // same phone already exists as a guest → upgrade that row (keeps its order history)
        $guest = User::where('phone', $phone)->first();
        $user  = $guest ? tap($guest)->update($attributes) : User::create($attributes);
 
        Cache::forget($this->verifiedKey('phone', $phone));
        if ($email) {
            Cache::forget($this->verifiedKey('email', $email));
        }
 
        Auth::login($user);
        $request->session()->regenerate();
 
        return response()->json([
            'message'  => 'Account created.',
            'redirect' => url('/'),
        ], 201);
    }
 
    /* ---------------- helpers ---------------- */
 
    // phone → last 10 digits (drops +91 / 0), email → trimmed lower-case
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
}
