<?php

use App\Http\Controllers\Supplier\BranchRequestController;
use App\Http\Controllers\Supplier\DashboardController;
use App\Http\Controllers\Supplier\ForgotPasswordController;
use App\Http\Controllers\Supplier\LoginController;
use App\Http\Controllers\Supplier\LogoutController;
use App\Http\Controllers\Supplier\OnboardingController;
use App\Http\Controllers\Supplier\ProfileController;
use App\Http\Controllers\Supplier\ResetPasswordController;
use App\Http\Controllers\Supplier\SetPasswordController;
use App\Http\Controllers\Supplier\SupplierDocumentController;
use App\Http\Middleware\RedirectIfSupplierAuthenticated;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::get('dashboard', DashboardController::class)
    ->middleware('auth:supplier')
    ->name('dashboard');

// Supplier Routes
Route::middleware(RedirectIfSupplierAuthenticated::class)->group(function () {
    Route::get('/supplier/login', [LoginController::class, 'show'])
        ->name('supplier.login');
    Route::post('/supplier/login', [LoginController::class, 'store'])
        ->middleware('throttle:supplier-login')
        ->name('supplier.login.store');
    Route::get('/supplier/set-password', [SetPasswordController::class, 'show'])
        ->name('supplier.set-password');
    Route::post('/supplier/auth/set-password', [SetPasswordController::class, 'store'])
        ->name('supplier.set-password.store');
    Route::get('/supplier/forgot-password', [ForgotPasswordController::class, 'show'])
        ->name('supplier.forgot-password');
    Route::post('/supplier/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])
        ->middleware('throttle:supplier-forgot-password')
        ->name('supplier.forgot-password.store');
    Route::get('/supplier/reset-password', [ResetPasswordController::class, 'show'])
        ->name('supplier.reset-password');
    Route::post('/supplier/reset-password', [ResetPasswordController::class, 'reset'])
        ->name('supplier.reset-password.store');
});

Route::middleware('auth:supplier')->group(function () {
    Route::get('/supplier/onboarding', [OnboardingController::class, 'show'])
        ->name('supplier.onboarding');
    Route::post('/supplier/onboarding/submit', [OnboardingController::class, 'submit'])
        ->name('supplier.onboarding.submit');

    Route::get('/supplier/profile/edit', [ProfileController::class, 'edit'])
        ->name('supplier.profile.edit');
    Route::put('/supplier/profile', [ProfileController::class, 'update'])
        ->name('supplier.profile.update');

    Route::post('/supplier/documents/{supplierDocument}/upload', [SupplierDocumentController::class, 'upload'])
        ->middleware('throttle:supplier-document-upload')
        ->name('supplier.documents.upload');
    Route::get('/supplier/documents/{supplierDocument}/preview', [SupplierDocumentController::class, 'preview'])
        ->name('supplier.documents.preview');
    Route::get('/supplier/documents/{supplierDocument}/download', [SupplierDocumentController::class, 'download'])
        ->name('supplier.documents.download');
    Route::delete('/supplier/documents/{supplierDocument}', [SupplierDocumentController::class, 'destroy'])
        ->name('supplier.documents.destroy');

    Route::post('/supplier/branch-requests', [BranchRequestController::class, 'store'])
        ->name('supplier.branch-requests.store');

    Route::post('/supplier/auth/logout', LogoutController::class)
        ->name('supplier.logout');
});

require __DIR__.'/settings.php';
