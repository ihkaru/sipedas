<?php

namespace Tests\Feature\Api;

use App\Models\AlokasiHonor;
use App\Models\ApiAuditLog;
use App\Models\Honor;
use App\Models\KegiatanManmit;
use App\Models\Kemitraan;
use App\Models\Mitra;
use App\Models\NomorSurat;
use App\Models\Pegawai;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EntityLifecycleAndRollbackApiTest extends TestCase
{
    use RefreshDatabase;

    protected string $apiKey = 'test-lifecycle-rollback-api-key';
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'dokter_v.api_key' => $this->apiKey,
            'sipedas.api_key' => $this->apiKey,
        ]);

        $this->user = User::factory()->create([
            'email' => 'admin-lifecycle@bps.go.id',
            'name' => 'Admin Lifecycle',
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

    protected function createMitra(string $idSobat = '61049901', string $nama = 'Test Mitra', int $tahun = 2026): Mitra
    {
        $mitra = Mitra::create([
            'id_sobat' => $idSobat,
            'nama_1' => $nama,
            'nik' => '610499' . str_pad($idSobat, 10, '0', STR_PAD_LEFT),
            'no_telp' => '081234567890',
            'nomor_wa' => '6281234567890',
        ]);

        Kemitraan::create([
            'mitra_id' => $mitra->id,
            'tahun' => $tahun,
            'status' => 'AKTIF',
        ]);

        return $mitra;
    }

    protected function createKegiatanAndHonor(string $kegId = 'KEG-LC-1', string $honId = 'HON-LC-1', float $harga = 50000): array
    {
        $kegiatan = KegiatanManmit::create([
            'id' => $kegId,
            'nama' => 'Kegiatan Lifecycle ' . $kegId,
            'jenis_kegiatan' => 'SURVEI',
            'tgl_mulai_pelaksanaan' => '2026-06-01',
            'tgl_akhir_pelaksanaan' => '2026-06-30',
        ]);

        $honor = Honor::create([
            'id' => $honId,
            'kegiatan_manmit_id' => $kegiatan->id,
            'jabatan' => 'Pencacah',
            'jenis_honor' => 'Honor Pendataan',
            'satuan_honor' => 'Dokumen',
            'harga_per_satuan' => $harga,
            'tanggal_akhir_kegiatan' => '2026-06-25',
        ]);

        return [$kegiatan, $honor];
    }

    public function test_update_alokasi_target_and_total_recalculated_with_rollback(): void
    {
        $mitra = $this->createMitra();
        [$kegiatan, $honor] = $this->createKegiatanAndHonor('KEG-ALOK', 'HON-ALOK', 50000);

        // Buat alokasi awal: target 10 -> total 500.000
        $createRes = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->postJson('/api/v1/alokasi', [
                'mitra_id' => $mitra->id,
                'honor_id' => $honor->id,
                'target' => 10,
            ]);

        $createRes->assertStatus(201);
        $alokasiId = $createRes->json('data.id');
        $this->assertEquals(500000, AlokasiHonor::find($alokasiId)->total_honor);

        // Update target ke 20 -> total 1.000.000
        $updateRes = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->patchJson("/api/v1/alokasi/{$alokasiId}", [
                'target' => 20,
            ]);

        $updateRes->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.target', 20)
            ->assertJsonPath('data.total_honor', 1000000);

        $alokasiUpdated = AlokasiHonor::find($alokasiId);
        $this->assertEquals(20, $alokasiUpdated->target_per_satuan_honor);
        $this->assertEquals(1000000, $alokasiUpdated->total_honor);

        // Periksa audit log tercatat
        $auditLog = ApiAuditLog::where('action', 'UPDATE_ALOKASI')
            ->where('target_id', $alokasiId)
            ->latest()
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertTrue($auditLog->is_reversible);

        // Lakukan rollback
        $rollbackRes = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->postJson("/api/v1/audit-logs/{$auditLog->id}/rollback", [
                'reason' => 'Uji coba rollback target alokasi',
            ]);

        $rollbackRes->assertStatus(200)
            ->assertJsonPath('status', 'success');

        // Pastikan alokasi kembali ke target 10 dan total 500.000
        $alokasiRolledBack = AlokasiHonor::find($alokasiId);
        $this->assertEquals(10, $alokasiRolledBack->target_per_satuan_honor);
        $this->assertEquals(500000, $alokasiRolledBack->total_honor);
    }

    public function test_update_alokasi_rejected_when_exceeding_sbml_limit(): void
    {
        $mitra = $this->createMitra('61049902', 'Mitra Over SBML');
        [$kegiatan, $honor] = $this->createKegiatanAndHonor('KEG-SBML', 'HON-SBML', 500000);

        $createRes = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->postJson('/api/v1/alokasi', [
                'mitra_id' => $mitra->id,
                'honor_id' => $honor->id,
                'target' => 2, // 1.000.000 (masih di bawah 3.353.000)
            ]);

        $createRes->assertStatus(201);
        $alokasiId = $createRes->json('data.id');

        // Coba update target ke 10 -> 5.000.000 (melebihi pagu SBML survei 3.353.000)
        $updateRes = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->patchJson("/api/v1/alokasi/{$alokasiId}", [
                'target' => 10,
            ]);

        $updateRes->assertStatus(422)
            ->assertJsonPath('status', 'error');

        // Pastikan alokasi tidak berubah
        $this->assertEquals(2, AlokasiHonor::find($alokasiId)->target_per_satuan_honor);
    }

    public function test_create_mitra_and_rollback(): void
    {
        $payload = [
            'nama' => 'Joko Widodo Baru',
            'nik' => '6104991234567890',
            'id_sobat' => '61048899',
            'nomor_wa' => '0813-9876-5432',
            'email' => 'joko.baru@example.com',
            'posisi' => 'Pencacah Lapangan',
            'status_kemitraan' => 'AKTIF',
            'tahun' => 2026,
        ];

        $res = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->postJson('/api/v1/mitras', $payload);

        $res->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.nama', 'Joko Widodo Baru')
            ->assertJsonPath('data.nomor_wa', '6281398765432')
            ->assertJsonPath('data.status_kemitraan', 'AKTIF');

        $mitraId = $res->json('data.id');
        $this->assertDatabaseHas('mitras', ['id' => $mitraId, 'nik' => '6104991234567890']);
        $this->assertDatabaseHas('kemitraans', ['mitra_id' => $mitraId, 'tahun' => 2026, 'status' => 'AKTIF']);

        // Cari audit log
        $audit = ApiAuditLog::where('action', 'CREATE_MITRA')
            ->where('target_id', $mitraId)
            ->first();

        $this->assertNotNull($audit);

        // Rollback pembuatan mitra
        $rollbackRes = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->postJson("/api/v1/audit-logs/{$audit->id}/rollback");

        $rollbackRes->assertStatus(200);
        $this->assertDatabaseMissing('mitras', ['id' => $mitraId]);
        $this->assertDatabaseMissing('kemitraans', ['mitra_id' => $mitraId]);
    }

    public function test_delete_mitra_blocked_when_has_allocations(): void
    {
        $mitra = $this->createMitra('61049903', 'Mitra Has Alloc');
        [$kegiatan, $honor] = $this->createKegiatanAndHonor('KEG-DEL-M', 'HON-DEL-M', 50000);

        $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->postJson('/api/v1/alokasi', [
                'mitra_id' => $mitra->id,
                'honor_id' => $honor->id,
                'target' => 5,
            ])->assertStatus(201);

        // Coba hapus mitra
        $deleteRes = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->deleteJson("/api/v1/mitras/{$mitra->id}");

        $deleteRes->assertStatus(422)
            ->assertJsonPath('status', 'error');

        $this->assertDatabaseHas('mitras', ['id' => $mitra->id]);
    }

    public function test_delete_mitra_and_rollback(): void
    {
        $mitra = $this->createMitra('61049904', 'Mitra Can Delete');
        $mitraId = $mitra->id;

        $deleteRes = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->deleteJson("/api/v1/mitras/{$mitraId}");

        $deleteRes->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseMissing('mitras', ['id' => $mitraId]);

        // Audit log
        $audit = ApiAuditLog::where('action', 'DELETE_MITRA')
            ->where('target_id', $mitraId)
            ->first();

        $this->assertNotNull($audit);

        // Rollback penghapusan
        $rollbackRes = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->postJson("/api/v1/audit-logs/{$audit->id}/rollback");

        $rollbackRes->assertStatus(200);
        $this->assertDatabaseHas('mitras', ['id' => $mitraId, 'nama_1' => 'Mitra Can Delete']);
        $this->assertDatabaseHas('kemitraans', ['mitra_id' => $mitraId, 'tahun' => 2026, 'status' => 'AKTIF']);
    }

    public function test_delete_pegawai_and_rollback(): void
    {
        $pegawai = Pegawai::create([
            'nip' => '199501012020011005',
            'nip9' => '199501015',
            'nama' => 'Pegawai Uji Hapus',
            'panggilan' => 'Hapus',
            'golongan' => 'III/a',
            'pangkat' => 'Penata Muda',
            'jabatan' => 'Pranata Komputer',
            'email' => 'pegawai.hapus@bps.go.id',
            'unit_kerja' => 'IPDS',
            'nomor_wa' => '628111222333',
        ]);

        $deleteRes = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->deleteJson("/api/v1/pegawais/{$pegawai->nip}");

        $deleteRes->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseMissing('pegawais', ['nip' => '199501012020011005']);

        // Audit log
        $audit = ApiAuditLog::where('action', 'DELETE_PEGAWAI')->latest()->first();
        $this->assertNotNull($audit);

        // Rollback
        $rollbackRes = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->postJson("/api/v1/audit-logs/{$audit->id}/rollback");

        $rollbackRes->assertStatus(200);
        $this->assertDatabaseHas('pegawais', [
            'nip' => '199501012020011005',
            'nama' => 'Pegawai Uji Hapus',
        ]);
    }

    public function test_update_kontrak_syncs_allocation_date_and_rollback(): void
    {
        $mitra = $this->createMitra('61049905', 'Mitra Kontrak');
        [$kegiatan, $honor] = $this->createKegiatanAndHonor('KEG-KONTRAK', 'HON-KONTRAK', 50000);

        $alokasiRes = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->postJson('/api/v1/alokasi', [
                'mitra_id' => $mitra->id,
                'honor_id' => $honor->id,
                'target' => 5,
            ]);

        $alokasiId = $alokasiRes->json('data.id');
        $alokasi = AlokasiHonor::find($alokasiId);
        $kontrakId = $alokasi->surat_perjanjian_kerja_id;
        $this->assertNotNull($kontrakId);

        $originalDate = $alokasi->tanggal_penanda_tanganan_spk_oleh_petugas;

        // Update tanggal nomor kontrak ke 2026-06-15
        $newDate = '2026-06-15';
        $updateRes = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->patchJson("/api/v1/kontrak/{$kontrakId}", [
                'tanggal_nomor' => $newDate,
            ]);

        $updateRes->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.tanggal_nomor', $newDate);

        // Cek alokasi tersinkronisasi
        $alokasiUpdated = AlokasiHonor::find($alokasiId);
        $this->assertEquals($newDate, Carbon::parse($alokasiUpdated->tanggal_penanda_tanganan_spk_oleh_petugas)->format('Y-m-d'));

        // Rollback
        $audit = ApiAuditLog::where('action', 'UPDATE_KONTRAK')
            ->where('target_id', $kontrakId)
            ->first();
        $this->assertNotNull($audit);

        $rollbackRes = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->postJson("/api/v1/audit-logs/{$audit->id}/rollback");

        $rollbackRes->assertStatus(200);

        // Verifikasi kontrak dan alokasi kembali ke tanggal awal
        $kontrakRolledBack = NomorSurat::find($kontrakId);
        $this->assertEquals(
            Carbon::parse($originalDate)->format('Y-m-d'),
            Carbon::parse($kontrakRolledBack->tanggal_nomor)->format('Y-m-d')
        );
    }

    public function test_update_bast_and_rollback(): void
    {
        $mitra = $this->createMitra('61049906', 'Mitra BAST');
        [$kegiatan, $honor] = $this->createKegiatanAndHonor('KEG-BAST', 'HON-BAST', 50000);

        $alokasiRes = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->postJson('/api/v1/alokasi', [
                'mitra_id' => $mitra->id,
                'honor_id' => $honor->id,
                'target' => 5,
            ]);

        $alokasiId = $alokasiRes->json('data.id');
        $alokasi = AlokasiHonor::find($alokasiId);
        $bastId = $alokasi->surat_bast_id;
        $this->assertNotNull($bastId);

        $bastOriginal = NomorSurat::find($bastId);
        $originalDate = $bastOriginal->tanggal_nomor;

        // Update tanggal nomor BAST ke 2026-06-28
        $newDate = '2026-06-28';
        $updateRes = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->patchJson("/api/v1/bast/{$bastId}", [
                'tanggal_nomor' => $newDate,
            ]);

        $updateRes->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.tanggal_nomor', $newDate);

        // Rollback
        $audit = ApiAuditLog::where('action', 'UPDATE_BAST')
            ->where('target_id', $bastId)
            ->first();
        $this->assertNotNull($audit);

        $rollbackRes = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->postJson("/api/v1/audit-logs/{$audit->id}/rollback");

        $rollbackRes->assertStatus(200);

        $bastRolledBack = NomorSurat::find($bastId);
        $this->assertEquals(
            Carbon::parse($originalDate)->format('Y-m-d'),
            Carbon::parse($bastRolledBack->tanggal_nomor)->format('Y-m-d')
        );
    }

    public function test_update_kegiatan_manmit_additional_fields(): void
    {
        [$kegiatan] = $this->createKegiatanAndHonor('KEG-UPDATE-FIELDS', 'HON-UF', 50000);

        $updateRes = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->patchJson("/api/v1/kegiatan-manmit/{$kegiatan->id}", [
                'jenis_kegiatan' => 'SENSUS',
                'frekuensi_kegiatan' => 'TAHUNAN',
                'template_kontrak' => 'TEMPLATE_SENSUS_2026',
            ]);

        $updateRes->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.jenis_kegiatan', 'SENSUS')
            ->assertJsonPath('data.frekuensi_kegiatan', 'TAHUNAN')
            ->assertJsonPath('data.template_kontrak', 'TEMPLATE_SENSUS_2026');

        $kegiatanFresh = KegiatanManmit::find($kegiatan->id);
        $this->assertEquals('SENSUS', $kegiatanFresh->jenis_kegiatan);
        $this->assertEquals('TAHUNAN', $kegiatanFresh->frekuensi_kegiatan);
        $this->assertEquals('TEMPLATE_SENSUS_2026', $kegiatanFresh->template_kontrak);
    }
}
