<?php

namespace Tests\Feature\Api;

use App\Models\AlokasiHonor;
use App\Models\ApiAuditLog;
use App\Models\Honor;
use App\Models\KegiatanManmit;
use App\Models\Mitra;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KegiatanManmitCreationApiTest extends TestCase
{
    use RefreshDatabase;

    protected string $apiKey = 'test-kegiatan-creation-key';
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

    public function test_create_kegiatan_manmit_with_explicit_id_and_nested_honors(): void
    {
        $payload = [
            'id' => 'DPP26',
            'nama' => '(DPP26) Updating Direktori Perusahaan Pertanian 2026',
            'tgl_mulai_pelaksanaan' => '2026-07-01',
            'tgl_akhir_pelaksanaan' => '2026-09-30',
            'jenis_kegiatan' => 'SURVEI',
            'honors' => [
                [
                    'jabatan' => 'PPL',
                    'jenis_honor' => 'PENDATAAN',
                    'satuan_honor' => 'DOKUMEN',
                    'harga_per_satuan' => 53000,
                    'tanggal_akhir_kegiatan' => '2026-09-30',
                ],
            ],
        ];

        $res = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->postJson('/api/v1/kegiatan-manmit', $payload);

        $res->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.id', 'DPP26')
            ->assertJsonPath('data.honors_count', 1)
            ->assertJsonPath('data.honors.0.id', 'DPP26-PPL-PENDATAAN')
            ->assertJsonPath('data.honors.0.harga_per_satuan', 53000);

        $this->assertDatabaseHas('kegiatan_manmits', [
            'id' => 'DPP26',
            'nama' => '(DPP26) Updating Direktori Perusahaan Pertanian 2026',
        ]);

        $this->assertDatabaseHas('honors', [
            'id' => 'DPP26-PPL-PENDATAAN',
            'kegiatan_manmit_id' => 'DPP26',
            'harga_per_satuan' => 53000,
        ]);

        // Audit log check
        $log = ApiAuditLog::where('action', 'CREATE_KEGIATAN_MANMIT')->latest()->first();
        $this->assertNotNull($log);
        $this->assertTrue($log->is_reversible);

        // Rollback
        $rollRes = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->postJson("/api/v1/audit-logs/{$log->id}/rollback");

        $rollRes->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseMissing('kegiatan_manmits', ['id' => 'DPP26']);
        $this->assertDatabaseMissing('honors', ['id' => 'DPP26-PPL-PENDATAAN']);
    }

    public function test_create_kegiatan_manmit_with_auto_generated_id_from_parentheses(): void
    {
        $payload = [
            'nama' => '(DUTL26) Updating Direktori Usaha Pertanian Lainnya 2026',
            'tgl_mulai_pelaksanaan' => '2026-07-01',
            'tgl_akhir_pelaksanaan' => '2026-09-30',
            'jenis_kegiatan' => 'SURVEI',
        ];

        $res = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->postJson('/api/v1/kegiatan-manmit', $payload);

        $res->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.id', 'DUTL26');

        $this->assertDatabaseHas('kegiatan_manmits', ['id' => 'DUTL26']);
    }

    public function test_create_kegiatan_manmit_rejected_on_duplicate_id(): void
    {
        KegiatanManmit::create([
            'id' => 'DPP26',
            'nama' => 'Existing DPP 2026',
            'jenis_kegiatan' => 'SURVEI',
            'tgl_mulai_pelaksanaan' => '2026-07-01',
            'tgl_akhir_pelaksanaan' => '2026-09-30',
        ]);

        $res = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->postJson('/api/v1/kegiatan-manmit', [
                'id' => 'DPP26',
                'nama' => 'Duplicate DPP 2026',
                'tgl_mulai_pelaksanaan' => '2026-07-01',
                'tgl_akhir_pelaksanaan' => '2026-09-30',
            ]);

        $res->assertStatus(422)
            ->assertJsonValidationErrors('id');
    }

    public function test_delete_kegiatan_manmit_and_rollback(): void
    {
        $kegiatan = KegiatanManmit::create([
            'id' => 'SAMPLE26',
            'nama' => 'Kegiatan Sample 2026',
            'jenis_kegiatan' => 'SURVEI',
            'tgl_mulai_pelaksanaan' => '2026-01-01',
            'tgl_akhir_pelaksanaan' => '2026-01-31',
        ]);

        $honor = Honor::create([
            'id' => 'SAMPLE26-PPL-PENDATAAN',
            'kegiatan_manmit_id' => 'SAMPLE26',
            'jabatan' => 'PPL',
            'jenis_honor' => 'PENDATAAN',
            'satuan_honor' => 'DOKUMEN',
            'harga_per_satuan' => 45000,
            'tanggal_akhir_kegiatan' => '2026-01-31',
        ]);

        // Hapus
        $res = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->deleteJson("/api/v1/kegiatan-manmit/{$kegiatan->id}");

        $res->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.deleted_id', 'SAMPLE26');

        $this->assertDatabaseMissing('kegiatan_manmits', ['id' => 'SAMPLE26']);
        $this->assertDatabaseMissing('honors', ['id' => 'SAMPLE26-PPL-PENDATAAN']);

        // Rollback delete
        $log = ApiAuditLog::where('action', 'DELETE_KEGIATAN_MANMIT')->latest()->first();
        $this->assertNotNull($log);

        $rollRes = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->postJson("/api/v1/audit-logs/{$log->id}/rollback");

        $rollRes->assertStatus(200);

        $this->assertDatabaseHas('kegiatan_manmits', ['id' => 'SAMPLE26']);
        $this->assertDatabaseHas('honors', ['id' => 'SAMPLE26-PPL-PENDATAAN']);
    }

    public function test_delete_kegiatan_manmit_blocked_when_has_allocations(): void
    {
        $kegiatan = KegiatanManmit::create([
            'id' => 'ACTIVE26',
            'nama' => 'Kegiatan Aktif 2026',
            'jenis_kegiatan' => 'SURVEI',
            'tgl_mulai_pelaksanaan' => '2026-01-01',
            'tgl_akhir_pelaksanaan' => '2026-01-31',
        ]);

        $honor = Honor::create([
            'id' => 'ACTIVE26-PPL-PENDATAAN',
            'kegiatan_manmit_id' => 'ACTIVE26',
            'jabatan' => 'PPL',
            'jenis_honor' => 'PENDATAAN',
            'satuan_honor' => 'DOKUMEN',
            'harga_per_satuan' => 45000,
            'tanggal_akhir_kegiatan' => '2026-01-31',
        ]);

        $mitra = Mitra::create([
            'id' => 8881,
            'nama_1' => 'Mitra Aktif',
            'nik' => '6104123456780001',
            'id_sobat' => '61048881',
        ]);

        AlokasiHonor::create([
            'mitra_id' => $mitra->id,
            'honor_id' => $honor->id,
            'target_per_satuan_honor' => 5,
            'total_honor' => 225000,
            'status' => 'PENDING',
            'tanggal_mulai_perjanjian' => '2026-01-01',
            'tanggal_akhir_perjanjian' => '2026-01-31',
        ]);

        $res = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->deleteJson("/api/v1/kegiatan-manmit/{$kegiatan->id}");

        $res->assertStatus(422)
            ->assertJsonPath('status', 'error');

        $this->assertDatabaseHas('kegiatan_manmits', ['id' => 'ACTIVE26']);
    }
}
