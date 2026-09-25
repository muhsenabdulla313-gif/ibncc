<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GuestOtpController;
use App\Http\Controllers\LoginController;


Route::get('/', function () {
    return view('index');
});
Route::get('/admin', function () {
    return view('layouts.admin');
});
Route::get('/admin/login', function () {
    return view('admin.login');
});

// Route::view('dashboard', 'dashboard')
//     ->middleware(['auth', 'verified'])
//     ->name('dashboard');



require __DIR__.'/auth.php';

Route::post('/login', [LoginController::class, 'login']);
Route::post('/guest/send-otp', [GuestOtpController::class, 'sendOtp']);
Route::post('/guest/verify-otp', [GuestOtpController::class, 'verifyOtp']);
Route::middleware('role:admin')->prefix('admin')->group(function () {
});
Route::middleware('role:member')->group(function () {
});

Route::middleware('role:member,guest')->group(function () {
});