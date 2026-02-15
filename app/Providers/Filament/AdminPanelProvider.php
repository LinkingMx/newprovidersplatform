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
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Guava\FilamentKnowledgeBase\Plugins\KnowledgeBaseCompanionPlugin;
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
                    50 => '253, 252, 250',
                    100 => '250, 247, 242',
                    200 => '245, 239, 230',
                    300 => '235, 223, 199',  // Cream
                    400 => '220, 201, 163',
                    500 => '197, 160, 89',   // Gold
                    600 => '168, 134, 61',
                    700 => '138, 107, 47',
                    800 => '107, 82, 36',
                    900 => '77, 59, 26',
                    950 => '61, 53, 42',
                ],
                'gray' => [
                    50 => '253, 252, 250',
                    100 => '245, 244, 242',
                    200 => '232, 230, 227',
                    300 => '212, 209, 204',
                    400 => '158, 154, 148',
                    500 => '107, 102, 96',
                    600 => '74, 69, 64',
                    700 => '45, 42, 69',
                    800 => '25, 23, 49',   // Navy
                    900 => '18, 16, 36',
                    950 => '13, 15, 26',
                ],
                'warning' => [
                    50 => '253, 249, 240',
                    100 => '250, 240, 219',
                    200 => '245, 223, 179',
                    300 => '232, 199, 122',
                    400 => '212, 172, 94',
                    500 => '197, 160, 89',   // Gold
                    600 => '168, 134, 61',
                    700 => '138, 107, 47',
                    800 => '107, 82, 36',
                    900 => '77, 59, 26',
                    950 => '47, 35, 19',
                ],
            ])
            ->font('Open Sans')
            ->darkMode(true)
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth(\Filament\Support\Enums\Width::Full)
            ->viteTheme('resources/css/filament/admin/theme.css')
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
                KnowledgeBaseCompanionPlugin::make()
                    ->knowledgeBasePanelId('knowledge-base')
                    ->modalPreviews()
                    ->slideOverPreviews(),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
