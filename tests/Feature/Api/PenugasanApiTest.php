<?php

namespace Tests\Feature\Api;

use App\Models\ApiAuditLog;
use App\Models\Kegiatan;
use App\Models\KegiatanManmit;
use App\Models\Mitra;
use App\Models\NomorSurat;
use App\Models\Pegawai;
use App\Models\Pengaturan;
use App\Models\Penugasan;
use App\Models\RiwayatPengajuan;
use App\Models\Setting;
use App\Models\User;
use App\Supports\Constants;
use App\Supports\TanggalMerah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PenugasanApiTest extends TestCase
{
    use RefreshDatabase;

    protected string $apiKey = 'test-penugasan-api-key';
    protected User $user;
    protected Pegawai $pegawai1;
    protected Pegawai $pegawai2;
    protected Pegawai $kepalaSatker;
    protected Mitra $mitra1;
    protected Kegiatan $kegiatan;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'dokter_v.api_key' => $this->apiKey,
            'sipedas.api_key' => $this->apiKey,
        ]);

        TanggalMerah::$tanggalMerahDates = null;
        Pengaturan::$values = null;

        $this->user = User::factory()->create([
            'email' => 'agent@bps.go.id',
            'name' => 'Agent Dokter V',
        ]);

        // Setup Kepala Satker / Default PLH
        $this->kepalaSatker = Pegawai::create([
            'nip' => '198008112005021004',
            'nip9' => '198008112',
            'nama' => 'Ir. Kepala Satker, M.Si',
            'jabatan' => 'Kepala BPS Kabupaten Kubu Raya',
            'golongan' => 'IV/b',
            'pangkat' => 'Pembina Tk I',
            'unit_kerja' => 'BPS Kabupaten Kubu Raya',
            'panggilan' => 'Kepala',
            'email' => 'kepala@bps.go.id',
        ]);

        Pengaturan::create([
            'key' => 'ID_PLH_DEFAULT',
            'nilai' => $this->kepalaSatker->nip,
            'deskripsi' => 'Default NIP PLH Kepala Satker',
        ]);

        Pengaturan::create([
            'key' => 'NAMA_KAKO',
            'nilai' => 'BPS Kabupaten Kubu Raya',
            'deskripsi' => 'Nama Satker Lengkap',
        ]);

        // Setup Pegawai
        $this->pegawai1 = Pegawai::create([
            'nip' => '199501012020121001',
            'nip9' => '199501012',
            'nama' => 'Ihza Budiman, S.Tr.Stat.',
            'jabatan' => 'Pranata Komputer Ahli Pertama',
            'golongan' => 'III/a',
            'pangkat' => 'Penata Muda',
            'unit_kerja' => 'BPS Kabupaten Kubu Raya',
            'panggilan' => 'Ihza',
            'email' => 'ihza@bps.go.id',
            'atasan_langsung_id' => $this->kepalaSatker->nip,
        ]);

        $this->pegawai2 = Pegawai::create([
            'nip' => '199602022020122002',
            'nip9' => '199602022',
            'nama' => 'Tari Lestari, S.Stat.',
            'jabatan' => 'Statistisi Ahli Pertama',
            'golongan' => 'III/a',
            'pangkat' => 'Penata Muda',
            'unit_kerja' => 'BPS Kabupaten Kubu Raya',
            'panggilan' => 'Tari',
            'email' => 'tari@bps.go.id',
            'atasan_langsung_id' => $this->kepalaSatker->nip,
        ]);

        // Setup Mitra
        $this->mitra1 = Mitra::create([
            'id_sobat' => '610422020048',
            'nama_1' => 'Herri Gustaman',
            'email' => 'herri@gmail.com',
            'nik' => '6104010101900001',
            'posisi' => 'PPL',
            'status_seleksi_1_terpilih_2_tidak_terpilih' => 'Terpilih',
        ]);

        // Setup Kegiatan Manmit & Kegiatan Perjadin
        $kegiatanManmit = KegiatanManmit::create([
            'id' => 'SUSENAS26',
            'nama' => 'Pendataan Susenas Maret 2026',
            'jenis_kegiatan' => 'SURVEI',
            'tgl_mulai_pelaksanaan' => '2026-03-01',
            'tgl_akhir_pelaksanaan' => '2026-03-31',
        ]);

        $this->kegiatan = Kegiatan::create([
            'id' => 'SUSENAS26',
            'nama' => 'Pendataan Susenas Maret 2026',
            'tgl_awal_perjadin' => '2026-03-01 00:00:00',
            'tgl_akhir_perjadin' => '2026-03-31 23:59:59',
            'kegiatan_manmit_id' => 'SUSENAS26',
            'pj_kegiatan_id' => $this->pegawai1->nip,
        ]);
    }

    public function test_preflight_dry_run_simulation_check(): void
    {
        // 2026-03-02 adalah hari Senin (hari kerja)
        $payload = [
            'jenis_surat_tugas' => 'PERJALAN_DINAS_DALAM_KOTA',
            'kegiatan_id' => 'SUSENAS26',
            'nips' => [$this->pegawai1->nip],
            'tgl_mulai_tugas' => '2026-03-02',
            'tgl_akhir_tugas' => '2026-03-04',
            'level_tujuan_penugasan' => 'LEVEL_PENUGASAN_NAMA_TEMPAT',
            'nama_tempat_tujuan' => 'Kantor Desa Kuala Dua',
            'transportasi' => 'TRANSPORTASI_KENDARAAN_DINAS',
        ];

        $res = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->postJson('/api/v1/penugasan/check', $payload);

        $res->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.eligible', true)
            ->assertJsonPath('data.preview.total_pegawai', 1)
            ->assertJsonPath('data.preview.total_personil', 1)
            ->assertJsonPath('data.approver.nip', $this->kepalaSatker->nip)
            ->assertJsonPath('data.preview.dokumen.surat_tugas.akan_diterbitkan', true)
            ->assertJsonPath('data.preview.dokumen.surat_perjalanan_dinas.akan_diterbitkan', true);

        // Pastikan tidak ada data yang tersimpan di DB
        $this->assertEquals(0, Penugasan::count());
        $this->assertEquals(0, NomorSurat::count());
        $this->assertEquals(0, ApiAuditLog::count());
    }

    public function test_reject_when_date_outside_kegiatan_range_with_remediation(): void
    {
        $payload = [
            'jenis_surat_tugas' => 'NON_SPPD',
            'kegiatan_id' => 'SUSENAS26',
            'nips' => [$this->pegawai1->nip],
            'tgl_mulai_tugas' => '2026-04-05', // Di luar batas kegiatan (akhir Maret)
            'tgl_akhir_tugas' => '2026-04-10',
            'level_tujuan_penugasan' => 'LEVEL_PENUGASAN_TANPA_LOKASI',
        ];

        $res = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->postJson('/api/v1/penugasan/check', $payload);

        $res->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('data.eligible', false);

        $this->assertArrayHasKey('tgl_akhir_tugas', $res->json('data.errors'));
        $this->assertArrayHasKey('tgl_akhir_tugas', $res->json('data.remediations'));
    }

    public function test_reject_when_weekend_travel_disallowed(): void
    {
        // 2026-03-07 adalah hari Sabtu (weekend)
        Setting::updateOrCreate(['key' => 'allow_weekend_travel'], ['value' => '0']);

        $payload = [
            'jenis_surat_tugas' => 'PERJALAN_DINAS_DALAM_KOTA',
            'kegiatan_id' => 'SUSENAS26',
            'nips' => [$this->pegawai1->nip],
            'tgl_mulai_tugas' => '2026-03-07',
            'tgl_akhir_tugas' => '2026-03-08',
            'level_tujuan_penugasan' => 'LEVEL_PENUGASAN_NAMA_TEMPAT',
            'nama_tempat_tujuan' => 'Kecamatan Sungai Raya',
            'transportasi' => 'TRANSPORTASI_KENDARAAN_DINAS',
        ];

        $res = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->postJson('/api/v1/penugasan/check', $payload);

        $res->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('data.eligible', false);

        $this->assertArrayHasKey('tgl_mulai_tugas', $res->json('data.errors'));
    }

    public function test_create_non_sppd_auto_approves_and_generates_st_number(): void
    {
        // Izin weekend travel aktif
        Setting::updateOrCreate(['key' => 'allow_weekend_travel'], ['value' => '1']);

        $payload = [
            'jenis_surat_tugas' => 'NON_SPPD',
            'kegiatan_id' => 'SUSENAS26',
            'nips' => [$this->pegawai1->nip],
            'tgl_mulai_tugas' => '2026-03-02',
            'tgl_akhir_tugas' => '2026-03-05',
            'level_tujuan_penugasan' => 'LEVEL_PENUGASAN_TANPA_LOKASI',
        ];

        $res = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->postJson('/api/v1/penugasan', $payload);

        $res->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('total_created', 1);

        $penugasan = Penugasan::first();
        $this->assertNotNull($penugasan);
        $this->assertEquals(Constants::NON_SPPD, $penugasan->jenis_surat_tugas);
        // Non-SPPD auto approved
        $this->assertEquals(Constants::STATUS_PENGAJUAN_DISETUJUI, $penugasan->riwayatPengajuan->status);
        // Memiliki nomor surat tugas
        $this->assertNotNull($penugasan->surat_tugas_id);
        // Tidak memiliki nomor SPD
        $this->assertNull($penugasan->surat_perjadin_id);

        // Verifikasi Audit Log
        $auditLog = ApiAuditLog::where('action', 'CREATE_PENUGASAN')->first();
        $this->assertNotNull($auditLog);
        $this->assertTrue($auditLog->is_reversible);
    }

    public function test_create_perjalanan_dinas_with_approver_and_transition_action(): void
    {
        Setting::updateOrCreate(['key' => 'allow_weekend_travel'], ['value' => '1']);

        $payload = [
            'jenis_surat_tugas' => 'PERJALAN_DINAS_DALAM_KOTA',
            'kegiatan_id' => 'SUSENAS26',
            'nips' => [$this->pegawai1->nip],
            'tgl_mulai_tugas' => '2026-03-02',
            'tgl_akhir_tugas' => '2026-03-04',
            'level_tujuan_penugasan' => 'LEVEL_PENUGASAN_NAMA_TEMPAT',
            'nama_tempat_tujuan' => 'Kantor Desa Kuala Dua',
            'transportasi' => 'TRANSPORTASI_KENDARAAN_DINAS',
        ];

        $res = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->postJson('/api/v1/penugasan', $payload);

        $res->assertStatus(201);
        $penugasanId = $res->json('data.0.id');

        $penugasan = Penugasan::find($penugasanId);
        $this->assertEquals(Constants::STATUS_PENGAJUAN_DIKIRIM, $penugasan->riwayatPengajuan->status);
        $this->assertNull($penugasan->surat_tugas_id);
        $this->assertNull($penugasan->surat_perjadin_id);

        // Eksekusi aksi approval
        $actionRes = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->postJson("/api/v1/penugasan/{$penugasanId}/action", [
                'action' => 'setujui',
            ]);

        $actionRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', Constants::STATUS_PENGAJUAN_DISETUJUI);

        $penugasan->refresh();
        $this->assertEquals(Constants::STATUS_PENGAJUAN_DISETUJUI, $penugasan->riwayatPengajuan->status);
        $this->assertNotNull($penugasan->surat_tugas_id);
        $this->assertNotNull($penugasan->surat_perjadin_id);
    }

    public function test_reject_overlapping_perjadin_schedule(): void
    {
        Setting::updateOrCreate(['key' => 'allow_weekend_travel'], ['value' => '1']);

        // Buat penugasan SPPD pertama yang sudah disetujui (2-4 Maret 2026)
        $p1 = Penugasan::create([
            'nip' => $this->pegawai1->nip,
            'kegiatan_id' => 'SUSENAS26',
            'nip_pengaju' => $this->pegawai1->nip,
            'level_tujuan_penugasan' => Constants::LEVEL_PENUGASAN_TANPA_LOKASI,
            'tgl_mulai_tugas' => '2026-03-02 08:00:00',
            'tgl_akhir_tugas' => '2026-03-04 17:00:00',
            'tgl_pengajuan_tugas' => '2026-03-01',
            'jenis_peserta' => Constants::JENIS_PESERTA_SURAT_TUGAS_PEGAWAI,
            'grup_id' => Penugasan::getGrupId(),
            'jenis_surat_tugas' => Constants::PERJALAN_DINAS_DALAM_KOTA,
            'plh_id' => $this->kepalaSatker->nip,
        ]);
        RiwayatPengajuan::kirim([$p1->id]);
        $p1->setujui(checkRole: false);

        // Coba ajukan SPPD kedua untuk pegawai yang sama pada 3-6 Maret 2026 (tumpang tindih di tanggal 3 & 4 Maret)
        $payload = [
            'jenis_surat_tugas' => 'PERJALAN_DINAS_DALAM_KOTA',
            'kegiatan_id' => 'SUSENAS26',
            'nips' => [$this->pegawai1->nip],
            'tgl_mulai_tugas' => '2026-03-03',
            'tgl_akhir_tugas' => '2026-03-06',
            'level_tujuan_penugasan' => 'LEVEL_PENUGASAN_NAMA_TEMPAT',
            'nama_tempat_tujuan' => 'Desa Rasau Jaya',
            'transportasi' => 'TRANSPORTASI_KENDARAAN_DINAS',
        ];

        $res = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->postJson('/api/v1/penugasan/check', $payload);

        $res->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('data.eligible', false);

        $this->assertArrayHasKey('jadwal_bentrok', $res->json('data.errors'));
        $this->assertContains('2026-03-03', $res->json('data.conflicts.overlapping_dates'));
        $this->assertContains('2026-03-04', $res->json('data.conflicts.overlapping_dates'));
    }

    public function test_team_creation_shares_grup_id_and_nomor_surat(): void
    {
        Setting::updateOrCreate(['key' => 'allow_weekend_travel'], ['value' => '1']);

        $payload = [
            'jenis_surat_tugas' => 'PERJALAN_DINAS_DALAM_KOTA',
            'kegiatan_id' => 'SUSENAS26',
            'nips' => [$this->pegawai1->nip, $this->pegawai2->nip],
            'mitras' => [$this->mitra1->id_sobat],
            'tgl_mulai_tugas' => '2026-03-10',
            'tgl_akhir_tugas' => '2026-03-12',
            'level_tujuan_penugasan' => 'LEVEL_PENUGASAN_NAMA_TEMPAT',
            'nama_tempat_tujuan' => 'Balai Pertemuan Kecamatan Teluk Pakedai',
            'transportasi' => 'TRANSPORTASI_KENDARAAN_DINAS',
        ];

        $res = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->postJson('/api/v1/penugasan', $payload);

        $res->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('total_created', 3);

        $grupId = $res->json('grup_id');
        $this->assertNotEmpty($grupId);

        $teamPenugasans = Penugasan::where('grup_id', $grupId)->get();
        $this->assertCount(3, $teamPenugasans);

        // Setujui anggota pertama
        $teamPenugasans[0]->setujui(checkRole: false);
        // Setujui anggota kedua
        $teamPenugasans[1]->setujui(checkRole: false);

        // Pastikan keduanya menggunakan surat_tugas_id dan surat_perjadin_id yang sama
        $this->assertEquals($teamPenugasans[0]->surat_tugas_id, $teamPenugasans[1]->surat_tugas_id);
        $this->assertEquals($teamPenugasans[0]->surat_perjadin_id, $teamPenugasans[1]->surat_perjadin_id);
    }

    public function test_audit_log_rollback_deletes_penugasan_and_cleans_up_numbers(): void
    {
        Setting::updateOrCreate(['key' => 'allow_weekend_travel'], ['value' => '1']);

        $payload = [
            'jenis_surat_tugas' => 'NON_SPPD',
            'kegiatan_id' => 'SUSENAS26',
            'nips' => [$this->pegawai1->nip],
            'tgl_mulai_tugas' => '2026-03-16',
            'tgl_akhir_tugas' => '2026-03-18',
            'level_tujuan_penugasan' => 'LEVEL_PENUGASAN_TANPA_LOKASI',
        ];

        $res = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->postJson('/api/v1/penugasan', $payload);

        $res->assertStatus(201);
        $penugasanId = $res->json('data.0.id');
        $auditLogId = $res->json('audit_log_id');

        $this->assertDatabaseHas('penugasans', ['id' => $penugasanId]);
        $nomorSuratCountBefore = NomorSurat::count();
        $this->assertGreaterThan(0, $nomorSuratCountBefore);

        // Eksekusi rollback
        $rollbackRes = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->postJson("/api/v1/audit-logs/{$auditLogId}/rollback", [
                'reason' => 'Pengujian rollback penugasan via API test',
            ]);

        $rollbackRes->assertStatus(200)
            ->assertJsonPath('status', 'success');

        // Pastikan record penugasan terhapus
        $this->assertDatabaseMissing('penugasans', ['id' => $penugasanId]);
        // Pastikan nomor surat yang tidak terpakai dibersihkan
        $this->assertEquals(0, Penugasan::count());
    }

    public function test_get_penugasan_list_and_compact_mode(): void
    {
        Setting::updateOrCreate(['key' => 'allow_weekend_travel'], ['value' => '1']);

        // Buat 2 penugasan
        $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->postJson('/api/v1/penugasan', [
                'jenis_surat_tugas' => 'NON_SPPD',
                'kegiatan_id' => 'SUSENAS26',
                'nips' => [$this->pegawai1->nip],
                'tgl_mulai_tugas' => '2026-03-20',
                'tgl_akhir_tugas' => '2026-03-22',
                'level_tujuan_penugasan' => 'LEVEL_PENUGASAN_TANPA_LOKASI',
            ]);

        // 1. Test regular index
        $res = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->getJson('/api/v1/penugasan?nip=' . $this->pegawai1->nip);

        $res->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('pagination.total', 1);

        // 2. Test compact mode
        $compactRes = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->getJson('/api/v1/penugasan?compact=1');

        $compactRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'grup_id',
                        'personil_id',
                        'personil_nama',
                        'kegiatan_id',
                        'kegiatan_nama',
                        'jenis_surat',
                        'status',
                        'tgl_mulai',
                        'tgl_akhir',
                        'lokasi',
                        'no_st',
                        'no_spd',
                    ]
                ]
            ]);
    }

    public function test_update_penugasan_and_rollback(): void
    {
        Setting::updateOrCreate(['key' => 'allow_weekend_travel'], ['value' => '1']);

        // Buat penugasan SPPD (status: STATUS_PENGAJUAN_DIKIRIM)
        $createRes = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->postJson('/api/v1/penugasan', [
                'jenis_surat_tugas' => 'PERJALAN_DINAS_DALAM_KOTA',
                'kegiatan_id' => 'SUSENAS26',
                'nips' => [$this->pegawai1->nip],
                'tgl_mulai_tugas' => '2026-03-09',
                'tgl_akhir_tugas' => '2026-03-11',
                'level_tujuan_penugasan' => 'LEVEL_PENUGASAN_NAMA_TEMPAT',
                'nama_tempat_tujuan' => 'Kantor Desa Limbung',
                'transportasi' => 'TRANSPORTASI_KENDARAAN_DINAS',
            ]);

        $createRes->assertStatus(201);
        $penugasanId = $createRes->json('data.0.id');

        // Update tanggal dan lokasi
        $updateRes = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->patchJson("/api/v1/penugasan/{$penugasanId}", [
                'nama_tempat_tujuan' => 'Kantor Desa Arang Limbung',
                'tbh_hari_jalan_awal' => 1,
            ]);

        $updateRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.lokasi.nama_tempat_tujuan', 'Kantor Desa Arang Limbung');

        $auditLogId = $updateRes->json('audit_log_id');
        $this->assertNotNull($auditLogId);

        // Rollback update
        $rollbackRes = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->postJson("/api/v1/audit-logs/{$auditLogId}/rollback", [
                'reason' => 'Rollback update penugasan test',
            ]);

        $rollbackRes->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $penugasan = Penugasan::find($penugasanId);
        $this->assertEquals('Kantor Desa Limbung', $penugasan->nama_tempat_tujuan);
    }

    public function test_destroy_penugasan_and_rollback(): void
    {
        Setting::updateOrCreate(['key' => 'allow_weekend_travel'], ['value' => '1']);

        $createRes = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->postJson('/api/v1/penugasan', [
                'jenis_surat_tugas' => 'PERJALAN_DINAS_DALAM_KOTA',
                'kegiatan_id' => 'SUSENAS26',
                'nips' => [$this->pegawai1->nip],
                'tgl_mulai_tugas' => '2026-03-16',
                'tgl_akhir_tugas' => '2026-03-18',
                'level_tujuan_penugasan' => 'LEVEL_PENUGASAN_NAMA_TEMPAT',
                'nama_tempat_tujuan' => 'Kecamatan Rasau Jaya',
                'transportasi' => 'TRANSPORTASI_KENDARAAN_DINAS',
            ]);

        $createRes->assertStatus(201);
        $penugasanId = $createRes->json('data.0.id');

        // Delete penugasan
        $delRes = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->deleteJson("/api/v1/penugasan/{$penugasanId}");

        $delRes->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('penugasans', ['id' => $penugasanId]);
        $auditLogId = $delRes->json('audit_log_id');

        // Rollback delete (memulihkan record)
        $rollbackRes = $this->withHeaders(['X-API-KEY' => $this->apiKey])
            ->postJson("/api/v1/audit-logs/{$auditLogId}/rollback", [
                'reason' => 'Rollback penghapusan penugasan test',
            ]);

        $rollbackRes->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('penugasans', ['id' => $penugasanId]);
    }
}
