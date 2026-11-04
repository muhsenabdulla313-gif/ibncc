<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\NetworkingController;
use App\Http\Controllers\Admin\HeaderNoticeController;
use App\Http\Controllers\LookupController;
use App\Http\Controllers\MyAccountController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TradingController;
use App\Http\Controllers\MemberSocialController as Social;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\Admin\AdminMessageController;
use App\Http\Controllers\Admin\ChurchDirectoryController;
use App\Http\Middleware\EnsureAdmin;
use App\Http\Controllers\Admin\UserCrudController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\ProductListingController;
use App\Http\Controllers\Seller\SellerHubController;
use App\Http\Controllers\Admin\CatalogController;

use App\Http\Controllers\Seller\SellerCatalogController;
use App\Http\Controllers\Seller\SellerController;
use App\Http\Controllers\Seller\SellerOrderController;
use App\Http\Controllers\Seller\SellerProductController;
use App\Http\Middleware\EnsureSeller;


require __DIR__ . '/auth.php';

Route::get('/', function () {
    return view('index');
})->name('index');
Route::get('/my_account', function () {
    return view('user.myaccount');
});

Route::get('/trading', [TradingController::class, 'index'])->name('trading');


Route::get('/admin', function () {
    return view('admin.dashboard');
})->name('admin.dashboard');
Route::get('/network', [NetworkingController::class, 'index'])->name('network');
Route::get('/members/{user:uuid}', [MemberController::class, 'show'])->name('members.show');
Route::get('/networking', [NetworkingController::class, 'index'])->name('networking.index');

Route::get('/events/{event:slug}', [EventController::class, 'show'])->name('events.show');


// Route::prefix('admin')->name('admin.')->group(function () {
//     Route::get('/login', [AdminLoginController::class, 'showLogin'])->name('login');
//     Route::post('/login', [AdminLoginController::class, 'login'])->name('login.submit');
//     Route::resource('banners', BannerController::class)->only(['index', 'store', 'update', 'destroy']);
//     Route::middleware(['auth', 'role:admin'])->group(function () {
//         Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');

//         Route::resource('header-notice', HeaderNoticeController::class)
//             ->only(['index', 'store', 'update', 'destroy'])
//             ->parameters(['header-notice' => 'headerNotice']);
//     });
// });
// Route::middleware('role:admin')->group(function () {
//     Route::prefix('admin')->name('admin.')->group(function () {
//         $types = ['rite', 'diocese', 'forane', 'parish'];
//         Route::get('/church', [ChurchDirectoryController::class, 'index'])->name('church.index');
//         Route::get('/church/options', [ChurchDirectoryController::class, 'options'])->name('church.options');
//         Route::get('/church/{type}', [ChurchDirectoryController::class, 'list'])->whereIn('type', $types)->name('church.list');
//         Route::post('/church/{type}', [ChurchDirectoryController::class, 'store'])->whereIn('type', $types)->name('church.store');
//         Route::put('/church/{type}/{id}', [ChurchDirectoryController::class, 'update'])->whereIn('type', $types)->whereNumber('id')->name('church.update');
//         Route::delete('/church/{type}/{id}', [ChurchDirectoryController::class, 'destroy'])->whereIn('type', $types)->whereNumber('id')->name('church.destroy');

//         Route::resource('admin-messages', AdminMessageController::class)
//             ->only(['index', 'store', 'update', 'destroy'])
//             ->parameters(['admin-messages' => 'adminMessage']);

//     });




