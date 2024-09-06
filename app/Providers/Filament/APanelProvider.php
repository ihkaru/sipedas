<?php

namespace App\Providers\Filament;

use App\Filament\Resources\PenugasanResource\Widgets\PenugasanDisetujuiTable;
use App\Filament\Resources\PenugasanResource\Widgets\PenugasanTable;
use App\Filament\Resources\PenugasanResource\Widgets\StatusSuratTugasAndaChart;
use App\Filament\Resources\PenugasanResource\Widgets\StatusSuratTugasChart;
use App\Filament\Resources\RiwayatPengajuanResource\Widgets\RiwayatPengajuanTable;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class APanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('a')
            ->spa()
            ->path('a')
            ->login()
            ->favicon(env('APP_URL')."/favicon.ico")
            // ->brandLogo(env('APP_URL')."/logo.svg")
            ->brandName('SIKENDIS')
            ->colors([
                'primary' => Color::Teal,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                StatusSuratTugasChart::class,
                StatusSuratTugasAndaChart::class,
                RiwayatPengajuanTable::class,
                PenugasanDisetujuiTable::class,
                PenugasanTable::class,
                // Widgets\AccountWidget::class,

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
            ])
            ->plugins([
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make()
            ])
            ;
    }
}
