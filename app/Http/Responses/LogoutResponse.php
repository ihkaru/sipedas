<?php

namespace App\Http\Responses;

use Filament\Auth\Http\Responses\Contracts\LogoutResponse as Responsable;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class LogoutResponse implements Responsable
{
    public function toResponse($request): RedirectResponse | Redirector
    {
        // Alihkan ke SAML SLO agar sesi IdP di rIDM juga dihentikan, lalu mendarat di ?logged_out=1
        return redirect()->route('saml.logout');
    }
}
