<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Permite que un admin (web guard) inicie sesión como un proveedor (supplier
 * guard) sin perder su propia sesión admin. Útil para soporte: el admin ve
 * el dashboard exactamente como lo ve el proveedor.
 *
 * Mientras está activa, `session('impersonator_id')` guarda el id del admin
 * original. El banner del SupplierLayout lee ese estado vía Inertia shared
 * props para ofrecer un botón de "Salir de la vista".
 */
class ImpersonateSupplierController
{
    public const SESSION_KEY = 'impersonator_id';

    public function start(Request $request, Supplier $supplier): RedirectResponse
    {
        $admin = $request->user();

        abort_unless($admin instanceof User && $admin->can('impersonate_supplier'), 403);

        if ($request->session()->has(self::SESSION_KEY)) {
            abort(409, 'Ya estás impersonando a otro proveedor.');
        }

        $request->session()->put(self::SESSION_KEY, $admin->id);
        Auth::guard('supplier')->login($supplier);

        activity()
            ->causedBy($admin)
            ->performedOn($supplier)
            ->withProperties(['admin_email' => $admin->email, 'supplier_email' => $supplier->email])
            ->log('impersonation_started');

        return redirect()->route('dashboard');
    }

    public function stop(Request $request): RedirectResponse
    {
        $impersonatorId = $request->session()->get(self::SESSION_KEY);

        if (! $impersonatorId) {
            return redirect('/admin');
        }

        $admin = User::find($impersonatorId);
        $supplier = Auth::guard('supplier')->user();

        if ($admin && $supplier) {
            activity()
                ->causedBy($admin)
                ->performedOn($supplier)
                ->withProperties(['admin_email' => $admin->email, 'supplier_email' => $supplier->email])
                ->log('impersonation_stopped');
        }

        Auth::guard('supplier')->logout();
        $request->session()->forget(self::SESSION_KEY);

        return redirect('/admin/suppliers');
    }
}
