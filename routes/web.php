<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\MemberController;
require __DIR__ . '/auth.php';
use App\Http\Controllers\NetworkingController;
use App\Http\Controllers\Admin\HeaderNoticeController;
use App\Http\Controllers\LookupController;
use App\Http\Controllers\MyAccountController;

Route::get('/', function () {
    return view('index');
})->name('index');
Route::get('/my_account', function () {
    return view('user.myaccount');
});
Route::get('/trading', function () {
    return view('trading');
})->name('trading');
Route::get('/trading/my_accout_details', function () {
    return view('trading.my_account');
})->name('trading.myaccount');
Route::get('/admin', function () {
    return view('dashboard');
})->name('admin.dashboard');
Route::get('/network', [NetworkingController::class, 'index'])->name('network');
Route::get('/members/{user}', [MemberController::class, 'show'])->name('members.show');
Route::get('/networking', [NetworkingController::class, 'index'])->name('networking.index');
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminLoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminLoginController::class, 'login'])->name('login.submit');
    Route::resource('banners', BannerController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::middleware(['auth', 'role:admin'])->group(function () {
        Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');

        Route::resource('header-notice', HeaderNoticeController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['header-notice' => 'headerNotice']);
    });
});
Route::middleware('role:admin')->prefix('admin')->group(function () {
});
Route::middleware('role:member')->group(function () {
});
Route::middleware('role:member,guest')->group(function () {
});

Route::prefix('lookups')->group(function () {
    Route::get('dioceses/{rite}', [LookupController::class, 'dioceses']);
    Route::get('foranes/{diocese}', [LookupController::class, 'foranes']);
    Route::get('parishes/{forane}', [LookupController::class, 'parishes']);
    Route::get('states/{country}', [LookupController::class, 'states']);
    Route::get('districts/{state}', [LookupController::class, 'districts']);
    Route::get('jobs/{category}', [LookupController::class, 'jobs']);
});
Route::post('/register/otp/send', [RegisterController::class, 'sendOtp'])
    ->middleware('throttle:5,1')->name('register.otp.send');

Route::post('/register/otp/verify', [RegisterController::class, 'verifyOtp'])
    ->middleware('throttle:10,1')->name('register.otp.verify');

Route::post('/register', [RegisterController::class, 'store'])
    ->middleware('throttle:10,1')->name('register.store');

Route::post('/login', [RegisterController::class, 'login'])->name('login.submit');
Route::post('/logout', [RegisterController::class, 'logout'])->middleware('auth')->name('logout');


Route::get('/trading/my_accout_details', [MyAccountController::class, 'show'])->name('trading.myaccount');
Route::post('/trading/my_account/update', [MyAccountController::class, 'update'])->name('network.myaccount.update');


Route::get('/profile', [MemberController::class, 'me'])->name('profile.me');
    