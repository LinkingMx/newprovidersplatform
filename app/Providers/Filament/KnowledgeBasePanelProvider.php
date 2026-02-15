<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Guava\FilamentKnowledgeBase\Plugins\KnowledgeBasePlugin;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class KnowledgeBasePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('knowledge-base')
            ->path('kb')
            ->colors([
                'primary' => [
                    50 => '253, 252, 250',
                    100 => '250, 247, 242',
                    200 => '245, 239, 230',
                    300 => '235, 223, 199',
                    400 => '220, 201, 163',
                    500 => '197, 160, 89',
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
                    800 => '25, 23, 49',
                    900 => '18, 16, 36',
                    950 => '13, 15, 26',
                ],
            ])
            ->font('Open Sans')
            ->darkMode(true)
            ->brandLogo(asset('images/logo-dark.svg'))
            ->darkModeBrandLogo(asset('images/logo-light.svg'))
            ->brandLogoHeight('2rem')
            ->viteTheme('resources/css/filament/knowledge-base/theme.css')
            ->plugins([
                KnowledgeBasePlugin::make(),
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
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
