<?php

namespace Tests\Feature\Api;

use App\Models\ApiAuditLog;
use App\Models\Honor;
use App\Models\KegiatanManmit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KegiatanManmitMutationApiTest extends TestCase
{
    use RefreshDatabase;

    protected string $apiKey = 'test-kegiatan-key';
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

    public function test_show_kegiatan_manmit(): void
    {
        $kegiatan = KegiatanManmit::create([
            'id' => 'VHTS26',
            'nama' => 'Survei Hotel Bulanan 2026',
            'jenis_kegiatan' => 'SURVEI',
            'tgl_mulai_pelaksanaan' => '2026-01-01',
            'tgl_akhir_pelaksanaan' => '2026-12-31',
        ]);

        $res = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->getJson("/api/v1/kegiatan-manmit/{$kegiatan->id}");

        $res->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.id', 'VHTS26')
            ->assertJsonPath('data.nama', 'Survei Hotel Bulanan 2026');
    }

    public function test_update_kegiatan_manmit_dates_and_name_with_rollback(): void
    {
        $kegiatan = KegiatanManmit::create([
            'id' => 'SERUTI26-TW3',
            'nama' => 'Seruti Triwulan III 2026',
            'jenis_kegiatan' => 'SURVEI',
            'tgl_mulai_pelaksanaan' => '2026-07-01',
            'tgl_akhir_pelaksanaan' => '2026-09-18',
        ]);

        // Perpanjang tanggal akhir pelaksanaan ke 14 Oktober 2026
        $res = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->patchJson("/api/v1/kegiatan-manmit/{$kegiatan->id}", [
                'tgl_akhir_pelaksanaan' => '2026-10-14',
                'nama' => 'Seruti Triwulan III (Diperpanjang)',
            ]);

        $res->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.tgl_akhir_pelaksanaan', '2026-10-14')
            ->assertJsonPath('data.nama', 'Seruti Triwulan III (Diperpanjang)');

        $kegiatan->refresh();
        $this->assertEquals('2026-10-14', $kegiatan->tgl_akhir_pelaksanaan);

        // Verifikasi audit log
        $auditLog = ApiAuditLog::where('action', 'UPDATE_KEGIATAN_MANMIT')->latest()->first();
        $this->assertNotNull($auditLog);
        $this->assertEquals('2026-09-18', $auditLog->state_before['tgl_akhir_pelaksanaan']);
        $this->assertEquals('2026-10-14', $auditLog->state_after['tgl_akhir_pelaksanaan']);

        // Rollback
        $rollRes = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->postJson("/api/v1/audit-logs/{$auditLog->id}/rollback");

        $rollRes->assertStatus(200);

        $kegiatan->refresh();
        $this->assertEquals('2026-09-18', $kegiatan->tgl_akhir_pelaksanaan);
        $this->assertEquals('Seruti Triwulan III 2026', $kegiatan->nama);
    }

    public function test_update_kegiatan_manmit_rejected_when_narrowing_range_orphans_child_honors(): void
    {
        $kegiatan = KegiatanManmit::create([
            'id' => 'SUSENAS26',
            'nama' => 'Susenas 2026',
            'jenis_kegiatan' => 'SURVEI',
            'tgl_mulai_pelaksanaan' => '2026-02-01',
            'tgl_akhir_pelaksanaan' => '2026-04-30',
        ]);

        Honor::create([
            'id' => 'SUSENAS26-ENTRI',
            'kegiatan_manmit_id' => $kegiatan->id,
            'jabatan' => 'Operator',
            'jenis_honor' => 'ENTRI',
            'satuan_honor' => 'Dokumen',
            'harga_per_satuan' => 20000,
            'tanggal_akhir_kegiatan' => '2026-04-20',
        ]);

        // Coba persempit rentang kegiatan menjadi selesai 31 Maret (meninggalkan honor 20 April di luar rentang)
        $res = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->patchJson("/api/v1/kegiatan-manmit/{$kegiatan->id}", [
                'tgl_akhir_pelaksanaan' => '2026-03-31',
            ]);

        $res->assertStatus(422)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('data.conflicting_honors.0.id', 'SUSENAS26-ENTRI');

        // Pastikan tidak berubah
        $kegiatan->refresh();
        $this->assertEquals('2026-04-30', $kegiatan->tgl_akhir_pelaksanaan);
    }
}
