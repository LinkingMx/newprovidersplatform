<?php

use App\Http\Controllers\Supplier\ForgotPasswordController;
use App\Http\Controllers\Supplier\LoginController;
use App\Http\Controllers\Supplier\OnboardingController;
use App\Http\Controllers\Supplier\ResetPasswordController;
use App\Http\Controllers\Supplier\SetPasswordController;
use App\Http\Controllers\Supplier\SupplierDocumentController;
use App\Http\Middleware\RedirectIfSupplierAuthenticated;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

// Proveedor Dashboard
Route::get('dashboard', function () {
    $supplier = auth('supplier')->user()->load('branches');
    $documents = $supplier->documents()
        ->with(['documentType', 'documentState'])
        ->get()
        ->map(fn ($doc) => [
            'id' => $doc->id,
            'document_type' => [
                'id' => $doc->documentType->id,
                'nombre' => $doc->documentType->nombre,
            ],
            'document_state' => [
                'id' => $doc->documentState->id,
                'nombre' => $doc->documentState->nombre,
                'etiqueta' => $doc->documentState->etiqueta,
                'color' => $doc->documentState->color,
            ],
            'archivo_nombre' => $doc->archivo_nombre,
            'has_file' => $doc->archivo_path !== null,
            'can_upload' => in_array($doc->document_state_id, [1, 4]),
            'notas' => $doc->notas,
            'uploaded_at' => $doc->uploaded_at?->toISOString(),
        ]);

    return Inertia::render('Supplier/Dashboard', [
        'supplier' => $supplier,
        'documents' => $documents,
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
    Route::get('/supplier/forgot-password', [ForgotPasswordController::class, 'show'])
        ->name('supplier.forgot-password');
    Route::post('/supplier/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])
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

    Route::post('/supplier/documents/{supplierDocument}/upload', [SupplierDocumentController::class, 'upload'])
        ->name('supplier.documents.upload');
    Route::get('/supplier/documents/{supplierDocument}/download', [SupplierDocumentController::class, 'download'])
        ->name('supplier.documents.download');

    Route::post('/supplier/auth/logout', function () {
        auth('supplier')->logout();

        return redirect('/');
    })->name('supplier.logout');
});

require __DIR__.'/settings.php';
