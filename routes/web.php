<?php

use App\Http\Controllers\Supplier\LoginController;
use App\Http\Controllers\Supplier\OnboardingController;
use App\Http\Controllers\Supplier\SetPasswordController;
use App\Http\Middleware\RedirectIfSupplierAuthenticated;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

// Proveedor Dashboard
Route::get('dashboard', function () {
    return Inertia::render('Supplier/Dashboard', [
        'supplier' => auth('supplier')->user(),
    ]);
})->middleware('auth:supplier')->name('dashboard');

// Supplier Routes
Route::middleware(RedirectIfSupplierAuthenticated::class)->group(function () {
    Route::get('/supplier/login', [LoginController::class, 'show'])
        ->name('supplier.login');
    Route::post('/supplier/login', [LoginController::class, 'store'])
        ->name('supplier.login.store');
    Route::get('/supplier/set-password', [SetPasswordController::class, 'show'])
        ->name('supplier.set-password');
    Route::post('/supplier/auth/set-password', [SetPasswordController::class, 'store'])
        ->name('supplier.set-password.store');
});

Route::middleware('auth:supplier')->group(function () {
    Route::get('/supplier/onboarding', [OnboardingController::class, 'show'])
        ->name('supplier.onboarding');
    Route::post('/supplier/onboarding/submit', [OnboardingController::class, 'submit'])
        ->name('supplier.onboarding.submit');

    Route::post('/supplier/auth/logout', function () {
        auth('supplier')->logout();

        return redirect('/');
    })->name('supplier.logout');
});

require __DIR__.'/settings.php';
