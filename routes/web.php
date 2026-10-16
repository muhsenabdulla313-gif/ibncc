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
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MemberSocialController as Social;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\ProfileVerifyController;

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
Route::get('/members/{user:uuid}', [MemberController::class, 'show'])->name('members.show');
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


Route::middleware('auth')->prefix('profile')->group(function () {
    Route::post('/privacy', [ProfileController::class, 'privacy'])->name('profile.privacy');
    Route::post('/image', [ProfileController::class, 'image'])->name('profile.image');
    Route::post('/gallery', [ProfileController::class, 'galleryStore'])->name('profile.gallery.store');
    Route::post('/password', [ProfileController::class, 'password'])->name('profile.password');
    Route::get('/', [ProfileController::class, 'me'])->name('profile.me');
    Route::post('/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/social', [ProfileController::class, 'socialStore'])->name('profile.social.store');
    Route::put('/social/{id}', [ProfileController::class, 'socialUpdate'])->name('profile.social.update');
    Route::delete('/social/{id}', [ProfileController::class, 'socialDestroy'])->name('profile.social.destroy');
    Route::post('members/{uuid}/save', [Social::class, 'toggleSave'])->name('members.save');
    Route::post('members/{uuid}/block', [Social::class, 'toggleBlock'])->name('members.block');

    Route::get('messages', [Social::class, 'conversations'])->name('messages.index');
    Route::get('messages/{uuid}', [Social::class, 'thread'])->name('messages.thread');
    Route::post('messages/{uuid}', [Social::class, 'send'])->middleware('throttle:30,1')->name('messages.send');

    Route::post('admin-messages/read', [Social::class, 'readAdmin'])->name('admin-messages.read');
    Route::get('share/members', [Social::class, 'shareSearch'])->name('share.members');
Route::get('activity', [ActivityController::class, 'index'])->name('activity');
Route::post('members/{uuid}/share', [Social::class, 'share'])->middleware('throttle:30,1')->name('members.share');


});
Route::post('/profile/verify', [ProfileVerifyController::class, 'handle'])
    ->middleware('auth')->name('profile.verify');

Route::post('/profile/email/send-otp', [ProfileVerifyController::class, 'sendEmailOtp'])->name('profile.email.otp');
Route::post('/profile/email/verify-otp', [ProfileVerifyController::class, 'verifyEmailOtp'])->name('profile.email.verify');