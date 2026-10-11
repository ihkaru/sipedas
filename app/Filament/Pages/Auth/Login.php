<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;

class Login extends BaseLogin
{
    public function mount(): void
    {
        parent::mount();

        // Redirect langsung ke SAML SSO rIDM kecuali jika parameter ?local=1 disertakan untuk akses darurat
        if (!request()->has('local')) {
            $this->redirect(route('saml.login'));
        }
    }
}
