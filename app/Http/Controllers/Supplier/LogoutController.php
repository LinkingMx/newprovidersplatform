<?php

declare(strict_types=1);

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Admin\ImpersonateSupplierController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LogoutController
{
    public function __invoke(Request $request): RedirectResponse
    {
        // Si esta sesión es una impersonación, redirigimos al admin en vez de a /.
        $wasImpersonation = $request->session()->has(ImpersonateSupplierController::SESSION_KEY);

        auth('supplier')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $wasImpersonation
            ? redirect('/admin/suppliers')
            : redirect()->route('home');
    }
}
