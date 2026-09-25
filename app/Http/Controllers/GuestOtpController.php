<?php

namespace App\Http\Controllers;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class GuestOtpController extends Controller
{
   public function sendOtp(Request $request)
    {
        $request->validate(['phone' => 'required|digits:10']);
 
        $guestRole = Role::where('name', 'guest')->firstOrFail();
 
        $user = User::firstOrCreate(
            ['phone' => $request->phone],
            ['role_id' => $guestRole->id]
        );
 
        $otp = random_int(100000, 999999);
 
        $user->update([
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(5),
        ]);
 
 
        return response()->json(['message' => 'OTP sent']);
    }
 
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|digits:10',
            'otp' => 'required|digits:6',
        ]);
 
        $user = User::where('phone', $request->phone)
            ->where('otp_code', $request->otp)
            ->where('otp_expires_at', '>=', now())
            ->first();
 
        if (! $user) {
            return response()->json(['message' => 'Invalid or expired OTP'], 422);
        }
 
        $user->update([
            'phone_verified_at' => now(),
            'otp_code' => null,
            'otp_expires_at' => null,
        ]);
 
        Auth::login($user); // same guard, same table — role_id keeps them scoped to guest permissions
 
        return response()->json(['message' => 'Verified']);
    }
}
