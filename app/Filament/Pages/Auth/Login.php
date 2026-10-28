<?php

namespace App\Filament\Pages\Auth;

use Filament\Actions\Action;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\Layout;

#[Layout('filament-panels::components.layout.simple')]
class Login extends BaseLogin
{
    protected static string $layout = 'filament-panels::components.layout.simple';

    public function mount(): void
    {
        parent::mount();

        $authMode = config('satker.auth.mode', 'sso_saml');

        // Hanya auto-redirect jika mode memang 'sso_saml' dan bukan logout / local request
        if ($authMode === 'sso_saml') {
            if (request()->has('logged_out') || request()->has('local')) {
                return;
            }

            if (\Illuminate\Support\Facades\Route::has(config('satker.auth.sso_route', 'saml.login'))) {
                $this->redirect(route(config('satker.auth.sso_route', 'saml.login')));
            }
        }
    }

    public function getHeading(): string|Htmlable
    {
        if (request()->has('logged_out')) {
            return 'Anda Telah Keluar';
        }

        if (request()->has('local')) {
            return 'Login Akun Lokal';
        }

        $namaSatker = config('satker.nama', 'DOKTER-V');
        return 'Masuk ke ' . $namaSatker;
    }

    public function getSubheading(): string|Htmlable|null
    {
        $authMode = config('satker.auth.mode', 'sso_saml');
        $ssoLabel = config('satker.auth.sso_label', 'Masuk via SSO BPS');

        if (request()->has('logged_out')) {
            return new HtmlString(
                'Sesi kerja Anda telah aman diakhiri.<br>' .
                '<span class="text-xs text-gray-500 dark:text-gray-400">Butuh akses akun lokal? <a href="' . url('/a/login?local=1') . '" class="underline text-primary-600 dark:text-primary-400">Klik di sini</a></span>'
            );
        }

        if (request()->has('local') && $authMode === 'sso_saml') {
            return new HtmlString(
                'Mode akses menggunakan akun database lokal.<br>' .
                '<span class="text-xs text-gray-500 dark:text-gray-400"><a href="' . route('saml.login') . '" class="underline text-primary-600 dark:text-primary-400">&larr; ' . e($ssoLabel) . '</a></span>'
            );
        }

        return 'Portal Manajemen Kegiatan, Administrasi Surat & Perjalanan Dinas';
    }

    public function form(Schema $schema): Schema
    {
        $authMode = config('satker.auth.mode', 'sso_saml');

        // Pada mode sso_saml murni, sembunyikan form input lokal kecuali jika ?local=1
        if ($authMode === 'sso_saml' && !request()->has('local')) {
            return $schema->components([]);
        }

        return parent::form($schema);
    }

    protected function getFormActions(): array
    {
        $authMode = config('satker.auth.mode', 'sso_saml');
        $actions = [];

        // Tampilkan tombol submit form email & password jika bukan mode sso_saml murni tanpa ?local
        if ($authMode !== 'sso_saml' || request()->has('local')) {
            $actions[] = $this->getAuthenticateFormAction();
        }

        // Tampilkan tombol SSO jika mode hybrid atau mode sso_saml tanpa ?local
        if ($authMode === 'hybrid' || ($authMode === 'sso_saml' && !request()->has('local'))) {
            if (\Illuminate\Support\Facades\Route::has(config('satker.auth.sso_route', 'saml.login'))) {
                $actions[] = Action::make('saml_login')
                    ->label(config('satker.auth.sso_label', 'Masuk dengan SSO BPS (SAML 2.0)'))
                    ->icon('heroicon-o-arrow-right-end-on-rectangle')
                    ->url(route(config('satker.auth.sso_route', 'saml.login')))
                    ->button()
                    ->color($authMode === 'hybrid' ? 'gray' : 'primary');
            }
        }

        return $actions;
    }

    public function hasFullWidthFormActions(): bool
    {
        return true;
    }
}
