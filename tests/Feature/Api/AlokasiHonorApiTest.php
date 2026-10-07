<?php

namespace Tests\Feature\Api;

use App\Models\AlokasiHonor;
use App\Models\Honor;
use App\Models\KegiatanManmit;
use App\Models\Kemitraan;
use App\Models\Mitra;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlokasiHonorApiTest extends TestCase
{
    use RefreshDatabase;

    protected string $apiKey = 'test-secret-api-key-12345';

    protected function setUp(): void
    {
        parent::setUp();

        config(['sipedas.api_key' => $this->apiKey]);

        // Setup SBML settings
        Setting::updateOrCreate(
            ['key' => 'STANDAR_BIAYA_MASUKAN_LAINNYA_NON_PNS_OB_PETUGAS_PENDATAAN_LAPANGAN_SENSUS'],
            ['value' => 4694000]
        );
        Setting::updateOrCreate(
            ['key' => 'STANDAR_BIAYA_MASUKAN_LAINNYA_NON_PNS_OB_PETUGAS_PENDATAAN_LAPANGAN_SURVEI'],
            ['value' => 3353000]
        );
    }

    protected function createMitra(string $idSobat = '61041001', string $nama = 'Ahmad Dahlan', int $tahun = 2026): Mitra
    {
        $mitra = Mitra::create([
            'id_sobat' => $idSobat,
            'nama_1' => $nama,
            'nik' => '6104100000000001',
        ]);

        Kemitraan::create([
            'mitra_id' => $mitra->id,
            'tahun' => $tahun,
            'status' => 'AKTIF',
        ]);

        return $mitra;
    }

    protected function createKegiatanAndHonor(string $kegId = 'KEG-API-1', string $honId = 'HON-API-1', float $harga = 50000): Honor
    {
        $kegiatan = KegiatanManmit::create([
            'id' => $kegId,
            'nama' => 'Kegiatan API ' . $kegId,
            'jenis_kegiatan' => 'SURVEI',
            'tgl_mulai_pelaksanaan' => '2026-05-01',
            'tgl_akhir_pelaksanaan' => '2026-05-31',
        ]);

        return Honor::create([
            'id' => $honId,
            'kegiatan_manmit_id' => $kegiatan->id,
            'jabatan' => 'Pencacah',
            'jenis_honor' => 'Honor Pendataan',
            'satuan_honor' => 'Dokumen',
            'harga_per_satuan' => $harga,
            'tanggal_akhir_kegiatan' => '2026-05-20',
        ]);
    }

    public function test_request_without_api_key_returns_401(): void
    {
        $response = $this->getJson('/api/v1/alokasi');

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
            ]);
    }

    public function test_request_with_invalid_api_key_returns_401(): void
    {
        $response = $this->withHeaders([
            'X-API-KEY' => 'wrong-key',
        ])->getJson('/api/v1/alokasi');

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'message' => 'Unauthorized: API Key tidak valid.',
            ]);
    }

    public function test_request_with_valid_x_api_key_header_is_authorized(): void
    {
        $response = $this->withHeaders([
            'X-API-KEY' => $this->apiKey,
        ])->getJson('/api/v1/alokasi');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);
    }

    public function test_request_with_valid_sanctum_bearer_token_is_authorized(): void
    {
        $user = User::factory()->create([
            'email' => 'agent@bps.go.id',
        ]);
        $token = $user->createToken('coding-agent-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/alokasi');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);
    }

    public function test_check_eligibility_dry_run_endpoint(): void
    {
        $mitra = $this->createMitra();
        $honor = $this->createKegiatanAndHonor();

        $response = $this->withHeaders([
            'X-API-KEY' => $this->apiKey,
        ])->postJson('/api/v1/alokasi/check', [
            'mitra_id' => $mitra->id,
            'honor_id' => $honor->id,
            'target' => 10,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.eligible', true)
            ->assertJsonPath('data.kalkulasi.total_honor', 500000);

        // Pastikan tidak ada data yang tersimpan di DB
        $this->assertEquals(0, AlokasiHonor::count());
    }

    public function test_store_single_alokasi_creates_records_and_documents(): void
    {
        $mitra = $this->createMitra();
        $honor = $this->createKegiatanAndHonor();

        $response = $this->withHeaders([
            'X-API-KEY' => $this->apiKey,
        ])->postJson('/api/v1/alokasi', [
            'mitra_id' => $mitra->id,
            'honor_id' => $honor->id,
            'target' => 15,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.total_honor', 750000);

        $this->assertEquals(1, AlokasiHonor::count());

        $alokasi = AlokasiHonor::first();
        $this->assertNotNull($alokasi->surat_perjanjian_kerja_id);
        $this->assertNotNull($alokasi->surat_bast_id);

        // Response contains printable URL metadata
        $this->assertNotNull($response->json('data.kontrak.url_cetak'));
        $this->assertNotNull($response->json('data.bast.url_cetak'));
    }

    public function test_store_alokasi_validation_failure_when_sbml_exceeded(): void
    {
        $mitra = $this->createMitra();
        $honor = $this->createKegiatanAndHonor('KEG-EXP', 'HON-EXP', 2000000);

        // 3 target x Rp 2.000.000 = Rp 6.000.000 (exceeds Rp 3.353.000 limit)
        $response = $this->withHeaders([
            'X-API-KEY' => $this->apiKey,
        ])->postJson('/api/v1/alokasi', [
            'mitra_id' => $mitra->id,
            'honor_id' => $honor->id,
            'target' => 3,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('status', 'error')
            ->assertJsonStructure(['errors' => ['target']]);

        $this->assertEquals(0, AlokasiHonor::count());
    }

    public function test_store_batch_alokasi(): void
    {
        $mitra1 = $this->createMitra('61041002', 'Mitra Satu');
        $mitra2 = $this->createMitra('61041003', 'Mitra Dua');
        $honor = $this->createKegiatanAndHonor('KEG-BATCH', 'HON-BATCH', 50000);

        $response = $this->withHeaders([
            'X-API-KEY' => $this->apiKey,
        ])->postJson('/api/v1/alokasi', [
            'allocations' => [
                ['mitra_id' => $mitra1->id, 'honor_id' => $honor->id, 'target' => 10],
                ['id_sobat' => $mitra2->id_sobat, 'honor_id' => $honor->id, 'target' => 12],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.success_count', 2)
            ->assertJsonPath('data.failed_count', 0);

        $this->assertEquals(2, AlokasiHonor::count());
    }

    public function test_get_alokasi_detail_and_destroy(): void
    {
        $mitra = $this->createMitra();
        $honor = $this->createKegiatanAndHonor();

        $createResponse = $this->withHeaders([
            'X-API-KEY' => $this->apiKey,
        ])->postJson('/api/v1/alokasi', [
            'mitra_id' => $mitra->id,
            'honor_id' => $honor->id,
            'target' => 10,
        ]);

        $alokasiId = $createResponse->json('data.id');

        // GET Detail
        $detailResponse = $this->withHeaders([
            'X-API-KEY' => $this->apiKey,
        ])->getJson("/api/v1/alokasi/{$alokasiId}");

        $detailResponse->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.id', $alokasiId);

        // DELETE
        $deleteResponse = $this->withHeaders([
            'X-API-KEY' => $this->apiKey,
        ])->deleteJson("/api/v1/alokasi/{$alokasiId}");

        $deleteResponse->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertEquals(0, AlokasiHonor::count());
    }

    public function test_get_alokasi_search_by_keyword_without_sql_error(): void
    {
        $mitra = $this->createMitra('61041009', 'Siti Susenas', 2026);
        $honor = $this->createKegiatanAndHonor('KEG-SUSENAS-1', 'HON-SUSENAS-1', 50000);

        $this->withHeaders([
            'X-API-KEY' => $this->apiKey,
        ])->postJson('/api/v1/alokasi', [
            'mitra_id' => $mitra->id,
            'honor_id' => $honor->id,
            'target' => 5,
        ])->assertStatus(201);

        // Pencarian dengan keyword q=SUSENAS (sebelumnya memicu 500 karena Unknown column 'nomor_surat_tugas')
        $response = $this->withHeaders([
            'X-API-KEY' => $this->apiKey,
        ])->getJson('/api/v1/alokasi?q=SUSENAS');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');
        $this->assertCount(1, $response->json('data'));

        // Pastikan dokumen kontrak dan BAST juga tidak melempar error saat di-query dengan q
        $resKontrak = $this->withHeaders([
            'X-API-KEY' => $this->apiKey,
        ])->getJson('/api/v1/kontrak?q=SUSENAS');
        $resKontrak->assertStatus(200);

        $resBast = $this->withHeaders([
            'X-API-KEY' => $this->apiKey,
        ])->getJson('/api/v1/bast?q=SUSENAS');
        $resBast->assertStatus(200);
    }
}
