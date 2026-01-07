<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Customer\AuthController as CustomerAuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ProductImportController as AdminProductImportController;
use App\Http\Controllers\PresenceController;

// Home
Route::get('/', function () {
    return view('welcome');
});

// Presence channel authentication & disconnect
// Allow both admin and customer guards
Route::post('/broadcasting/auth', [PresenceController::class, 'authenticate'])->middleware('web');
Route::post('/presence/disconnect', [PresenceController::class, 'disconnect'])->middleware('web');

// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Guest routes
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login']);
        Route::get('/register', [AdminAuthController::class, 'showRegisterForm'])->name('register');
        Route::post('/register', [AdminAuthController::class, 'register']);
    });

    // Protected routes
    Route::middleware(['admin'])->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        
        // Products - Import routes must come BEFORE resource route
        Route::get('/products/import', [AdminProductImportController::class, 'showImportForm'])->name('products.import');
        Route::post('/products/import', [AdminProductImportController::class, 'import'])->name('products.import.store');
        Route::resource('products', AdminProductController::class);
    });
});

// Customer Routes
Route::prefix('customer')->name('customer.')->group(function () {
    // Guest routes
    Route::middleware('guest:customer')->group(function () {
        Route::get('/login', [CustomerAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [CustomerAuthController::class, 'login']);
        Route::get('/register', [CustomerAuthController::class, 'showRegisterForm'])->name('register');
        Route::post('/register', [CustomerAuthController::class, 'register']);
    });

    // Protected routes
    Route::middleware(['customer'])->group(function () {
        Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard');
    });
});
