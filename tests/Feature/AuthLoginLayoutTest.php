<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthLoginLayoutTest extends TestCase
{
    public function test_login_redirects_to_saml_when_sso_mode_is_active(): void
    {
        config(['satker.auth.mode' => 'sso_saml']);
        $response = $this->get('/a/login');

        $response->assertStatus(302);
        $response->assertRedirect(route('saml.login'));
    }

    public function test_login_local_emergency_mode_renders_successfully(): void
    {
        config(['satker.auth.mode' => 'sso_saml']);
        $response = $this->get('/a/login?local=1');

        $response->assertStatus(200);
        $response->assertSee('Login Akun Lokal');
    }

    public function test_login_renders_email_password_form_in_email_password_mode(): void
    {
        config(['satker.auth.mode' => 'email_password']);
        $response = $this->get('/a/login');

        $response->assertStatus(200);
    }

    public function test_login_logged_out_mode_renders_successfully(): void
    {
        $response = $this->get('/a/login?logged_out=1');

        $response->assertStatus(200);
        $response->assertSee('Anda Telah Keluar');
    }

    public function test_livewire_component_layout_config_is_safe(): void
    {
        $this->assertEquals(
            'filament-panels::components.layout.simple',
            config('livewire.component_layout')
        );
    }
}
