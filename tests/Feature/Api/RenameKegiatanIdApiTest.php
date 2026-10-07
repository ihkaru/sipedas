<?php

namespace Tests\Feature\Api;

use App\Models\AlokasiHonor;
use App\Models\ApiAuditLog;
use App\Models\Honor;
use App\Models\KegiatanManmit;
use App\Models\Kemitraan;
use App\Models\Mitra;
use App\Models\Setting;
use App\Services\HonorAllocationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RenameKegiatanIdApiTest extends TestCase
{
    use RefreshDatabase;

    protected string $apiKey = 'test-rename-kegiatan-key';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'dokter_v.api_key' => $this->apiKey,
            'sipedas.api_key' => $this->apiKey,
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

    public function test_rename_kegiatan_id_cascades_honors_and_preserves_spk_and_bast(): void
    {
        // 1. Buat kegiatan dengan ID 'SERUTI26'
        $kegiatan = KegiatanManmit::create([
            'id' => 'SERUTI26',
            'nama' => 'Survei Ekonomi Rumah Tangga Triwulan 3',
            'jenis_kegiatan' => 'SURVEI',
            'tgl_mulai_pelaksanaan' => '2026-10-01',
            'tgl_akhir_pelaksanaan' => '2026-10-31',
        ]);

        // 2. Buat honor dengan prefix ID 'SERUTI26-PCL'
        $honor = Honor::create([
            'id' => 'SERUTI26-PCL-HONOR',
            'kegiatan_manmit_id' => 'SERUTI26',
            'jabatan' => 'Pencacah',
            'jenis_honor' => 'Honor Pencacahan',
            'satuan_honor' => 'Dokumen',
            'harga_per_satuan' => 150000,
            'tanggal_akhir_kegiatan' => '2026-10-25',
        ]);

        // 3. Buat mitra dan alokasi (otomatis generate SPK & BAST)
        $mitra = Mitra::create([
            'id_sobat' => '61041306',
            'nama_1' => 'Muhammad Deny Hafizzul',
            'nik' => '6104100000000306',
        ]);
        Kemitraan::create([
            'mitra_id' => $mitra->id,
            'tahun' => 2026,
            'status' => 'AKTIF',
        ]);

        $alokasi = HonorAllocationService::allocate($mitra->id, $honor->id, 5.0);
        $originalSpkId = $alokasi->surat_perjanjian_kerja_id;
        $originalBastId = $alokasi->surat_bast_id;
        $originalSpkNomor = $alokasi->kontrak->nomor_surat_perjanjian_kerja;
        $originalBastNomor = $alokasi->bast->nomor_surat_perjanjian_kerja;

        $this->assertNotNull($originalSpkId);
        $this->assertNotNull($originalBastId);

        // 4. Panggil API rename-id: ubah 'SERUTI26' -> 'SERUTI26-TW3'
        $response = $this->withHeaders([
            'X-API-KEY' => $this->apiKey,
        ])->postJson('/api/v1/kegiatan-manmit/SERUTI26/rename-id', [
            'new_id' => 'SERUTI26-TW3',
            'new_nama' => 'Survei Ekonomi Rumah Tangga Triwulan III',
            'cascade_honor_ids' => true,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.old_id', 'SERUTI26')
            ->assertJsonPath('data.new_id', 'SERUTI26-TW3')
            ->assertJsonPath('data.affected_honors_count', 1)
            ->assertJsonPath('data.affected_alokasi_count', 1);

        // 5. Verifikasi di database:
        // A. Kegiatan lama tidak ada, kegiatan baru ada
        $this->assertNull(KegiatanManmit::find('SERUTI26'));
        $kegiatanBaru = KegiatanManmit::find('SERUTI26-TW3');
        $this->assertNotNull($kegiatanBaru);
        $this->assertEquals('Survei Ekonomi Rumah Tangga Triwulan III', $kegiatanBaru->nama);

        // B. Honor lama tidak ada, honor baru ada dengan prefix baru
        $this->assertNull(Honor::find('SERUTI26-PCL-HONOR'));
        $honorBaru = Honor::find('SERUTI26-TW3-PCL-HONOR');
        $this->assertNotNull($honorBaru);
        $this->assertEquals('SERUTI26-TW3', $honorBaru->kegiatan_manmit_id);

        // C. AlokasiHonor tetap utuh 100%, SPK & BAST TIDAK BERUBAH SAMA SEKALI
        $alokasiUpdated = AlokasiHonor::find($alokasi->id);
        $this->assertNotNull($alokasiUpdated);
        $this->assertEquals('SERUTI26-TW3-PCL-HONOR', $alokasiUpdated->honor_id);
        $this->assertEquals($originalSpkId, $alokasiUpdated->surat_perjanjian_kerja_id);
        $this->assertEquals($originalBastId, $alokasiUpdated->surat_bast_id);
        $this->assertEquals($originalSpkNomor, $alokasiUpdated->kontrak->nomor_surat_perjanjian_kerja);
        $this->assertEquals($originalBastNomor, $alokasiUpdated->bast->nomor_surat_perjanjian_kerja);

        // 6. Verifikasi Rollback Audit Log
        $auditLog = ApiAuditLog::where('action', 'RENAME_KEGIATAN_ID')->first();
        $this->assertNotNull($auditLog);
        $this->assertTrue($auditLog->is_reversible);

        $rollbackResponse = $this->withHeaders([
            'X-API-KEY' => $this->apiKey,
        ])->postJson("/api/v1/audit-logs/{$auditLog->id}/rollback", [
            'reason' => 'Rollback rename test',
        ]);
        $rollbackResponse->assertStatus(200);

        // Setelah rollback: ID kembali ke 'SERUTI26' dan honor kembali ke 'SERUTI26-PCL-HONOR'
        $this->assertNotNull(KegiatanManmit::find('SERUTI26'));
        $this->assertNull(KegiatanManmit::find('SERUTI26-TW3'));
        $this->assertNotNull(Honor::find('SERUTI26-PCL-HONOR'));
        $this->assertEquals('SERUTI26-PCL-HONOR', AlokasiHonor::find($alokasi->id)->honor_id);
    }
}
