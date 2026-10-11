<?php

namespace App\Providers\Filament;

use App\Filament\Resources\PenugasanResource\Widgets\PenugasanDisetujuiTable;
use App\Filament\Resources\PenugasanResource\Widgets\PenugasanTable;
use App\Filament\Resources\PenugasanResource\Widgets\StatusSuratTugasAndaChart;
use App\Filament\Resources\PenugasanResource\Widgets\StatusSuratTugasChart;
use App\Filament\Resources\RiwayatPengajuanResource\Widgets\RiwayatPengajuanTable;
use Filament\FontProviders\SpatieGoogleFontProvider;
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

class APanelProvider extends PanelProvider {
    public function panel(Panel $panel): Panel {
        return $panel
            ->default()
            ->id('a')
            ->spa()
            ->path('a')
            ->login()
            // ->font('Inter', provider: SpatieGoogleFontProvider::class)
            ->favicon(asset('favicon.ico'))
            // ->brandLogo(env('APP_URL')."/logo.svg")
            ->brandName('Dokter V')
            ->colors([
                'primary' => Color::Teal,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
                \App\Filament\Pages\RekapPerjadin::class,
                \App\Filament\Pages\KalenderPerjadin::class,
                \App\Filament\Pages\MatriksPerjadinPegawai::class,
                \App\Filament\Pages\MonitorHonorMitra::class,
                \App\Filament\Pages\JadwalTerpadu::class,
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
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_END,
                fn (): string => \Illuminate\Support\Facades\Blade::render("@vite(['resources/css/app.css', 'resources/js/filament-chart-js-plugins.js'])")
            )
            ->renderHook(
                \Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
                fn (): string => '
                    <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e5e7eb; text-align: center;">
                        <div style="margin-bottom: 0.75rem; font-size: 0.75rem; text-transform: uppercase; color: #6b7280; font-weight: 500;">
                            Atau Masuk Terpadu
                        </div>
                        <a href="' . route('saml.login') . '" style="display: flex; align-items: center; justify-content: center; width: 100%; padding: 0.625rem 1rem; font-size: 0.875rem; font-weight: 600; color: #ffffff; background-color: #0f766e; border-radius: 0.5rem; text-decoration: none; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); transition: background-color 0.2s;" onmouseover="this.style.backgroundColor=\'#115e59\'" onmouseout="this.style.backgroundColor=\'#0f766e\'">
                            <svg style="width: 1.25rem; height: 1.25rem; margin-right: 0.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Masuk dengan SSO BPS (SAML 2.0)
                        </a>
                    </div>
                '
            )
            ->renderHook(
                \Filament\View\PanelsRenderHook::SCRIPTS_AFTER,
                fn (): string => '<script>
                    if (window.location.protocol === "https:" && window.livewireScriptConfig) {
                        if (window.livewireScriptConfig.uri && window.livewireScriptConfig.uri.startsWith("http:")) {
                            window.livewireScriptConfig.uri = window.livewireScriptConfig.uri.replace(/^http:/, "https:");
                        }
                        if (window.livewireScriptConfig.moduleUrl && window.livewireScriptConfig.moduleUrl.startsWith("http:")) {
                            window.livewireScriptConfig.moduleUrl = window.livewireScriptConfig.moduleUrl.replace(/^http:/, "https:");
                        }
                    }
                </script>'
            )
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
            ])
        ;
    }
}
