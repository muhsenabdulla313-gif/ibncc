<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Diocese;
use App\Models\Forane;
use App\Models\Parish;
use App\Models\Rite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MyAccountController extends Controller
{
    /**
     * Display the authenticated user's account details.
     */
    public function show()
    {
        if (! Auth::check()) {
            return redirect('/#login');
        }

        $user = Auth::user()->load([
            'profile',
            'role',
            'parish.forane.diocese.rite',
            'addresses.district',
            'addresses.state',
            'orders.items.product',
        ]);

        $profile = $user->profile;
        $rites   = Rite::orderBy('name')->get(['id', 'name']);

        // Pre-fetch church hierarchy if user already has a parish assigned
        $currentRiteId    = $user->parish?->forane?->diocese?->rite_id;
        $currentDioceseId = $user->parish?->forane?->diocese_id;
        $currentForaneId  = $user->parish?->forane_id;

        $dioceses = $currentRiteId
            ? Diocese::where('rite_id', $currentRiteId)->orderBy('name')->get(['id', 'name'])
            : collect();

        $foranes = $currentDioceseId
            ? Forane::where('diocese_id', $currentDioceseId)->orderBy('name')->get(['id', 'name'])
            : collect();

        $parishes = $currentForaneId
            ? Parish::where('forane_id', $currentForaneId)->orderBy('name')->get(['id', 'name'])
            : collect();

        return view('trading.my_account', [
            'user'             => $user,
            'profile'          => $profile,
            'rites'            => $rites,
            'dioceses'         => $dioceses,
            'foranes'          => $foranes,
            'parishes'         => $parishes,
            'currentRiteId'    => $currentRiteId,
            'currentDioceseId' => $currentDioceseId,
            'currentForaneId'  => $currentForaneId,
            'orders'           => $user->orders()->with('items.product')->latest()->get(),
            'addresses'        => $user->addresses()->latest()->get(),
        ]);
    }

    /**
     * Update user profile information.
     */
    public function update(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 401);

        // Normalize phone and email
        if ($request->filled('phone')) {
            $request->merge([
                'phone' => substr(preg_replace('/\D+/', '', (string) $request->input('phone')), -10),
            ]);
        }
        if ($request->filled('email')) {
            $request->merge([
                'email' => mb_strtolower(trim((string) $request->input('email'))) ?: null,
            ]);
        }

        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'phone'       => ['required', 'digits:10', Rule::unique('users', 'phone')->ignore($user->id)],
            'email'       => ['nullable', 'email', 'max:100', Rule::unique('users', 'email')->ignore($user->id)],
            'dob'         => 'nullable|date|before:today',
            'gender'      => 'nullable|string|in:male,female,other',
            'blood_group' => 'nullable|string|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'parish_id'   => 'nullable|integer|exists:parishes,id',
            'photo'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ], [
            'phone.digits'     => 'Enter a valid 10-digit phone number.',
            'phone.unique'     => 'This phone number is already registered.',
            'email.unique'     => 'This email is already registered.',
            'parish_id.exists' => 'Selected parish does not exist.',
            'photo.image'      => 'The profile photo must be a valid image file.',
        ]);

        $phone = $data['phone'];
        $email = $data['email'] ?? null;

        $phoneChanged = $phone !== $user->phone;
        $emailChanged = $email && $email !== $user->email;

        // Check OTP verification flags if phone or email changed
        if ($phoneChanged && ! Cache::get("reg_ok:phone:{$phone}")) {
            throw ValidationException::withMessages(['phone' => 'Please verify your new phone number before saving.']);
        }
        if ($emailChanged && ! Cache::get("reg_ok:email:{$email}")) {
            throw ValidationException::withMessages(['email' => 'Please verify your new email address before saving.']);
        }

        // Update User core fields
        $userUpdates = [
            'name'  => $data['name'],
            'phone' => $phone,
            'email' => $email,
        ];
        if ($phoneChanged) {
            $userUpdates['phone_verified_at'] = now();
        }
        if ($emailChanged) {
            $userUpdates['email_verified_at'] = now();
        }
        if (array_key_exists('parish_id', $data)) {
            $userUpdates['parish_id'] = $data['parish_id'];
        }

        $user->update($userUpdates);

        // Update Profile fields
        $profileUpdates = [];
        if (array_key_exists('dob', $data)) {
            $profileUpdates['dob'] = $data['dob'];
        }
        if (array_key_exists('gender', $data)) {
            $profileUpdates['gender'] = $data['gender'];
        }
        if (array_key_exists('blood_group', $data)) {
            $profileUpdates['blood_group'] = $data['blood_group'];
        }

        // Profile Photo upload
        if ($request->hasFile('photo')) {
            $oldPhoto = $user->profile->photo;
            $photoPath = $request->file('photo')->store('profiles/photo', 'public');
            $profileUpdates['photo'] = $photoPath;

            if ($oldPhoto && Storage::disk('public')->exists($oldPhoto)) {
                Storage::disk('public')->delete($oldPhoto);
            }
        }

        if (! empty($profileUpdates)) {
            $user->profile()->updateOrCreate([], $profileUpdates);
        }

        // Clear verification cache
        if ($phoneChanged) {
            Cache::forget("reg_ok:phone:{$phone}");
        }
        if ($emailChanged) {
            Cache::forget("reg_ok:email:{$email}");
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully!',
            'user'    => [
                'name'        => $user->name,
                'phone'       => $user->phone,
                'email'       => $user->email,
                'dob'         => $user->profile->dob?->format('j F Y') ?? 'Not added yet',
                'gender'      => ucfirst($user->profile->gender ?? ''),
                'blood_group' => $user->profile->blood_group ?? '',
                'parish'      => $user->parish?->name ?? 'Not set',
                'forane'      => $user->forane_name ?? 'Not set',
                'diocese'     => $user->diocese_name ?? 'Not set',
                'rite'        => $user->rite_name ?? 'Not set',
                'photo_url'   => $user->profile->photo_url,
            ],
        ]);
    }

    /**
     * Update user password.
     */
    public function password(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 401);

        $validated = $request->validate([
            'current_password'          => 'required|string',
            'new_password'              => 'required|string|min:6|max:64',
            'new_password_confirmation' => 'required|same:new_password',
        ], [
            'current_password.required'      => 'Please enter your current password.',
            'new_password.min'               => 'New password must be at least 6 characters.',
            'new_password_confirmation.same' => 'Password confirmation does not match.',
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'The current password you provided is incorrect.',
                'errors'  => ['current_password' => ['The current password you provided is incorrect.']],
            ], 422);
        }

        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully!',
        ]);
    }

    /**
     * Add or update an address.
     */
    public function saveAddress(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 401);

        $validated = $request->validate([
            'id'    => 'nullable|integer',
            'type'  => 'nullable|string|max:50',
            'house' => 'required|string|max:255',
            'pin'   => 'nullable|string|max:20',
        ]);

        if (! empty($validated['id'])) {
            $address = $user->addresses()->findOrFail($validated['id']);
            $address->update([
                'type'  => $validated['type'] ?? 'home',
                'house' => $validated['house'],
                'pin'   => $validated['pin'] ?? null,
            ]);
        } else {
            $user->addresses()->create([
                'type'  => $validated['type'] ?? 'home',
                'house' => $validated['house'],
                'pin'   => $validated['pin'] ?? null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Address saved successfully!',
        ]);
    }

    /**
     * Delete an address.
     */
    public function deleteAddress(Address $address)
    {
        $user = auth()->user();
        abort_unless($user && $address->user_id === $user->id, 403);

        $address->delete();

        return response()->json([
            'success' => true,
            'message' => 'Address deleted successfully!',
        ]);
    }
}
