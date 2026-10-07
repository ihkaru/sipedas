<?php

namespace Tests\Feature\Api;

use App\Models\AlokasiHonor;
use App\Models\ApiAuditLog;
use App\Models\ApiKey;
use App\Models\Honor;
use App\Models\KegiatanManmit;
use App\Models\Kemitraan;
use App\Models\Mitra;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TokenEfficientSearchFilterTest extends TestCase
{
    use RefreshDatabase;

    protected string $apiKey = 'test-token-efficient-key';
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'dokter_v.api_key' => $this->apiKey,
            'sipedas.api_key' => $this->apiKey,
        ]);

        $this->user = User::factory()->create([
            'email' => 'agent@bps.go.id',
        ]);

        ApiKey::create([
            'user_id' => $this->user->id,
            'name' => 'Search & Filter Test Key',
            'key' => $this->apiKey,
            'is_active' => true,
        ]);

        Setting::updateOrCreate(
            ['key' => 'STANDAR_BIAYA_MASUKAN_LAINNYA_NON_PNS_OB_PETUGAS_PENDATAAN_LAPANGAN_SENSUS'],
            ['value' => 4694000]
        );
        Setting::updateOrCreate(
            ['key' => 'STANDAR_BIAYA_MASUKAN_LAINNYA_NON_PNS_OB_PETUGAS_PENDATAAN_LAPANGAN_SURVEI'],
            ['value' => 3353000]
        );
    }

    public function test_kegiatan_manmit_search_and_filters(): void
    {
        // 1. Kegiatan A di bulan Mei dengan honor
        $kegA = KegiatanManmit::create([
            'id' => 'KEG-MAY-1',
            'nama' => 'Survei Biaya Hidup Mei',
            'jenis_kegiatan' => 'SURVEI',
            'tgl_mulai_pelaksanaan' => '2026-05-01',
            'tgl_akhir_pelaksanaan' => '2026-05-31',
        ]);
        Honor::create([
            'id' => 'HON-MAY-1',
            'kegiatan_manmit_id' => $kegA->id,
            'jabatan' => 'Pencacah',
            'jenis_honor' => 'Honor Pendataan',
            'satuan_honor' => 'Dokumen',
            'harga_per_satuan' => 50000,
            'tanggal_akhir_kegiatan' => '2026-05-20',
        ]);

        // 2. Kegiatan B di bulan Juni tanpa honor
        KegiatanManmit::create([
            'id' => 'KEG-JUN-1',
            'nama' => 'Sensus Pertanian Juni',
            'jenis_kegiatan' => 'SENSUS',
            'tgl_mulai_pelaksanaan' => '2026-06-01',
            'tgl_akhir_pelaksanaan' => '2026-06-30',
        ]);

        // Test search query `q`
        $resSearch = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->getJson('/api/v1/kegiatan-manmit?q=Biaya Hidup');
        $resSearch->assertStatus(200);
        $this->assertCount(1, $resSearch->json('data'));
        $this->assertEquals('KEG-MAY-1', $resSearch->json('data.0.id'));

        // Test filter bulan 5
        $resBulan = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->getJson('/api/v1/kegiatan-manmit?tahun=2026&bulan=5');
        $resBulan->assertStatus(200);
        $this->assertCount(1, $resBulan->json('data'));
        $this->assertEquals('KEG-MAY-1', $resBulan->json('data.0.id'));

        // Test filter has_honors=1
        $resHonors = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->getJson('/api/v1/kegiatan-manmit?has_honors=1');
        $resHonors->assertStatus(200);
        $this->assertCount(1, $resHonors->json('data'));

        // Test compact mode (token saving)
        $resCompact = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->getJson('/api/v1/kegiatan-manmit?compact=1');
        $resCompact->assertStatus(200);
        $item = $resCompact->json('data.0');
        $this->assertArrayHasKey('id', $item);
        $this->assertArrayHasKey('nama', $item);
        $this->assertArrayHasKey('honors', $item);
        $this->assertArrayNotHasKey('created_at', $item); // Pruned!
    }

    public function test_mitra_batch_ids_and_compact_mode(): void
    {
        $mitra1 = Mitra::create(['id_sobat' => '61040001', 'nama_1' => 'Mitra Satu', 'nik' => '6104000000000001']);
        Kemitraan::create(['mitra_id' => $mitra1->id, 'tahun' => 2026, 'status' => 'AKTIF']);

        $mitra2 = Mitra::create(['id_sobat' => '61040002', 'nama_1' => 'Mitra Dua', 'nik' => '6104000000000002']);
        Kemitraan::create(['mitra_id' => $mitra2->id, 'tahun' => 2026, 'status' => 'AKTIF']);

        $mitra3 = Mitra::create(['id_sobat' => '61040003', 'nama_1' => 'Mitra Tiga', 'nik' => '6104000000000003']);
        Kemitraan::create(['mitra_id' => $mitra3->id, 'tahun' => 2026, 'status' => 'TIDAK_AKTIF']);

        // Batch lookup IDs
        $resBatch = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->getJson("/api/v1/mitras?ids={$mitra1->id},{$mitra2->id}");
        $resBatch->assertStatus(200);
        $this->assertCount(2, $resBatch->json('data'));

        // Search by keyword `q`
        $resQ = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->getJson("/api/v1/mitras?q=Satu");
        $resQ->assertStatus(200);
        $this->assertCount(1, $resQ->json('data'));
        $this->assertEquals('Mitra Satu', $resQ->json('data.0.nama'));

        // Compact mode
        $resCompact = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->getJson("/api/v1/mitras?compact=1");
        $resCompact->assertStatus(200);
        $item = $resCompact->json('data.0');
        $this->assertArrayHasKey('id', $item);
        $this->assertArrayHasKey('id_sobat', $item);
        $this->assertArrayNotHasKey('created_at', $item); // Pruned!
    }

    public function test_audit_logs_only_rollbackable_and_show(): void
    {
        // Log 1: reversible & not rolled back
        $log1 = ApiAuditLog::create([
            'user_id' => $this->user->id,
            'action' => 'ALLOCATE_HONOR',
            'method' => 'POST',
            'endpoint' => '/api/v1/alokasi',
            'target_id' => 101,
            'is_reversible' => true,
            'is_rolled_back' => false,
            'state_before' => ['foo' => 'bar'],
            'state_after' => ['foo' => 'baz'],
        ]);

        // Log 2: irreversible
        ApiAuditLog::create([
            'user_id' => $this->user->id,
            'action' => 'VIEW_REPORT',
            'method' => 'GET',
            'endpoint' => '/api/v1/kegiatan-manmit',
            'is_reversible' => false,
            'is_rolled_back' => false,
        ]);

        // Log 3: already rolled back
        ApiAuditLog::create([
            'user_id' => $this->user->id,
            'action' => 'ALLOCATE_HONOR',
            'method' => 'POST',
            'endpoint' => '/api/v1/alokasi',
            'target_id' => 102,
            'is_reversible' => true,
            'is_rolled_back' => true,
            'rolled_back_at' => now(),
        ]);

        // Filter only_rollbackable=1
        $resRollbackable = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->getJson('/api/v1/audit-logs?only_rollbackable=1');
        $resRollbackable->assertStatus(200);
        $this->assertCount(1, $resRollbackable->json('data'));
        $this->assertEquals($log1->id, $resRollbackable->json('data.0.id'));

        // Compact mode: state_before should NOT be in listing
        $this->assertArrayNotHasKey('state_before', $resRollbackable->json('data.0'));

        // Detail endpoint GET /audit-logs/{id} returns full state
        $resShow = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->getJson("/api/v1/audit-logs/{$log1->id}");
        $resShow->assertStatus(200);
        $this->assertEquals(['foo' => 'bar'], $resShow->json('data.state_before'));
        $this->assertEquals(['foo' => 'baz'], $resShow->json('data.state_after'));
    }
}
