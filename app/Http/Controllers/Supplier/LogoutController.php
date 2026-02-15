<?php

declare(strict_types=1);

namespace App\Http\Controllers\Supplier;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LogoutController
{
    public function __invoke(Request $request): RedirectResponse
    {
        auth('supplier')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
