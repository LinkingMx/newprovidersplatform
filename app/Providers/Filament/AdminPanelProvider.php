<?php

namespace App\Providers\Filament;

use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(\App\Filament\Pages\Login::class)
            ->passwordReset()
            ->renderHook(
                \Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
                fn (): string => \Illuminate\Support\Facades\Blade::render('<div class="text-center"><x-filament::link href="/" icon="heroicon-m-arrow-left" icon-position="before">Volver al inicio</x-filament::link></div>'),
            )
            ->colors([
                'primary' => [
                    50 => '246, 244, 250',
                    100 => '233, 227, 242',
                    200 => '213, 203, 229',
                    300 => '183, 166, 210',
                    400 => '147, 122, 185',
                    500 => '115, 87, 156', // Púrpura principal (ajustado para legibilidad)
                    600 => '92, 67, 129',
                    700 => '75, 54, 105',
                    800 => '64, 47, 88',
                    900 => '54, 41, 74',
                    950 => '40, 29, 65', // El púrpura oscuro del fondo del logo
                ],
                'gray' => Color::Slate, // Slate combina mejor con tonos púrpuras que Gray puro
            ])
            ->font('Instrument Sans')
            ->brandLogo(asset('images/logo-dark.svg'))
            ->darkModeBrandLogo(asset('images/logo-light.svg'))
            ->brandLogoHeight('2rem')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make()
                    ->navigationLabel('Roles')
                    ->modelLabel('Rol')
                    ->pluralModelLabel('Roles')
                    ->navigationGroup('Administración')
                    ->navigationSort(7),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
