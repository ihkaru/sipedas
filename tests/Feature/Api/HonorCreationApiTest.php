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

class HonorCreationApiTest extends TestCase
{
    use RefreshDatabase;

    protected string $apiKey = 'test-honor-creation-key';
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

    public function test_create_honor_under_existing_kegiatan_and_rollback(): void
    {
        $kegiatan = KegiatanManmit::create([
            'id' => 'DPP26',
            'nama' => 'Updating DPP 2026',
            'jenis_kegiatan' => 'SURVEI',
            'tgl_mulai_pelaksanaan' => '2026-07-01',
            'tgl_akhir_pelaksanaan' => '2026-09-30',
        ]);

        $payload = [
            'kegiatan_manmit_id' => 'DPP26',
            'jabatan' => 'PPL',
            'jenis_honor' => 'PENDATAAN',
            'satuan_honor' => 'DOKUMEN',
            'harga_per_satuan' => 53000,
            'tanggal_akhir_kegiatan' => '2026-09-30',
        ];

        $res = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->postJson('/api/v1/honors', $payload);

        $res->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.id', 'DPP26-PPL-PENDATAAN')
            ->assertJsonPath('data.kegiatan_manmit_id', 'DPP26')
            ->assertJsonPath('data.harga_per_satuan', 53000)
            ->assertJsonPath('data.tanggal_akhir_kegiatan', '2026-09-30')
            ->assertJsonPath('data.tanggal_pembayaran_maksimal', '2026-10-20');

        $this->assertDatabaseHas('honors', [
            'id' => 'DPP26-PPL-PENDATAAN',
            'kegiatan_manmit_id' => 'DPP26',
            'harga_per_satuan' => 53000,
        ]);

        $log = ApiAuditLog::where('action', 'CREATE_HONOR')->latest()->first();
        $this->assertNotNull($log);
        $this->assertTrue($log->is_reversible);

        // Rollback
        $rollRes = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->postJson("/api/v1/audit-logs/{$log->id}/rollback");

        $rollRes->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseMissing('honors', ['id' => 'DPP26-PPL-PENDATAAN']);
    }

    public function test_create_honor_rejected_when_date_outside_kegiatan_range(): void
    {
        KegiatanManmit::create([
            'id' => 'DPP26',
            'nama' => 'Updating DPP 2026',
            'jenis_kegiatan' => 'SURVEI',
            'tgl_mulai_pelaksanaan' => '2026-07-01',
            'tgl_akhir_pelaksanaan' => '2026-09-30',
        ]);

        $res = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->postJson('/api/v1/honors', [
                'kegiatan_manmit_id' => 'DPP26',
                'jabatan' => 'PPL',
                'jenis_honor' => 'PENDATAAN',
                'satuan_honor' => 'DOKUMEN',
                'harga_per_satuan' => 53000,
                'tanggal_akhir_kegiatan' => '2026-10-15', // Di luar batas 30 Sept 2026
            ]);

        $res->assertStatus(422)
            ->assertJsonPath('status', 'error');
    }

    public function test_delete_honor_and_rollback(): void
    {
        $kegiatan = KegiatanManmit::create([
            'id' => 'DUTL26',
            'nama' => 'Updating DUTL 2026',
            'jenis_kegiatan' => 'SURVEI',
            'tgl_mulai_pelaksanaan' => '2026-07-01',
            'tgl_akhir_pelaksanaan' => '2026-09-30',
        ]);

        $honor = Honor::create([
            'id' => 'DUTL26-PPL-PENDATAAN',
            'kegiatan_manmit_id' => 'DUTL26',
            'jabatan' => 'PPL',
            'jenis_honor' => 'PENDATAAN',
            'satuan_honor' => 'DOKUMEN',
            'harga_per_satuan' => 53000,
            'tanggal_akhir_kegiatan' => '2026-09-30',
        ]);

        $res = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->deleteJson("/api/v1/honors/{$honor->id}");

        $res->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.deleted_id', 'DUTL26-PPL-PENDATAAN');

        $this->assertDatabaseMissing('honors', ['id' => 'DUTL26-PPL-PENDATAAN']);

        // Rollback delete
        $log = ApiAuditLog::where('action', 'DELETE_HONOR')->latest()->first();
        $this->assertNotNull($log);

        $rollRes = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->postJson("/api/v1/audit-logs/{$log->id}/rollback");

        $rollRes->assertStatus(200);

        $this->assertDatabaseHas('honors', ['id' => 'DUTL26-PPL-PENDATAAN']);
    }

    public function test_delete_honor_blocked_when_has_allocations(): void
    {
        $kegiatan = KegiatanManmit::create([
            'id' => 'DUTL26',
            'nama' => 'Updating DUTL 2026',
            'jenis_kegiatan' => 'SURVEI',
            'tgl_mulai_pelaksanaan' => '2026-07-01',
            'tgl_akhir_pelaksanaan' => '2026-09-30',
        ]);

        $honor = Honor::create([
            'id' => 'DUTL26-PPL-PENDATAAN',
            'kegiatan_manmit_id' => 'DUTL26',
            'jabatan' => 'PPL',
            'jenis_honor' => 'PENDATAAN',
            'satuan_honor' => 'DOKUMEN',
            'harga_per_satuan' => 53000,
            'tanggal_akhir_kegiatan' => '2026-09-30',
        ]);

        $mitra = Mitra::create([
            'id' => 8882,
            'nama_1' => 'Mitra DUTL',
            'nik' => '6104123456780002',
            'id_sobat' => '61048882',
        ]);

        AlokasiHonor::create([
            'mitra_id' => $mitra->id,
            'honor_id' => $honor->id,
            'target_per_satuan_honor' => 3,
            'total_honor' => 159000,
            'status' => 'PENDING',
            'tanggal_mulai_perjanjian' => '2026-07-01',
            'tanggal_akhir_perjanjian' => '2026-09-30',
        ]);

        $res = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->deleteJson("/api/v1/honors/{$honor->id}");

        $res->assertStatus(422)
            ->assertJsonPath('status', 'error');

        $this->assertDatabaseHas('honors', ['id' => 'DUTL26-PPL-PENDATAAN']);
    }
}
