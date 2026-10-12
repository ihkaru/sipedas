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

        // Jangan auto-redirect jika pengguna baru saja logout (?logged_out=1) atau mode login lokal darurat (?local=1)
        if (request()->has('logged_out') || request()->has('local')) {
            return;
        }

        // Auto-redirect langsung ke SSO SAML rIDM
        $this->redirect(route('saml.login'));
    }

    public function getHeading(): string|Htmlable
    {
        if (request()->has('logged_out')) {
            return 'Anda Telah Keluar';
        }

        if (request()->has('local')) {
            return 'Login Darurat (Lokal)';
        }

        return 'Masuk ke SIPEDAS';
    }

    public function getSubheading(): string|Htmlable|null
    {
        if (request()->has('logged_out')) {
            return new HtmlString(
                'Sesi kerja Anda telah aman diakhiri.<br>' .
                '<span class="text-xs text-gray-500 dark:text-gray-400">Butuh akses darurat? <a href="' . url('/a/login?local=1') . '" class="underline text-primary-600 dark:text-primary-400">Login akun lokal</a></span>'
            );
        }

        if (request()->has('local')) {
            return new HtmlString(
                'Mode darurat menggunakan akun database lokal.<br>' .
                '<span class="text-xs text-gray-500 dark:text-gray-400"><a href="' . route('saml.login') . '" class="underline text-primary-600 dark:text-primary-400">&larr; Masuk via SSO BPS Mempawah</a></span>'
            );
        }

        return 'Portal Manajemen Kegiatan & Perjalanan Dinas';
    }

    public function form(Schema $schema): Schema
    {
        if (!request()->has('local')) {
            // Hilangkan input email & password jika bukan mode lokal darurat
            return $schema->components([]);
        }

        return parent::form($schema);
    }

    protected function getFormActions(): array
    {
        if (!request()->has('local')) {
            return [
                Action::make('saml_login')
                    ->label('Masuk dengan SSO BPS Mempawah (SAML 2.0)')
                    ->icon('heroicon-o-arrow-right-end-on-rectangle')
                    ->url(route('saml.login'))
                    ->button()
                    ->color('primary'),
            ];
        }

        return [
            $this->getAuthenticateFormAction(),
        ];
    }

    public function hasFullWidthFormActions(): bool
    {
        return true;
    }
}
