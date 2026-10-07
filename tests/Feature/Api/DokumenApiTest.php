<?php

namespace Tests\Feature\Api;

use App\Models\AlokasiHonor;
use App\Models\Honor;
use App\Models\KegiatanManmit;
use App\Models\Kemitraan;
use App\Models\Mitra;
use App\Models\Setting;
use App\Services\HonorAllocationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DokumenApiTest extends TestCase
{
    use RefreshDatabase;

    protected string $apiKey = 'test-secret-api-key-12345';

    protected function setUp(): void
    {
        parent::setUp();

        config(['sipedas.api_key' => $this->apiKey]);

        Setting::updateOrCreate(
            ['key' => 'STANDAR_BIAYA_MASUKAN_LAINNYA_NON_PNS_OB_PETUGAS_PENDATAAN_LAPANGAN_SENSUS'],
            ['value' => 4694000]
        );
        Setting::updateOrCreate(
            ['key' => 'STANDAR_BIAYA_MASUKAN_LAINNYA_NON_PNS_OB_PETUGAS_PENDATAAN_LAPANGAN_SURVEI'],
            ['value' => 3353000]
        );
    }

    protected function createMitra(string $idSobat = '61042001', string $nama = 'Cut Nyak Dien', int $tahun = 2026): Mitra
    {
        $mitra = Mitra::create([
            'id_sobat' => $idSobat,
            'nama_1' => $nama,
            'nik' => '6104200000000001',
        ]);

        Kemitraan::create([
            'mitra_id' => $mitra->id,
            'tahun' => $tahun,
            'status' => 'AKTIF',
        ]);

        return $mitra;
    }

    protected function createKegiatanAndHonor(string $kegId = 'KEG-DOK-1', string $honId = 'HON-DOK-1'): Honor
    {
        $kegiatan = KegiatanManmit::create([
            'id' => $kegId,
            'nama' => 'Survei Pertanian Terpadu',
            'jenis_kegiatan' => 'SURVEI',
            'tgl_mulai_pelaksanaan' => '2026-05-01',
            'tgl_akhir_pelaksanaan' => '2026-05-31',
        ]);

        return Honor::create([
            'id' => $honId,
            'kegiatan_manmit_id' => $kegiatan->id,
            'jabatan' => 'Pencacah',
            'jenis_honor' => 'Honor Pengawasan',
            'satuan_honor' => 'Dokumen',
            'harga_per_satuan' => 75000,
            'tanggal_akhir_kegiatan' => '2026-05-20',
        ]);
    }

    public function test_get_kontrak_list_returns_grouped_contract_metadata_and_print_urls(): void
    {
        $mitra = $this->createMitra();
        $honor = $this->createKegiatanAndHonor();

        // Buat alokasi
        HonorAllocationService::allocate($mitra->id, $honor->id, 10.0);

        $response = $this->withHeaders([
            'X-API-KEY' => $this->apiKey,
        ])->getJson('/api/v1/kontrak?tahun=2026&bulan=5');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('meta.total_kontrak', 1)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'surat_perjanjian_kerja_id',
                        'nomor_surat',
                        'tanggal_nomor',
                        'periode',
                        'mitra',
                        'total_akumulasi_honor',
                        'url_cetak_pdf',
                    ]
                ]
            ]);

        $this->assertStringContainsString('/cetak/kontrak', $response->json('data.0.url_cetak_pdf'));
        $this->assertStringNotContainsString('id_kegiatan_manmit', $response->json('data.0.url_cetak_pdf'));
        $this->assertStringContainsString('tahun=2026', $response->json('data.0.url_cetak_pdf'));
        $this->assertStringContainsString('bulan=5', $response->json('data.0.url_cetak_pdf'));
        $this->assertStringContainsString('mitra_id=' . $mitra->id, $response->json('data.0.url_cetak_pdf'));
    }

    public function test_get_bast_list_returns_bast_metadata_and_print_urls(): void
    {
        $mitra = $this->createMitra();
        $honor = $this->createKegiatanAndHonor();

        HonorAllocationService::allocate($mitra->id, $honor->id, 10.0);

        $response = $this->withHeaders([
            'X-API-KEY' => $this->apiKey,
        ])->getJson('/api/v1/bast?tahun=2026&bulan=5');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('meta.total_bast', 1)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'surat_bast_id',
                        'nomor_surat',
                        'tanggal_nomor',
                        'periode',
                        'mitra',
                        'honor',
                        'total_honor',
                        'url_cetak_pdf',
                    ]
                ]
            ]);

        $this->assertStringContainsString('/cetak/bast', $response->json('data.0.url_cetak_pdf'));
    }

    public function test_discovery_endpoints_kegiatan_and_mitras(): void
    {
        $this->createMitra();
        $this->createKegiatanAndHonor();

        // 1. Kegiatan lookup
        $kegResponse = $this->withHeaders([
            'X-API-KEY' => $this->apiKey,
        ])->getJson('/api/v1/kegiatan-manmit?tahun=2026');

        $kegResponse->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure(['data', 'meta']);

        // 2. Mitra lookup
        $mitraResponse = $this->withHeaders([
            'X-API-KEY' => $this->apiKey,
        ])->getJson('/api/v1/mitras?tahun=2026');

        $mitraResponse->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_mitra_endpoint_advanced_search_and_filters(): void
    {
        $mitra1 = Mitra::create([
            'id_sobat' => '61042099',
            'nama_1' => 'Agus Setiawan',
            'nik' => '6104209900000001',
            'email' => 'agus@bps.go.id',
            'no_telp' => '08123456789',
            'kecamatan_domisili' => 'Delta Pawan',
            'desa_domisili' => 'Kantor',
            'jenis_kelamin' => 'L',
            'posisi' => 'PCL',
        ]);
        Kemitraan::create([
            'mitra_id' => $mitra1->id,
            'tahun' => 2026,
            'status' => 'AKTIF',
        ]);

        $mitra2 = Mitra::create([
            'id_sobat' => '61042088',
            'nama_1' => 'Siti Aminah',
            'nik' => '6104208800000002',
            'email' => 'siti@bps.go.id',
            'no_telp' => '08987654321',
            'kecamatan_domisili' => 'Benua Kayong',
            'desa_domisili' => 'Tuan-tuan',
            'jenis_kelamin' => 'P',
            'posisi' => 'PML',
        ]);
        Kemitraan::create([
            'mitra_id' => $mitra2->id,
            'tahun' => 2026,
            'status' => 'AKTIF',
        ]);

        // 1. Search by email
        $resSearch = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->getJson('/api/v1/mitras?tahun=2026&q=agus@bps.go.id');
        $resSearch->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nama', 'Agus Setiawan');

        // 2. Filter by kecamatan
        $resKec = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->getJson('/api/v1/mitras?tahun=2026&kecamatan=Delta+Pawan');
        $resKec->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nama', 'Agus Setiawan');

        // 3. Filter by jenis kelamin & posisi
        $resJk = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->getJson('/api/v1/mitras?tahun=2026&jenis_kelamin=P&posisi=PML');
        $resJk->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nama', 'Siti Aminah');

        // 4. Non-compact response returns enriched fields
        $resFull = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->getJson('/api/v1/mitras?tahun=2026&q=Siti');
        $resFull->assertStatus(200)
            ->assertJsonPath('data.0.kecamatan', 'Benua Kayong')
            ->assertJsonPath('data.0.desa', 'Tuan-tuan')
            ->assertJsonPath('data.0.posisi', 'PML');
    }
}
