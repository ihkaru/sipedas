<?php

namespace Tests\Feature\Api;

use App\Models\ApiAuditLog;
use App\Models\Kemitraan;
use App\Models\Mitra;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MitraUpdateApiTest extends TestCase
{
    use RefreshDatabase;

    protected string $apiKey = 'test-mitra-update-key';
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
            'name' => 'Agent Dokter V',
        ]);
    }

    public function test_get_mitra_detail_by_id_and_id_sobat(): void
    {
        $mitra = Mitra::create([
            'id_sobat' => '61041999',
            'nama_1' => 'Budi Santoso',
            'nik' => '6104101111110001',
            'no_telp' => '0534123456',
            'nomor_wa' => '6281255554444',
            'email' => 'budi@example.com',
            'alamat_detail' => 'Jl. Merdeka No. 12',
        ]);

        Kemitraan::create([
            'mitra_id' => $mitra->id,
            'tahun' => 2026,
            'status' => 'AKTIF',
        ]);

        // 1. Fetch by primary database ID
        $resById = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->getJson("/api/v1/mitras/{$mitra->id}");

        $resById->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.id', $mitra->id)
            ->assertJsonPath('data.nama', 'Budi Santoso')
            ->assertJsonPath('data.nomor_wa', '6281255554444')
            ->assertJsonPath('data.whatsapp_target', '6281255554444')
            ->assertJsonPath('data.whatsapp_url', 'https://wa.me/6281255554444')
            ->assertJsonPath('data.kemitraan_terkini.status', 'AKTIF');

        // 2. Fetch by id_sobat
        $resBySobat = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->getJson("/api/v1/mitras/61041999");

        $resBySobat->assertStatus(200)
            ->assertJsonPath('data.id', $mitra->id)
            ->assertJsonPath('data.id_sobat', '61041999');

        // 3. Non-existent returns 404
        $resNotFound = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->getJson("/api/v1/mitras/9999999999");
        $resNotFound->assertStatus(404);
    }

    public function test_update_mitra_nomor_wa_normalizes_and_preserves_original_no_telp(): void
    {
        $mitra = Mitra::create([
            'id_sobat' => '61042001',
            'nama_1' => 'Siti Aisyah',
            'nik' => '6104102222220002',
            'no_telp' => '0534987654', // original imported from SOBAT
            'nomor_wa' => null,
            'email' => 'siti@example.com',
        ]);

        // Update via PATCH dengan format WA lokal berkode 08
        $response = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->patchJson("/api/v1/mitras/{$mitra->id}", [
                'nomor_wa' => '0812-5830-6655',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.nomor_wa', '6281258306655')
            ->assertJsonPath('data.whatsapp_target', '6281258306655')
            ->assertJsonPath('data.whatsapp_url', 'https://wa.me/6281258306655')
            ->assertJsonPath('data.no_telp', '0534987654'); // no_telp asli tetap aman

        // Verifikasi di database
        $fresh = $mitra->fresh();
        $this->assertEquals('6281258306655', $fresh->nomor_wa);
        $this->assertEquals('0534987654', $fresh->no_telp);
    }

    public function test_update_mitra_via_id_sobat_and_multi_fields(): void
    {
        $mitra = Mitra::create([
            'id_sobat' => '61043003',
            'nama_1' => 'Ahmad Dahlan',
            'nik' => '6104103333330003',
            'no_telp' => '0811000000',
        ]);

        Kemitraan::create([
            'mitra_id' => $mitra->id,
            'tahun' => 2026,
            'status' => 'TIDAK_AKTIF',
        ]);

        // Panggil menggunakan ID Sobat dan metode PUT
        $response = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->putJson('/api/v1/mitras/61043003', [
                'no_wa' => '6289988776655',
                'email' => 'ahmad.dahlan@bps.go.id',
                'alamat_detail' => 'Komplek Permata Indah Blok B3',
                'catatan' => 'Mitra terlatih survei ekonomi',
                'status_kemitraan' => 'AKTIF',
                'tahun' => 2026,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.nomor_wa', '6289988776655')
            ->assertJsonPath('data.email', 'ahmad.dahlan@bps.go.id')
            ->assertJsonPath('data.alamat_detail', 'Komplek Permata Indah Blok B3')
            ->assertJsonPath('data.catatan', 'Mitra terlatih survei ekonomi')
            ->assertJsonPath('data.kemitraan.status', 'AKTIF');

        // Verifikasi relasi kemitraans berubah jadi AKTIF
        $kemitraan = Kemitraan::where('mitra_id', $mitra->id)->where('tahun', 2026)->first();
        $this->assertNotNull($kemitraan);
        $this->assertEquals('AKTIF', $kemitraan->status);
    }

    public function test_update_mitra_creates_audit_log_and_supports_reversible_rollback(): void
    {
        $mitra = Mitra::create([
            'id_sobat' => '61044004',
            'nama_1' => 'Dewi Sartika',
            'nik' => '6104104444440004',
            'nomor_wa' => '6281111111111',
            'email' => 'dewi.lama@example.com',
            'alamat_detail' => 'Alamat Lama',
        ]);

        Kemitraan::create([
            'mitra_id' => $mitra->id,
            'tahun' => 2026,
            'status' => 'AKTIF',
        ]);

        // 1. Lakukan pembaruan
        $updateRes = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->patchJson("/api/v1/mitras/{$mitra->id}", [
                'nomor_wa' => '6289999999999',
                'email' => 'dewi.baru@example.com',
                'alamat_detail' => 'Alamat Baru',
                'status_kemitraan' => 'BLACKLISTED',
                'tahun' => 2026,
            ]);
        $updateRes->assertStatus(200);

        // 2. Verifikasi Audit Log tercatat
        $auditLog = ApiAuditLog::where('action', 'UPDATE_MITRA')
            ->where('target_id', $mitra->id)
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertTrue($auditLog->is_reversible);
        $this->assertEquals('6281111111111', $auditLog->state_before['nomor_wa']);
        $this->assertEquals('dewi.lama@example.com', $auditLog->state_before['email']);
        $this->assertEquals('AKTIF', $auditLog->state_before['kemitraan_status']);
        $this->assertEquals('6289999999999', $auditLog->state_after['nomor_wa']);

        // 3. Lakukan Rollback via endpoint rollback audit-log
        $rollbackRes = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->postJson("/api/v1/audit-logs/{$auditLog->id}/rollback", [
                'reason' => 'Salah update nomor WA dan status mitra',
            ]);

        $rollbackRes->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.is_rolled_back', true);

        // 4. Verifikasi data Mitra & Kemitraan kembali ke kondisi semula!
        $fresh = $mitra->fresh();
        $this->assertEquals('6281111111111', $fresh->nomor_wa);
        $this->assertEquals('dewi.lama@example.com', $fresh->email);
        $this->assertEquals('Alamat Lama', $fresh->alamat_detail);

        $freshKemitraan = Kemitraan::where('mitra_id', $mitra->id)->where('tahun', 2026)->first();
        $this->assertEquals('AKTIF', $freshKemitraan->status);
    }

    public function test_update_mitra_validates_payload(): void
    {
        $mitra = Mitra::create([
            'id_sobat' => '61045005',
            'nama_1' => 'Bambang',
            'nik' => '6104105555550005',
        ]);

        $response = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->patchJson("/api/v1/mitras/{$mitra->id}", [
                'email' => 'bukan-email-valid',
                'status_kemitraan' => 'STATUS_SALAH',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'status_kemitraan']);
    }

    public function test_get_mitras_search_matches_nomor_wa(): void
    {
        $mitra = Mitra::create([
            'id_sobat' => '61046006',
            'nama_1' => 'Kartini',
            'nik' => '6104106666660006',
            'nomor_wa' => '6287711223344',
        ]);

        Kemitraan::create([
            'mitra_id' => $mitra->id,
            'tahun' => 2026,
            'status' => 'AKTIF',
        ]);

        $response = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->getJson('/api/v1/mitras?q=87711223344');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.id', $mitra->id)
            ->assertJsonPath('data.0.nama', 'Kartini')
            ->assertJsonPath('data.0.nomor_wa', '6287711223344');
    }
}