Route::prefix('admin')->name('admin.')->group(function () {

    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminLoginController::class, 'showLogin'])->name('login');
        Route::post('/login', [AdminLoginController::class, 'login'])->name('login.submit');
    });

    Route::middleware(['auth:admin', 'role:admin'])->group(function () {
        Route::get('/', fn() => view('admin.dashboard'))->name('dashboard');
        Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');
        Route::delete('events/images/{image}', [AdminEventController::class, 'destroyImage'])->name('events.images.destroy');
        Route::resource('events', AdminEventController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('banners', BannerController::class)->only(['index', 'store', 'update', 'destroy']);

        Route::resource('header-notice', HeaderNoticeController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['header-notice' => 'headerNotice']);

        Route::get('/users', [UserCrudController::class, 'index'])->name('users.index');
        Route::get('/users/data', [UserCrudController::class, 'data'])->name('users.data');
        Route::post('/users', [UserCrudController::class, 'store'])->name('users.store');
        Route::get('/users/{id}', [UserCrudController::class, 'show'])->whereNumber('id')->name('users.show');
        Route::put('/users/{id}', [UserCrudController::class, 'update'])->whereNumber('id')->name('users.update');
        Route::delete('/users/{id}', [UserCrudController::class, 'destroy'])->whereNumber('id')->name('users.destroy');

        $types = ['rite', 'diocese', 'forane', 'parish'];
        Route::get('/church', [ChurchDirectoryController::class, 'index'])->name('church.index');
        Route::get('/church/options', [ChurchDirectoryController::class, 'options'])->name('church.options');
        Route::get('/church/{type}', [ChurchDirectoryController::class, 'list'])->whereIn('type', $types)->name('church.list');
        Route::post('/church/{type}', [ChurchDirectoryController::class, 'store'])->whereIn('type', $types)->name('church.store');
        Route::put('/church/{type}/{id}', [ChurchDirectoryController::class, 'update'])->whereIn('type', $types)->whereNumber('id')->name('church.update');
        Route::delete('/church/{type}/{id}', [ChurchDirectoryController::class, 'destroy'])->whereIn('type', $types)->whereNumber('id')->name('church.destroy');

        Route::resource('admin-messages', AdminMessageController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['admin-messages' => 'adminMessage']);



Route::prefix('catalog')->name('catalog.')->group(function () {
    Route::get('/', [CatalogController::class, 'index'])->name('index');
    Route::get('lookups', [CatalogController::class, 'lookups'])->name('lookups');

    $e = 'categories|attribute-groups|attributes|units|brands';
    Route::get('{entity}', [CatalogController::class, 'list'])->where('entity', $e)->name('list');
    Route::post('{entity}', [CatalogController::class, 'store'])->where('entity', $e)->name('store');
    Route::get('{entity}/{id}', [CatalogController::class, 'show'])->where('entity', $e)->whereNumber('id')->name('show');
    Route::put('{entity}/{id}', [CatalogController::class, 'update'])->where('entity', $e)->whereNumber('id')->name('update');   // POST + _method=PUT (multipart)
    Route::delete('{entity}/{id}', [CatalogController::class, 'destroy'])->where('entity', $e)->whereNumber('id')->name('destroy');
});




    });
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

/* ---------------- Registration (public, user not logged in yet) ---------------- */
Route::post('/register/otp/send', [RegisterController::class, 'sendOtp'])
    ->middleware('throttle:5,1')->name('register.otp.send');

Route::post('/register/otp/verify', [RegisterController::class, 'verifyOtp'])
    ->middleware('throttle:10,1')->name('register.otp.verify');

Route::post('/register', [RegisterController::class, 'store'])
    ->middleware('throttle:10,1')->name('register.store');

Route::post('/login', [RegisterController::class, 'login'])->name('login.submit');
Route::post('/logout', [RegisterController::class, 'logout'])->middleware('auth')->name('logout');

/* ---------------- My account (trading) ---------------- */
Route::get('/trading/my_accout_details', [MyAccountController::class, 'show'])->name('trading.myaccount');
Route::post('/trading/my_account/update', [MyAccountController::class, 'update'])->name('network.myaccount.update');

/* ---------------- Profile (logged-in users only) ---------------- */
Route::middleware('auth')->prefix('profile')->group(function () {
    Route::get('/', [ProfileController::class, 'me'])->name('profile.me');
    Route::post('/update', [ProfileController::class, 'update'])->name('profile.update');

    Route::post('/privacy', [ProfileController::class, 'privacy'])->name('profile.privacy');
    Route::post('/image', [ProfileController::class, 'image'])->name('profile.image');
    Route::post('/gallery', [ProfileController::class, 'galleryStore'])->name('profile.gallery.store');
    Route::post('/password', [ProfileController::class, 'password'])->name('profile.password');

    Route::post('/social', [ProfileController::class, 'socialStore'])->name('profile.social.store');
    Route::put('/social/{id}', [ProfileController::class, 'socialUpdate'])->name('profile.social.update');
    Route::delete('/social/{id}', [ProfileController::class, 'socialDestroy'])->name('profile.social.destroy');

    Route::post('members/{uuid}/save', [Social::class, 'toggleSave'])->name('members.save');
    Route::post('members/{uuid}/block', [Social::class, 'toggleBlock'])->name('members.block');
    Route::post('members/{uuid}/share', [Social::class, 'share'])->middleware('throttle:30,1')->name('members.share');

    Route::get('messages', [Social::class, 'conversations'])->name('messages.index');
    Route::get('messages/{uuid}', [Social::class, 'thread'])->name('messages.thread');
    Route::post('messages/{uuid}', [Social::class, 'send'])->middleware('throttle:30,1')->name('messages.send');

    Route::post('admin-messages/read', [Social::class, 'readAdmin'])->name('admin-messages.read');
    Route::get('share/members', [Social::class, 'shareSearch'])->name('share.members');
    Route::get('activity', [ActivityController::class, 'index'])->name('activity');
});



Route::get('/products', [ProductListingController::class, 'retail'])
    ->name('products.index');

Route::get('/trading/products', [ProductListingController::class, 'trading'])
    ->name('trading.products.index');

Route::get('/products', [ProductListingController::class, 'retail'])
    ->name('products.index');

Route::get('/products/{slug}', [ProductListingController::class, 'showRetail'])
    ->name('products.show');

Route::get('/trading/products', [ProductListingController::class, 'trading'])
    ->name('trading.products.index');

Route::get('/trading/products/{slug}', [ProductListingController::class, 'showTrading'])
    ->name('trading.products.show');













// admin guard (separate from the member "web" guard). Change `auth:admin` if your guard/middleware is named differently.




Route::prefix('seller')->name('seller.')->group(function () {
    Route::get('/', [SellerController::class, 'index'])->name('index');
    // Route::post('/products', [SellerHubController::class, 'store'])->name('products.store');
    // Route::put('/products/{product}', [SellerHubController::class, 'update'])->name('products.update');
    // Route::delete('/products/{product}', [SellerHubController::class, 'destroy'])->name('products.destroy');
});



Route::prefix('seller')->name('seller.')->group(function () {

   

    // catalog helpers (category tree, attributes of a category)
    Route::get('categories/children', [SellerCatalogController::class, 'children'])->name('categories.children');
    Route::get('categories/{category}/attributes', [SellerCatalogController::class, 'attributes'])->name('categories.attributes');

    // products
    Route::get('products', [SellerProductController::class, 'index'])->name('products.index');
    Route::post('products', [SellerProductController::class, 'store'])->name('products.store');
    Route::get('products/{uuid}', [SellerProductController::class, 'show'])->name('products.show');
    Route::put('products/{uuid}', [SellerProductController::class, 'update'])->name('products.update');   // sent as POST + _method=PUT (multipart)
    Route::delete('products/{uuid}', [SellerProductController::class, 'destroy'])->name('products.destroy');
    Route::post('products/{uuid}/status', [SellerProductController::class, 'status'])->name('products.status');
    Route::put('products/{uuid}/details', [SellerProductController::class, 'details'])->name('products.details');
    Route::put('products/{uuid}/variants', [SellerProductController::class, 'variants'])->name('products.variants');
    Route::put('products/{uuid}/pricing', [SellerProductController::class, 'pricing'])->name('products.pricing');
    Route::put('products/{uuid}/bulk-rule', [SellerProductController::class, 'bulkRule'])->name('products.bulk');
    Route::delete('products/{uuid}/bulk-rule', [SellerProductController::class, 'disableBulk'])->name('products.bulk.disable');
    Route::put('products/{uuid}/tiers', [SellerProductController::class, 'tiers'])->name('products.tiers');
    Route::put('products/{uuid}/stock', [SellerProductController::class, 'stock'])->name('products.stock');

    Route::get('orders/{item}', [SellerOrderController::class, 'show'])->name('orders.show');
    Route::put('orders/{item}', [SellerOrderController::class, 'update'])->name('orders.update');
});
