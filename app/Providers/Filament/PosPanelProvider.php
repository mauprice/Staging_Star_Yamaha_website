<?php

namespace App\Providers\Filament;

use App\Filament\Pos\Pages\PosTerminal;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/**
 * Standalone panel for the in-store POS terminal, kept separate from the
 * main admin panel so a shop-floor device can be logged into /pos without
 * exposing the rest of the admin area. Every operator uses their own
 * account (same Users table, no separate credential store) so each sale's
 * cashier_id reflects who actually processed it.
 */
class PosPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('pos')
            ->path('pos')
            ->authGuard('pos')
            ->login()
            ->profile()
            ->colors([
                'primary' => Color::Emerald,
            ])
            ->maxContentWidth(Width::Full)
            ->brandName('Star Yamaha — POS')
            ->navigation(false)
            ->pages([
                PosTerminal::class,
            ])
            ->homeUrl(fn () => PosTerminal::getUrl())
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
