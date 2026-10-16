<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
class ProfileVerifyController extends Controller
{
     public function handle(Request $r)
    {
        $data = $r->validate([
            'action' => 'required|in:send,confirm',
            'value'  => 'required|email|max:255',
            'code'   => 'required_if:action,confirm|nullable|digits:6',
        ]);

        $user  = $r->user();
        $email = strtolower(trim($data['value']));

        if (User::where('email', $email)->where('id', '!=', $user->id)->exists()) {
            return response()->json(['message' => 'Email is already in use.'], 422);
        }

        $key = "profile_verify:{$user->id}:" . md5($email);

        if ($data['action'] === 'send') {
            $code = (string) random_int(100000, 999999);
            Cache::put($key, $code, now()->addMinutes(10));

            Mail::raw("Your verification code is {$code}. It expires in 10 minutes.", function ($m) use ($email) {
                $m->to($email)->subject('Verify your email - IBNCC');
            });

            return response()->json(['message' => 'Code sent.']);
        }

        if (Cache::get($key) !== $data['code']) {
            return response()->json(['message' => 'Invalid or expired code.'], 422);
        }
        Cache::forget($key);

        $user->forceFill([
            'email' => $email,
            'email_verified_at' => now(),
        ])->save();

        return response()->json(['message' => 'Verified.']);
    }
    public function sendEmailOtp(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $email = strtolower($data['email']);
        $key = "email_otp:{$user->id}";

        if (Cache::has("{$key}:lock")) {
            return response()->json(['message' => 'Please wait a few seconds before requesting another code.'], 429);
        }

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put($key, ['email' => $email, 'hash' => Hash::make($otp), 'tries' => 0], now()->addMinutes(10));
        Cache::put("{$key}:lock", 1, now()->addSeconds(30));

        Mail::raw("Your verification code is {$otp}. It expires in 10 minutes.", function ($m) use ($email) {
            $m->to($email)->subject('Your verification code');
        });

        return response()->json(['ok' => true]);
    }

    public function verifyEmailOtp(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'email' => ['required', 'email'],
            'otp'   => ['required', 'digits:6'],
        ]);

        $key = "email_otp:{$user->id}";
        $row = Cache::get($key);

        if (!$row || $row['email'] !== strtolower($data['email'])) {
            return response()->json(['message' => 'Code expired. Please request a new one.'], 422);
        }

        if ($row['tries'] >= 5) {
            Cache::forget($key);
            return response()->json(['message' => 'Too many attempts. Please request a new code.'], 429);
        }

        if (!Hash::check($data['otp'], $row['hash'])) {
            $row['tries']++;
            Cache::put($key, $row, now()->addMinutes(10));
            return response()->json(['message' => 'Incorrect code.'], 422);
        }

        $user->forceFill([
            'email' => $row['email'],
            'email_verified_at' => now(),
        ])->save();

        Cache::forget($key);

        return response()->json(['ok' => true, 'email' => $user->email]);
    }
}
