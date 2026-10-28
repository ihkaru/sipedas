<?php

namespace Tests\Feature;

use Tests\TestCase;

class SipancongLegacyRedirectTest extends TestCase
{
    public function test_legacy_sipancong_url_redirects_to_pembayaran(): void
    {
        $response = $this->get('/a/sipancong/pengajuans?activeTab=PPK');

        $response->assertStatus(301);
        $response->assertRedirect('/a/pembayaran/pengajuans?activeTab=PPK');
    }

    public function test_legacy_sipancong_root_redirects(): void
    {
        $response = $this->get('/a/sipancong');

        $response->assertStatus(301);
        $response->assertRedirect('/a/pembayaran/');
    }
}
