<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Admin\ImpersonateSupplierController;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Symfony\Component\HttpFoundation\Response;

class HandleInertiaRequests extends Middleware
{
    public function handle(Request $request, \Closure $next): Response
    {
        if ($request->is('admin/*') || $request->is('admin') || $request->is('livewire/*')) {
            return $next($request);
        }

        return parent::handle($request, $next);
    }

    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'flash' => [
                'status' => $request->session()->get('status'),
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
            'impersonating' => fn () => $this->resolveImpersonating($request),
        ];
    }

    /**
     * @return array{admin: array{name: string, email: string}, supplier: array{name: string, email: string}, stopUrl: string}|null
     */
    private function resolveImpersonating(Request $request): ?array
    {
        $impersonatorId = $request->session()->get(ImpersonateSupplierController::SESSION_KEY);

        if (! $impersonatorId) {
            return null;
        }

        $admin = User::find($impersonatorId);
        $supplier = $request->user('supplier');

        if (! $admin || ! $supplier) {
            return null;
        }

        return [
            'admin' => ['name' => $admin->name, 'email' => $admin->email],
            'supplier' => ['name' => $supplier->name, 'email' => $supplier->email],
            'stopUrl' => route('admin.suppliers.impersonate.stop'),
        ];
    }
}
