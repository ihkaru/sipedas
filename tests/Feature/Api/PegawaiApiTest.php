<?php

namespace Tests\Feature\Api;

use App\Models\ApiAuditLog;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PegawaiApiTest extends TestCase
{
    use RefreshDatabase;

    protected string $apiKey = 'test-pegawai-key';
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

    public function test_get_pegawais_with_search_and_compact_mode(): void
    {
        $pegawai = Pegawai::create([
            'nama' => 'Muhammad Ihza',
            'nip' => '199501012022031001',
            'nip9' => '199501012',
            'panggilan' => 'Ihza',
            'golongan' => 'III/a',
            'pangkat' => 'Penata Muda',
            'jabatan' => 'Pranata Komputer Ahli Pertama',
            'email' => 'ihza@bps.go.id',
            'unit_kerja' => 'BPS Kabupaten',
            'nomor_wa' => '6281255556666',
        ]);

        // 1. Search by name & compact
        $res = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->getJson('/api/v1/pegawais?q=Ihza&compact=1');

        $res->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.0.nip', '199501012022031001')
            ->assertJsonPath('data.0.nama', 'Muhammad Ihza')
            ->assertJsonPath('data.0.nomor_wa', '6281255556666');

        // 2. Search by NIP
        $resNip = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->getJson('/api/v1/pegawais?q=199501012');

        $resNip->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.0.nip', '199501012022031001');
    }

    public function test_show_pegawai_by_nip_or_nip9(): void
    {
        $pegawai = Pegawai::create([
            'nama' => 'Siti Nurhaliza',
            'nip' => '199202022019012002',
            'nip9' => '199202022',
            'panggilan' => 'Siti',
            'golongan' => 'III/b',
            'pangkat' => 'Penata Muda Tk. I',
            'jabatan' => 'Statistisi Ahli Pertama',
            'email' => 'siti@bps.go.id',
            'unit_kerja' => 'BPS Kabupaten',
            'nomor_wa' => '6281344445555',
        ]);

        // Fetch by full NIP
        $resFull = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->getJson("/api/v1/pegawais/{$pegawai->nip}");

        $resFull->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.nama', 'Siti Nurhaliza');

        // Fetch by NIP9
        $resNip9 = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->getJson("/api/v1/pegawais/{$pegawai->nip9}");

        $resNip9->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.nip', '199202022019012002');
    }

    public function test_store_pegawai_normalizes_wa_and_supports_rollback(): void
    {
        $payload = [
            'nama' => 'Ahmad Fauzan',
            'nip' => '199803032023011003',
            'nip9' => '199803031',
            'panggilan' => 'Fauzan',
            'golongan' => 'III/a',
            'pangkat' => 'Penata Muda',
            'jabatan' => 'Pranata Komputer',
            'email' => 'fauzan@bps.go.id',
            'unit_kerja' => 'BPS Kabupaten',
            'nomor_wa' => '081299887766', // Format 08 harus dinormalisasi ke 628
        ];

        $res = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->postJson('/api/v1/pegawais', $payload);

        $res->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.nip', '199803032023011003')
            ->assertJsonPath('data.nomor_wa', '6281299887766');

        $this->assertDatabaseHas('pegawais', [
            'nip' => '199803032023011003',
            'nomor_wa' => '6281299887766',
        ]);

        // Verifikasi audit log
        $auditLog = ApiAuditLog::where('action', 'CREATE_PEGAWAI')->latest()->first();
        $this->assertNotNull($auditLog);
        $this->assertEquals('199803032023011003', $auditLog->state_after['nip']);
        $this->assertTrue($auditLog->is_reversible);

        // Rollback pembuatan pegawai (menghapus record)
        $rollRes = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->postJson("/api/v1/audit-logs/{$auditLog->id}/rollback");

        $rollRes->assertStatus(200);

        $this->assertDatabaseMissing('pegawais', [
            'nip' => '199803032023011003',
        ]);
    }

    public function test_update_pegawai_and_supports_rollback(): void
    {
        $pegawai = Pegawai::create([
            'nama' => 'Rina Wijaya',
            'nip' => '199404042021022004',
            'nip9' => '199404042',
            'panggilan' => 'Rina',
            'golongan' => 'III/a',
            'pangkat' => 'Penata Muda',
            'jabatan' => 'Staf Umum',
            'email' => 'rina@bps.go.id',
            'unit_kerja' => 'Subbagian Umum',
            'nomor_wa' => '628111222333',
        ]);

        $res = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->patchJson("/api/v1/pegawais/{$pegawai->nip}", [
                'jabatan' => 'Kepala Subbagian Umum',
                'nomor_wa' => '081234567890',
            ]);

        $res->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.jabatan', 'Kepala Subbagian Umum')
            ->assertJsonPath('data.nomor_wa', '6281234567890');

        $pegawai->refresh();
        $this->assertEquals('Kepala Subbagian Umum', $pegawai->jabatan);
        $this->assertEquals('6281234567890', $pegawai->nomor_wa);

        // Verifikasi audit log
        $auditLog = ApiAuditLog::where('action', 'UPDATE_PEGAWAI')->latest()->first();
        $this->assertNotNull($auditLog);
        $this->assertEquals('Staf Umum', $auditLog->state_before['jabatan']);
        $this->assertEquals('Kepala Subbagian Umum', $auditLog->state_after['jabatan']);

        // Rollback update pegawai
        $rollRes = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->postJson("/api/v1/audit-logs/{$auditLog->id}/rollback");

        $rollRes->assertStatus(200);

        $pegawai->refresh();
        $this->assertEquals('Staf Umum', $pegawai->jabatan);
        $this->assertEquals('628111222333', $pegawai->nomor_wa);
    }
}
