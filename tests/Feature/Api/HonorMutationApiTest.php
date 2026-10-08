<?php

namespace Tests\Feature\Api;

use App\Models\AlokasiHonor;
use App\Models\ApiAuditLog;
use App\Models\Honor;
use App\Models\KegiatanManmit;
use App\Models\Kemitraan;
use App\Models\Mitra;
use App\Models\NomorSurat;
use App\Models\User;
use App\Supports\Constants;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HonorMutationApiTest extends TestCase
{
    use RefreshDatabase;

    protected string $apiKey = 'test-honor-key';
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

    public function test_get_honors_with_filters_and_compact_mode(): void
    {
        $kegiatan = KegiatanManmit::create([
            'id' => 'SUSENAS26',
            'nama' => 'Susenas Maret 2026',
            'jenis_kegiatan' => 'SURVEI',
            'tgl_mulai_pelaksanaan' => '2026-03-01',
            'tgl_akhir_pelaksanaan' => '2026-03-31',
        ]);

        $honor = Honor::create([
            'id' => 'SUSENAS26-PCL-DOKUMEN',
            'kegiatan_manmit_id' => $kegiatan->id,
            'jabatan' => 'PCL',
            'jenis_honor' => 'DOKUMEN',
            'satuan_honor' => 'Dokumen',
            'harga_per_satuan' => 50000,
            'tanggal_akhir_kegiatan' => '2026-03-25',
        ]);

        // 1. Full Mode
        $res = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->getJson('/api/v1/honors?kegiatan_id=SUSENAS26');

        $res->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.0.id', $honor->id)
            ->assertJsonPath('data.0.jabatan', 'PCL')
            ->assertJsonPath('data.0.harga_per_satuan', 50000);

        // 2. Compact Mode
        $resCompact = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->getJson('/api/v1/honors?compact=1&q=SUSENAS');

        $resCompact->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.0.id', $honor->id)
            ->assertJsonPath('data.0.kegiatan_id', 'SUSENAS26')
            ->assertJsonPath('data.0.harga', 50000);
    }

    public function test_show_honor_detail(): void
    {
        $kegiatan = KegiatanManmit::create([
            'id' => 'SERUTI26',
            'nama' => 'Seruti TW3 2026',
            'jenis_kegiatan' => 'SURVEI',
            'tgl_mulai_pelaksanaan' => '2026-07-01',
            'tgl_akhir_pelaksanaan' => '2026-10-31',
        ]);

        $honor = Honor::create([
            'id' => 'SERUTI26-PML-DOKUMEN',
            'kegiatan_manmit_id' => $kegiatan->id,
            'jabatan' => 'PML',
            'jenis_honor' => 'DOKUMEN',
            'satuan_honor' => 'Dokumen',
            'harga_per_satuan' => 75000,
            'tanggal_akhir_kegiatan' => '2026-09-30',
        ]);

        $res = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->getJson("/api/v1/honors/{$honor->id}");

        $res->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.id', $honor->id)
            ->assertJsonPath('data.kegiatan_manmit.id', 'SERUTI26')
            ->assertJsonPath('data.tanggal_akhir_kegiatan', '2026-09-30');
    }

    public function test_update_honor_tanggal_akhir_kegiatan_propagates_and_records_audit(): void
    {
        $kegiatan = KegiatanManmit::create([
            'id' => 'SERUTI26-TW3',
            'nama' => 'Seruti Triwulan III',
            'jenis_kegiatan' => 'SURVEI',
            'tgl_mulai_pelaksanaan' => '2026-07-01',
            'tgl_akhir_pelaksanaan' => '2026-10-31',
        ]);

        $honor = Honor::create([
            'id' => 'SERUTI26-TW3-ENTRI',
            'kegiatan_manmit_id' => $kegiatan->id,
            'jabatan' => 'Operator',
            'jenis_honor' => 'ENTRI',
            'satuan_honor' => 'Dokumen',
            'harga_per_satuan' => 25000,
            'tanggal_akhir_kegiatan' => '2026-09-30',
        ]);

        $mitra = Mitra::create([
            'id_sobat' => '61041001',
            'nama_1' => 'Mitra Seruti',
            'nik' => '6104100101010001',
        ]);

        Kemitraan::create([
            'mitra_id' => $mitra->id,
            'tahun' => 2026,
            'status' => 'AKTIF',
        ]);

        $spk = NomorSurat::create([
            'nomor' => 1,
            'sub_nomor' => 0,
            'tanggal_nomor' => '2026-09-01',
            'tahun' => 2026,
            'jenis' => 'SPK',
        ]);

        $bast = NomorSurat::create([
            'nomor' => 1,
            'sub_nomor' => 1,
            'tanggal_nomor' => '2026-09-30',
            'tahun' => 2026,
            'jenis' => 'BAST',
        ]);

        $alokasi = AlokasiHonor::create([
            'mitra_id' => $mitra->id,
            'honor_id' => $honor->id,
            'target_per_satuan_honor' => 10,
            'alokasi_volume' => 10,
            'total_honor' => 250000,
            'surat_perjanjian_kerja_id' => $spk->id,
            'surat_bast_id' => $bast->id,
            'tanggal_mulai_perjanjian' => '2026-09-01',
            'tanggal_akhir_perjanjian' => '2026-09-30',
            'tanggal_penanda_tanganan_spk_oleh_petugas' => '2026-08-31',
        ]);

        // Eksekusi update tanggal_akhir_kegiatan ke 14 Oktober 2026
        $res = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->patchJson("/api/v1/honors/{$honor->id}", [
                'tanggal_akhir_kegiatan' => '2026-10-14',
            ]);

        $res->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.tanggal_akhir_kegiatan', '2026-10-14')
            ->assertJsonPath('data.tanggal_pembayaran_maksimal', '2026-11-03') // 14 Oct + 20 days
            ->assertJsonPath('data.propagated_alokasi_count', 1);

        // Verifikasi alokasi honor dan nomor surat telah dipropagasi ke Oktober
        $alokasi->refresh();
        $this->assertEquals('2026-10-01', $alokasi->tanggal_mulai_perjanjian?->format('Y-m-d'));
        $this->assertEquals('2026-10-31', $alokasi->tanggal_akhir_perjanjian?->format('Y-m-d'));

        // BAST tanggal nomor harus diselaraskan dengan tanggal akhir (14 Okt 2026)
        $bast->refresh();
        $this->assertNotNull($bast->tanggal_nomor);

        // Verifikasi Audit Log
        $auditLog = ApiAuditLog::where('action', 'UPDATE_HONOR')->latest()->first();
        $this->assertNotNull($auditLog);
        $this->assertEquals('2026-09-30', $auditLog->state_before['tanggal_akhir_kegiatan']);
        $this->assertEquals('2026-10-14', $auditLog->state_after['tanggal_akhir_kegiatan']);
        $this->assertTrue($auditLog->is_reversible);

        // Rollback audit log
        $rollRes = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->postJson("/api/v1/audit-logs/{$auditLog->id}/rollback", [
                'reason' => 'Uji coba rollback honor tanggal',
            ]);

        $rollRes->assertStatus(200);

        // Verifikasi kondisi setelah rollback
        $honor->refresh();
        $this->assertEquals('2026-09-30', $honor->tanggal_akhir_kegiatan?->format('Y-m-d'));

        $alokasi->refresh();
        $this->assertEquals('2026-09-01', $alokasi->tanggal_mulai_perjanjian?->format('Y-m-d'));
        $this->assertEquals('2026-09-30', $alokasi->tanggal_akhir_perjanjian?->format('Y-m-d'));
    }

    public function test_update_honor_rejected_when_outside_kegiatan_range(): void
    {
        $kegiatan = KegiatanManmit::create([
            'id' => 'SERUTI-KECIL',
            'nama' => 'Seruti Batas Singkat',
            'jenis_kegiatan' => 'SURVEI',
            'tgl_mulai_pelaksanaan' => '2026-09-01',
            'tgl_akhir_pelaksanaan' => '2026-09-18', // Berakhir 18 September
        ]);

        $honor = Honor::create([
            'id' => 'SERUTI-KECIL-PCL',
            'kegiatan_manmit_id' => $kegiatan->id,
            'jabatan' => 'PCL',
            'jenis_honor' => 'DOKUMEN',
            'satuan_honor' => 'Dokumen',
            'harga_per_satuan' => 40000,
            'tanggal_akhir_kegiatan' => '2026-09-15',
        ]);

        // Coba update tanggal ke 14 Oktober 2026 (di luar rentang 18 September)
        $res = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->patchJson("/api/v1/honors/{$honor->id}", [
                'tanggal_akhir_kegiatan' => '2026-10-14',
            ]);

        $res->assertStatus(422)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('data.requested_tanggal_akhir_kegiatan', '2026-10-14');

        // Pastikan tidak berubah
        $honor->refresh();
        $this->assertEquals('2026-09-15', $honor->tanggal_akhir_kegiatan?->format('Y-m-d'));
    }
}
