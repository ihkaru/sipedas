<?php

namespace Tests\Feature\Api;

use App\Models\AlokasiHonor;
use App\Models\Honor;
use App\Models\KegiatanManmit;
use App\Models\Kemitraan;
use App\Models\Mitra;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MitraAvailabilityAndBatchCheckApiTest extends TestCase
{
    use RefreshDatabase;

    protected string $apiKey = 'test-secret-api-key-mitra-avail';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'dokter_v.api_key' => $this->apiKey,
        ]);

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

    protected function createMitra(string $idSobat, string $nama, int $tahun = 2026, string $status = 'AKTIF'): Mitra
    {
        $mitra = Mitra::create([
            'id_sobat' => $idSobat,
            'nama_1' => $nama,
            'nik' => '6104'.str_pad($idSobat, 12, '0', STR_PAD_LEFT),
        ]);

        Kemitraan::create([
            'mitra_id' => $mitra->id,
            'tahun' => $tahun,
            'status' => $status,
        ]);

        return $mitra;
    }

    protected function createHonor(string $kegId, string $honId, float $harga, string $jenisKegiatan = 'SURVEI', string $tglAkhir = '2026-05-25'): Honor
    {
        $kegiatan = KegiatanManmit::create([
            'id' => $kegId,
            'nama' => 'Kegiatan '.$kegId,
            'jenis_kegiatan' => $jenisKegiatan,
            'tanggal_mulai' => '2026-05-01',
            'tanggal_berakhir' => $tglAkhir,
        ]);

        return Honor::create([
            'id' => $honId,
            'kegiatan_manmit_id' => $kegiatan->id,
            'jabatan' => 'Pencacah Lapangan',
            'jenis_honor' => 'Honor Output',
            'satuan_honor' => 'Dokumen',
            'harga_per_satuan' => $harga,
            'tanggal_akhir_kegiatan' => $tglAkhir,
        ]);
    }

    public function test_batch_candidate_check_eligibility(): void
    {
        $honor = $this->createHonor('KEG-BATCH-1', 'HON-BATCH-1', 100000);

        // Mitra 1: Aktif & Pagu penuh
        $mitra1 = $this->createMitra('SOBAT001', 'Mitra Siap');

        // Mitra 2: Tidak aktif di tahun 2026
        $mitra2 = $this->createMitra('SOBAT002', 'Mitra Tidak Aktif', 2026, 'TIDAK_AKTIF');

        // Mitra 3: Aktif, tapi pagu sisa tinggal sedikit (sudah dialokasikan 3.300.000)
        $mitra3 = $this->createMitra('SOBAT003', 'Mitra Pagu Mepet');
        AlokasiHonor::create([
            'mitra_id' => $mitra3->id,
            'honor_id' => $honor->id,
            'target_per_satuan_honor' => 33,
            'total_honor' => 3300000,
            'tanggal_mulai_perjanjian' => '2026-05-01',
            'tanggal_akhir_perjanjian' => '2026-05-31',
        ]);

        // Uji cek batch dengan target 2 (beban = 200.000)
        // Mitra 1: sisa 3.353.000 >= 200.000 -> ELIGIBLE
        // Mitra 2: tidak aktif -> INELIGIBLE
        // Mitra 3: sisa 53.000 < 200.000 -> INELIGIBLE
        $response = $this->withHeader('X-API-KEY', $this->apiKey)
            ->postJson('/api/v1/alokasi/check', [
                'honor_id' => $honor->id,
                'target' => 2,
                'candidates' => [
                    $mitra1->id,
                    $mitra2->id_sobat,
                    $mitra3->id,
                ],
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'total_checked' => 3,
                    'eligible_count' => 1,
                    'ineligible_count' => 2,
                ],
            ]);

        $candidates = $response->json('data.candidates');
        $this->assertTrue($candidates[0]['eligible']);
        $this->assertEquals($mitra1->id, $candidates[0]['mitra']['id']);

        $this->assertFalse($candidates[1]['eligible']);
        $this->assertStringContainsString('tidak memiliki status kemitraan aktif', $candidates[1]['message']);

        $this->assertFalse($candidates[2]['eligible']);
        $this->assertStringContainsString('melebihi limit', $candidates[2]['message']);
    }

    public function test_get_mitras_with_honor_workload_filter_and_ready_only(): void
    {
        $honor = $this->createHonor('KEG-WORKLOAD-1', 'HON-WORKLOAD-1', 250000, 'SURVEI', '2026-06-20');

        $mitraA = $this->createMitra('SOBAT_A', 'Kandidat Siap');
        $mitraB = $this->createMitra('SOBAT_B', 'Kandidat Pagu Kurang');

        // Mitra B dialokasikan hingga batas mendekati habis di bulan Juni 2026
        AlokasiHonor::create([
            'mitra_id' => $mitraB->id,
            'honor_id' => $honor->id,
            'target_per_satuan_honor' => 13,
            'total_honor' => 3250000,
            'tanggal_mulai_perjanjian' => '2026-06-01',
            'tanggal_akhir_perjanjian' => '2026-06-30',
        ]);

        // Cek tanpa ready_only: kedua mitra harus muncul dengan atribut eligibility masing-masing
        $resAll = $this->withHeader('X-API-KEY', $this->apiKey)
            ->getJson("/api/v1/mitras?honor_id={$honor->id}&target=2");

        $resAll->assertStatus(200);
        $dataAll = collect($resAll->json('data'));
        $this->assertCount(2, $dataAll);

        $mitraARow = $dataAll->firstWhere('id', $mitraA->id);
        $this->assertTrue($mitraARow['eligibility']['eligible']);
        $this->assertEquals(500000, $mitraARow['eligibility']['beban_honor']);

        $mitraBRow = $dataAll->firstWhere('id', $mitraB->id);
        $this->assertFalse($mitraBRow['eligibility']['eligible']);

        // Cek DENGAN ready_only=1: hanya Mitra A yang harus dikembalikan
        $resReady = $this->withHeader('X-API-KEY', $this->apiKey)
            ->getJson("/api/v1/mitras?honor_id={$honor->id}&target=2&ready_only=1");

        $resReady->assertStatus(200);
        $dataReady = collect($resReady->json('data'));
        $this->assertCount(1, $dataReady);
        $this->assertEquals($mitraA->id, $dataReady->first()['id']);
        $this->assertEquals(1, $resReady->json('meta.total'));
        $this->assertEquals(500000, $resReady->json('meta.honor_query.beban_honor'));
    }

    public function test_get_mitras_with_min_sisa_filter(): void
    {
        $honor = $this->createHonor('KEG-SISA-1', 'HON-SISA-1', 50000, 'SURVEI', '2026-07-20');

        $mitra1 = $this->createMitra('SOBAT_SISA_1', 'Mitra Penuh');
        $mitra2 = $this->createMitra('SOBAT_SISA_2', 'Mitra Terpakai');

        AlokasiHonor::create([
            'mitra_id' => $mitra2->id,
            'honor_id' => $honor->id,
            'target_per_satuan_honor' => 30,
            'total_honor' => 1500000,
            'tanggal_mulai_perjanjian' => '2026-07-01',
            'tanggal_akhir_perjanjian' => '2026-07-31',
        ]);

        // Bulan Juli 2026:
        // Mitra 1: sisa survei = 3.353.000
        // Mitra 2: sisa survei = 3.353.000 - 1.500.000 = 1.853.000

        // Filter min_sisa = 2.000.000 -> hanya Mitra 1 yang lolos
        $resMinSisa = $this->withHeader('X-API-KEY', $this->apiKey)
            ->getJson('/api/v1/mitras?bulan=7&tahun=2026&min_sisa=2000000');

        $resMinSisa->assertStatus(200);
        $data = collect($resMinSisa->json('data'));
        $this->assertCount(1, $data);
        $this->assertEquals($mitra1->id, $data->first()['id']);
    }

    public function test_single_candidate_check_eligibility_backward_compatibility(): void
    {
        $honor = $this->createHonor('KEG-SINGLE-1', 'HON-SINGLE-1', 75000);
        $mitra = $this->createMitra('SOBAT_SINGLE', 'Mitra Single Check');

        $response = $this->withHeader('X-API-KEY', $this->apiKey)
            ->postJson('/api/v1/alokasi/check', [
                'honor_id' => $honor->id,
                'target' => 5,
                'mitra_id' => $mitra->id,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'eligible' => true,
                    'mitra' => [
                        'id' => $mitra->id,
                        'nama' => $mitra->nama_1,
                    ],
                    'kalkulasi' => [
                        'target' => 5,
                        'total_honor' => 375000,
                    ],
                ],
            ]);
    }
}
