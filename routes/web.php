<?php

use App\Http\Controllers\Admin\AdminAccountController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SalesReportController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pages.public.home', ['products' => config('storefront.products')]);
})->name('home');

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified', 'can:access admin'])
    ->name('dashboard');

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
    Route::get('reports', SalesReportController::class)
        ->middleware('can:view reports')
        ->name('reports.index');
    Route::get('transactions/{stage?}', [TransactionController::class, 'index'])
        ->middleware('can:view transactions')
        ->name('transactions.index');
    Route::patch('transactions/{order}', [TransactionController::class, 'update'])
        ->middleware('can:manage transactions')
        ->name('transactions.update');
    Route::view('themes', 'pages.admin.themes.index')
        ->middleware('can:view themes')
        ->name('themes.index');
    Route::resource('products', ProductController::class)
        ->only(['index'])
        ->middleware('can:view products');
    Route::resource('products', ProductController::class)
        ->only(['create', 'store', 'edit', 'update'])
        ->middleware('can:manage products');
});

require __DIR__.'/auth.php';
