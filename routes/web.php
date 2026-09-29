<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\RegisterController;
require __DIR__.'/auth.php';


Route::get('/', function () {
    return view('index');
    
})->name('index');
Route::get('/my_account', function () {
    return view('user.myaccount');
    });
Route::get('/trading', function () {
    return view('trading');
})->name('trading');
Route::get('/admin', function () {
    return view('layouts.admin');
})->name('admin.dashboard');



Route::prefix('admin')->name('admin.')->group(function () {

    Route::get('/login', [AdminLoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminLoginController::class, 'login'])->name('login.submit');

    Route::middleware(['auth', 'role:admin'])->group(function () {
        Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');
    });
});



// Route::post('/guest/send-otp', [GuestOtpController::class, 'sendOtp']);
// Route::post('/guest/verify-otp', [GuestOtpController::class, 'verifyOtp']);
Route::middleware('role:admin')->prefix('admin')->group(function () {
});
Route::middleware('role:member')->group(function () {
});

Route::middleware('role:member,guest')->group(function () {
});





Route::post('/register/otp/send', [RegisterController::class, 'sendOtp'])
    ->middleware('throttle:5,1')->name('register.otp.send');
 
Route::post('/register/otp/verify', [RegisterController::class, 'verifyOtp'])
    ->middleware('throttle:10,1')->name('register.otp.verify');
 
Route::post('/register', [RegisterController::class, 'store'])
    ->middleware('throttle:10,1')->name('register.store');

Route::post('/login', [RegisterController::class, 'login'])->name('login.submit');
Route::post('/logout', [RegisterController::class, 'logout'])->middleware('auth')->name('logout');