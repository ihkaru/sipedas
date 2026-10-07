<?php

namespace Tests\Feature\Api;

use App\Models\AlokasiHonor;
use App\Models\ApiAuditLog;
use App\Models\ApiKey;
use App\Models\Honor;
use App\Models\KegiatanManmit;
use App\Models\Kemitraan;
use App\Models\Mitra;
use App\Models\NomorSurat;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogApiTest extends TestCase
{
    use RefreshDatabase;

    protected string $apiKeyToken = 'test-audit-api-key-999';
    protected ApiKey $apiKeyModel;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create user & API Key model
        $this->user = User::factory()->create([
            'name' => 'Agent Tester',
            'email' => 'agent@bps.go.id',
        ]);

        $this->apiKeyModel = ApiKey::create([
            'user_id' => $this->user->id,
            'name' => 'Automated Test Key',
            'key' => $this->apiKeyToken,
            'is_active' => true,
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

    protected function createMitra(string $idSobat = '61041010', string $nama = 'Budi Santoso'): Mitra
    {
        $mitra = Mitra::create([
            'id_sobat' => $idSobat,
            'nama_1' => $nama,
            'nik' => '6104100000000010',
        ]);

        Kemitraan::create([
            'mitra_id' => $mitra->id,
            'tahun' => 2026,
            'status' => 'AKTIF',
        ]);

        return $mitra;
    }

    protected function createKegiatanAndHonor(string $kegId = 'KEG-AUDIT-1', string $honId = 'HON-AUDIT-1', float $harga = 75000): Honor
    {
        $kegiatan = KegiatanManmit::create([
            'id' => $kegId,
            'nama' => 'Kegiatan Audit ' . $kegId,
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

    public function test_dynamic_api_key_authenticates_and_updates_last_used(): void
    {
        $this->assertNull($this->apiKeyModel->last_used_at);

        $response = $this->withHeaders([
            'X-API-KEY' => $this->apiKeyToken,
        ])->getJson('/api/v1/audit-logs');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->apiKeyModel->refresh();
        $this->assertNotNull($this->apiKeyModel->last_used_at);
    }

    public function test_expired_api_key_is_rejected(): void
    {
        $this->apiKeyModel->update([
            'expires_at' => now()->subDay(),
        ]);

        $response = $this->withHeaders([
            'X-API-KEY' => $this->apiKeyToken,
        ])->getJson('/api/v1/audit-logs');

        $response->assertStatus(401)
            ->assertJsonPath('message', 'Unauthorized: API Key telah dinonaktifkan atau kedaluwarsa.');
    }

    public function test_disabled_api_key_is_rejected(): void
    {
        $this->apiKeyModel->update([
            'is_active' => false,
        ]);

        $response = $this->withHeaders([
            'X-API-KEY' => $this->apiKeyToken,
        ])->getJson('/api/v1/audit-logs');

        $response->assertStatus(401)
            ->assertJsonPath('message', 'Unauthorized: API Key telah dinonaktifkan atau kedaluwarsa.');
    }

    public function test_allocate_honor_creates_audit_log_and_rollback_reverts_state(): void
    {
        $mitra = $this->createMitra();
        $honor = $this->createKegiatanAndHonor();

        // 1. Buat alokasi honor via REST API
        $createResponse = $this->withHeaders([
            'X-API-KEY' => $this->apiKeyToken,
        ])->postJson('/api/v1/alokasi', [
            'mitra_id' => $mitra->id,
            'honor_id' => $honor->id,
            'target' => 10,
        ]);

        $createResponse->assertStatus(201);
        $alokasiId = $createResponse->json('data.id');

        $this->assertEquals(1, AlokasiHonor::count());
        $this->assertGreaterThan(0, NomorSurat::count());

        // 2. Verifikasi audit log tercatat
        $auditLog = ApiAuditLog::where('action', 'ALLOCATE_HONOR')
            ->where('target_id', $alokasiId)
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertTrue($auditLog->is_reversible);
        $this->assertFalse($auditLog->is_rolled_back);
        $this->assertEquals($this->user->id, $auditLog->user_id);
        $this->assertEquals($this->apiKeyModel->id, $auditLog->api_key_id);

        // 3. Eksekusi Rollback via REST API endpoint
        $rollbackResponse = $this->withHeaders([
            'X-API-KEY' => $this->apiKeyToken,
        ])->postJson("/api/v1/audit-logs/{$auditLog->id}/rollback", [
            'reason' => 'Testing programmatic rollback via REST API',
        ]);

        $rollbackResponse->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.is_rolled_back', true);

        // 4. Pastikan alokasi honor dan nomor surat telah dihapus
        $this->assertEquals(0, AlokasiHonor::count());
        $this->assertEquals(0, NomorSurat::count());

        $auditLog->refresh();
        $this->assertTrue($auditLog->is_rolled_back);
        $this->assertNotNull($auditLog->rolled_back_at);
        $this->assertEquals('Testing programmatic rollback via REST API', $auditLog->rollback_reason);

        // 5. Coba rollback kedua kali (harus gagal dengan 422)
        $secondRollback = $this->withHeaders([
            'X-API-KEY' => $this->apiKeyToken,
        ])->postJson("/api/v1/audit-logs/{$auditLog->id}/rollback");

        $secondRollback->assertStatus(422)
            ->assertJsonPath('status', 'error');
    }

    public function test_batch_allocate_honor_and_rollback(): void
    {
        $mitra1 = $this->createMitra('61041011', 'Mitra Batch 1');
        $mitra2 = $this->createMitra('61041012', 'Mitra Batch 2');
        $honor = $this->createKegiatanAndHonor('KEG-BATCH-AUDIT', 'HON-BATCH-AUDIT');

        $batchResponse = $this->withHeaders([
            'X-API-KEY' => $this->apiKeyToken,
        ])->postJson('/api/v1/alokasi', [
            'allocations' => [
                ['mitra_id' => $mitra1->id, 'honor_id' => $honor->id, 'target' => 5],
                ['mitra_id' => $mitra2->id, 'honor_id' => $honor->id, 'target' => 8],
            ],
        ]);

        $batchResponse->assertStatus(201);
        $this->assertEquals(2, AlokasiHonor::count());

        $auditLog = ApiAuditLog::where('action', 'BATCH_ALLOCATE')->first();
        $this->assertNotNull($auditLog);
        $this->assertTrue($auditLog->is_reversible);

        // Rollback batch
        $rollbackResponse = $this->withHeaders([
            'X-API-KEY' => $this->apiKeyToken,
        ])->postJson("/api/v1/audit-logs/{$auditLog->id}/rollback");

        $rollbackResponse->assertStatus(200);
        $this->assertEquals(0, AlokasiHonor::count());
    }

    public function test_delete_honor_audit_log_and_rollback(): void
    {
        $mitra = $this->createMitra();
        $honor = $this->createKegiatanAndHonor();

        $createResponse = $this->withHeaders([
            'X-API-KEY' => $this->apiKeyToken,
        ])->postJson('/api/v1/alokasi', [
            'mitra_id' => $mitra->id,
            'honor_id' => $honor->id,
            'target' => 10,
        ]);

        $alokasiId = $createResponse->json('data.id');
        $this->assertEquals(1, AlokasiHonor::count());

        // Hapus alokasi
        $deleteResponse = $this->withHeaders([
            'X-API-KEY' => $this->apiKeyToken,
        ])->deleteJson("/api/v1/alokasi/{$alokasiId}");

        $deleteResponse->assertStatus(200);
        $this->assertEquals(0, AlokasiHonor::count());

        // Verifikasi audit log DELETE_ALLOCATION
        $deleteAuditLog = ApiAuditLog::where('action', 'DELETE_ALLOCATION')
            ->where('target_id', $alokasiId)
            ->first();

        $this->assertNotNull($deleteAuditLog);
        $this->assertTrue($deleteAuditLog->is_reversible);

        // Rollback operasi delete -> mengembalikan data yang terhapus
        $rollbackResponse = $this->withHeaders([
            'X-API-KEY' => $this->apiKeyToken,
        ])->postJson("/api/v1/audit-logs/{$deleteAuditLog->id}/rollback");

        $rollbackResponse->assertStatus(200);
        $this->assertEquals(1, AlokasiHonor::count());

        $restored = AlokasiHonor::first();
        $this->assertEquals($mitra->id, $restored->mitra_id);
        $this->assertEquals($honor->id, $restored->honor_id);
    }
}
