<?php

use App\Http\Controllers\Auth\BranchSelectionController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Guest (unauthenticated) routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);

    Route::get('/forgot-password', [ForgotPasswordController::class, 'show'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'send'])->name('password.email');

    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'show'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'update'])->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Authenticated routes that complete the login flow (no post-login gate)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/otp', [OtpController::class, 'show'])->name('otp.show');
    Route::post('/otp', [OtpController::class, 'verify'])->name('otp.verify');
    Route::post('/otp/resend', [OtpController::class, 'resend'])->name('otp.resend');

    Route::get('/select-branch', [BranchSelectionController::class, 'show'])->name('branch.select');
    Route::post('/select-branch', [BranchSelectionController::class, 'select'])->name('branch.select.store');
});

/*
|--------------------------------------------------------------------------
| Application routes (require OTP verification + branch selection)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'post.login'])->group(function () {
    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::view('/dashboard', 'dashboard')->name('dashboard');

    // Point of Sale
    Route::view('/pos', 'pos.index')->name('pos.index');

    // Ledger
    Route::view('/ledger', 'ledger.index')->name('ledger.index');

    // Purchases
    Route::get('/purchases', [\App\Http\Controllers\PurchaseController::class, 'index'])->name('purchases.index');
    Route::view('/purchases/create', 'purchases.create')->name('purchases.create');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // User management
    Route::post('/users/{user}/password', [UserController::class, 'sendPasswordReset'])->name('users.password');
    Route::patch('/users/{user}/block', [UserController::class, 'toggleBlock'])->name('users.block');
    Route::resource('users', UserController::class)->except('show');
});
