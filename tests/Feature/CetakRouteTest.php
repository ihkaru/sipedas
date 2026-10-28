<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CetakRouteTest extends TestCase
{
    public function test_cetak_bast_route_is_defined(): void
    {
        $this->assertTrue(Route::has('cetak.bast'), 'Route [cetak.bast] must be registered in routes/web.php');

        $url = route('cetak.bast', [
            'tahun' => 2026,
            'bulan' => 10,
            'id_kegiatan_manmit' => 'SPUNP26-TW-TW3-triwulan-3',
        ]);

        $this->assertStringContainsString('/cetak/bast', $url);
        $this->assertStringContainsString('tahun=2026', $url);
        $this->assertStringContainsString('bulan=10', $url);
        $this->assertStringContainsString('id_kegiatan_manmit=SPUNP26-TW-TW3-triwulan-3', $url);
    }

    public function test_cetak_kontrak_route_is_defined(): void
    {
        $this->assertTrue(Route::has('cetak.kontrak'), 'Route [cetak.kontrak] must be registered in routes/web.php');

        $url = route('cetak.kontrak', [
            'tahun' => 2026,
            'bulan' => 10,
        ]);

        $this->assertStringContainsString('/cetak/kontrak', $url);
    }
}
