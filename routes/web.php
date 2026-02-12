<?php

use App\Http\Controllers\Supplier\OnboardingController;
use App\Http\Controllers\Supplier\SetPasswordController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Supplier Routes
Route::middleware('guest:supplier')->group(function () {
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

    Route::get('/supplier/dashboard', function () {
        return Inertia::render('Supplier/Dashboard', [
            'supplier' => auth('supplier')->user(),
        ]);
    })->name('supplier.dashboard');

    Route::post('/supplier/auth/logout', function () {
        auth('supplier')->logout();

        return redirect('/');
    })->name('supplier.logout');
});

require __DIR__.'/settings.php';
