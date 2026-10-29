<?php

use App\Http\Controllers\Admin\AdminAccountController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\HeroAdController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\SalesReportController;
use App\Http\Controllers\Admin\StoreLogoController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ProfileController;
use App\Models\HeroAd;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    $ads = HeroAd::query()
        ->orderBy('sort_order')
        ->orderBy('id')
        ->get();
    $heroSlides = $ads
        ->where('is_active', true)
        ->map(fn (HeroAd $ad): array => [
            'eyebrow' => $ad->eyebrow,
            'title' => $ad->title,
            'accent' => $ad->accent,
            'copy' => $ad->copy,
            'image_url' => Storage::disk('public')->url($ad->image_path),
        ]);

    if ($ads->isEmpty()) {
        $heroSlides = collect(config('storefront.hero_slides'))
            ->map(fn (array $slide): array => [
                ...$slide,
                'image_url' => asset('images/'.$slide['image']),
            ]);
    }

    return view('pages.public.home', [
        'products' => config('storefront.products'),
        'heroSlides' => $heroSlides,
    ]);
})->name('home');

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified', 'can:access admin'])
    ->name('dashboard');

Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout/cart', [CheckoutController::class, 'storeCart'])->name('checkout.cart');
Route::post('/checkout/order', [CheckoutController::class, 'storeOrder'])->name('checkout.order');
Route::get('/checkout/payment/{order}', [CheckoutController::class, 'payment'])->name('checkout.payment');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('admin-accounts', AdminAccountController::class)
        ->only(['index', 'create', 'store'])
        ->middleware('can:manage admin accounts');
    Route::resource('users', UserController::class)
        ->only(['index', 'edit', 'update'])
        ->middleware('can:manage customers');
    Route::resource('categories', CategoryController::class)
        ->only(['index', 'edit'])
        ->middleware('can:view categories');
    Route::resource('categories', CategoryController::class)
        ->only(['store', 'update'])
        ->middleware('can:manage categories');
    Route::get('reports/export', [SalesReportController::class, 'export'])
        ->middleware('can:view reports')
        ->name('reports.export');
    Route::get('reports', SalesReportController::class)
        ->middleware('can:view reports')
        ->name('reports.index');
    Route::resource('promotions', PromotionController::class)
        ->only(['index'])
        ->middleware('can:view promotions');
    Route::resource('promotions', PromotionController::class)
        ->only(['create', 'store', 'edit', 'update', 'destroy'])
        ->middleware('can:manage promotions');
    Route::resource('ads', HeroAdController::class)
        ->only(['index'])
        ->middleware('can:view ads');
    Route::resource('ads', HeroAdController::class)
        ->only(['create', 'store', 'edit', 'update', 'destroy'])
        ->middleware('can:manage ads');
    Route::get('transactions/{stage?}', [TransactionController::class, 'index'])
        ->middleware('can:view transactions')
        ->name('transactions.index');
    Route::patch('transactions/{order}', [TransactionController::class, 'update'])
        ->middleware('can:manage transactions')
        ->name('transactions.update');
    Route::post('transactions/{order}/confirm-payment', [TransactionController::class, 'confirmPayment'])
        ->middleware('can:manage transactions')
        ->name('transactions.confirm-payment');
    Route::view('themes', 'pages.admin.themes.index')
        ->middleware('can:view themes')
        ->name('themes.index');
    Route::post('themes/logo', [StoreLogoController::class, 'update'])
        ->middleware('can:view themes')
        ->name('themes.logo.update');
    Route::get('products/{product}/photo', [ProductController::class, 'photo'])
        ->middleware('can:view products')
        ->name('products.photo');
    Route::get('products/export', [ProductController::class, 'export'])
        ->middleware('can:view products')
        ->name('products.export');
    Route::resource('products', ProductController::class)
        ->only(['index'])
        ->middleware('can:view products');
    Route::resource('products', ProductController::class)
        ->only(['create', 'store', 'edit', 'update', 'destroy'])
        ->middleware('can:manage products');
});

require __DIR__.'/auth.php';
