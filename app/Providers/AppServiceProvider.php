<?php

namespace App\Providers;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->registerMailViews();
        $this->registerGates();
    }

    /**
     * Register custom abilities (Spatie permissions still apply via $user->can()).
     */
    private function registerGates(): void
    {
        // Admins con rol super_admin (Shield) pueden iniciar sesión como
        // cualquier proveedor para soporte. Shield permite asignar la
        // permission a otros roles desde /admin/roles si se desea.
        Gate::define('impersonate_supplier', function (User $user): bool {
            if ($user->hasRole('super_admin')) {
                return true;
            }

            try {
                return $user->hasPermissionTo('impersonate_supplier');
            } catch (\Throwable) {
                return false;
            }
        });
    }

    /**
     * Register mail view namespace.
     */
    private function registerMailViews(): void
    {
        View::addNamespace('mail', resource_path('views'));
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null
        );
    }
}
