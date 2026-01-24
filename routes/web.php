<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Auth\AddressController;

// Public routes
Route::get('/', [\App\Http\Controllers\Public\HomeController::class, 'index'])->name('home');
Route::get('/categories', [\App\Http\Controllers\Public\CategoriesController::class, 'index'])->name('categories.index');

// Authentication routes (must be before dynamic routes)
Route::middleware('guest')->group(function () {
    // Registration
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    // Login
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    // Password reset
    Route::get('/forgot-password', [PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});

// Dynamic routes (must be last to avoid conflicts)
Route::get('/products/{productSlug}', [\App\Http\Controllers\Public\ProductsController::class, 'show'])->name('products.show');
Route::get('/categories/{categorySlug}', [\App\Http\Controllers\Public\ProductsController::class, 'index'])->name('products.index');

// Profile route (accessible to all, shows stub for guests)
Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Profile (edit routes require auth)
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/password/edit', [ProfileController::class, 'editPassword'])->name('profile.edit-password');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    // Addresses
    Route::get('/profile/addresses', [AddressController::class, 'index'])->name('profile.addresses.index');
    Route::get('/profile/addresses/create', [AddressController::class, 'create'])->name('profile.addresses.create');
    Route::post('/profile/addresses', [AddressController::class, 'store'])->name('profile.addresses.store');
    Route::get('/profile/addresses/{addressId}/edit', [AddressController::class, 'edit'])->name('profile.addresses.edit');
    Route::put('/profile/addresses/{addressId}', [AddressController::class, 'update'])->name('profile.addresses.update');
    Route::delete('/profile/addresses/{addressId}', [AddressController::class, 'destroy'])->name('profile.addresses.destroy');
    Route::post('/profile/addresses/{addressId}/set-default', [AddressController::class, 'setDefault'])->name('profile.addresses.set-default');

    // Email verification
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware('signed')
        ->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});
