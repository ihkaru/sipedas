<?php

namespace Tests\Unit;

use App\Models\Kegiatan;
use App\Models\Pegawai;
use App\Models\Pengaturan;
use App\Models\Penugasan;
use App\Models\RiwayatPengajuan;
use App\Models\Setting;
use App\Models\User;
use App\Services\SuratTugas\SuratTugasNotificationService;
use App\Services\WhatsappNotifier;
use App\Supports\Constants;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SuratTugasApprovalDelegationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'kepala_satker', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'ppspm', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'pegawai', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    }

    public function test_is_user_approver_with_delegasi_ppspm_mode(): void
    {
        Setting::updateOrCreate(['key' => 'st_approval_mode'], ['value' => 'delegasi_ppspm']);

        $userKepala = User::factory()->create();
        $userKepala->assignRole('kepala_satker');

        $userPpspm = User::factory()->create();
        $userPpspm->assignRole('ppspm');

        $userPegawai = User::factory()->create();
        $userPegawai->assignRole('pegawai');

        // Dual approval: both Kepala Satker and PPSPM/Kasubbag can approve
        $this->assertTrue(Penugasan::isUserApprover($userKepala));
        $this->assertTrue(Penugasan::isUserApprover($userPpspm));
        $this->assertFalse(Penugasan::isUserApprover($userPegawai));
    }

    public function test_is_user_approver_with_kepala_only_mode(): void
    {
        Setting::updateOrCreate(['key' => 'st_approval_mode'], ['value' => 'kepala_only']);

        $userKepala = User::factory()->create();
        $userKepala->assignRole('kepala_satker');

        $userPpspm = User::factory()->create();
        $userPpspm->assignRole('ppspm');

        // In kepala_only mode, PPSPM cannot approve
        $this->assertTrue(Penugasan::isUserApprover($userKepala));
        $this->assertFalse(Penugasan::isUserApprover($userPpspm));
    }

    public function test_is_user_approver_with_ppspm_only_mode(): void
    {
        Setting::updateOrCreate(['key' => 'st_approval_mode'], ['value' => 'ppspm_only']);

        $userKepala = User::factory()->create();
        $userKepala->assignRole('kepala_satker');

        $userPpspm = User::factory()->create();
        $userPpspm->assignRole('ppspm');

        // In ppspm_only mode, PPSPM can approve, Kepala cannot
        $this->assertFalse(Penugasan::isUserApprover($userKepala));
        $this->assertTrue(Penugasan::isUserApprover($userPpspm));
    }

    public function test_plh_assigned_nip_can_always_approve(): void
    {
        Setting::updateOrCreate(['key' => 'st_approval_mode'], ['value' => 'kepala_only']);

        $pegawaiPlh = Pegawai::factory()->create(['nip' => '199001012015011001']);
        $userPlh = User::factory()->create(['email' => $pegawaiPlh->email]);
        $userPlh->assignRole('pegawai'); // Only pegawai role

        // When plh_id matches, this user can approve regardless of st_approval_mode
        $this->assertTrue(Penugasan::isUserApprover($userPlh, '199001012015011001'));
        $this->assertFalse(Penugasan::isUserApprover($userPlh, '198808082010011002'));
    }

    public function test_surat_tugas_event_settings_can_suppress_notification(): void
    {
        Setting::updateOrCreate(['key' => 'whatsapp_notification_enabled'], ['value' => '1']);
        Setting::updateOrCreate(['key' => 'whatsapp_notify_st_baru'], ['value' => '0']);
        Setting::updateOrCreate(['key' => 'whatsapp_notify_st_disetujui'], ['value' => '1']);

        $this->assertFalse(WhatsappNotifier::isEventEnabled('whatsapp_notify_st_baru'));
        $this->assertTrue(WhatsappNotifier::isEventEnabled('whatsapp_notify_st_disetujui'));
        $this->assertTrue(WhatsappNotifier::isEventEnabled('whatsapp_notify_st_revisi'));
        $this->assertTrue(WhatsappNotifier::isEventEnabled('whatsapp_notify_st_ditolak'));
    }

    public function test_notification_service_dispatches_whatsapp_message(): void
    {
        Http::fake([
            '*' => Http::response(['status' => 'success'], 200),
        ]);

        config(['services.whatsapp.aina.api_key' => 'test-api-key']);
        Setting::updateOrCreate(['key' => 'whatsapp_notification_enabled'], ['value' => '1']);
        Setting::updateOrCreate(['key' => 'st_approval_mode'], ['value' => 'delegasi_ppspm']);

        // Create Pegawai and User for PPSPM
        $pegawaiPpspm = Pegawai::factory()->create([
            'nama' => 'Kasubbag PPSPM',
            'panggilan' => 'Pak Kasubbag',
            'nomor_wa' => '081234567890',
        ]);
        $userPpspm = User::factory()->create(['email' => $pegawaiPpspm->email]);
        $userPpspm->assignRole('ppspm');

        // Create Pengaju
        $pegawaiPengaju = Pegawai::factory()->create([
            'nama' => 'Staff Pengaju',
            'panggilan' => 'Staff',
            'nomor_wa' => '089876543210',
        ]);

        $kegiatan = Kegiatan::factory()->create(['id' => 998877, 'nama' => 'Survei Evaluasi']);

        $penugasan = Penugasan::create([
            'nip' => $pegawaiPengaju->nip,
            'kegiatan_id' => $kegiatan->id,
            'nip_pengaju' => $pegawaiPengaju->nip,
            'level_tujuan_penugasan' => Constants::LEVEL_PENUGASAN_KECAMATAN,
            'nama_tempat_tujuan' => 'Kecamatan Mempawah Hilir',
            'tgl_mulai_tugas' => now()->toDateString(),
            'tgl_akhir_tugas' => now()->toDateString(),
            'tgl_pengajuan_tugas' => now()->toDateString(),
            'jenis_peserta' => Constants::JENIS_PESERTA_SURAT_TUGAS_PEGAWAI,
            'grup_id' => 12345,
            'jenis_surat_tugas' => Constants::PERJALAN_DINAS_DALAM_KOTA,
            'plh_id' => $pegawaiPpspm->nip,
        ]);

        RiwayatPengajuan::create([
            'penugasan_id' => $penugasan->id,
            'status' => Constants::STATUS_PENGAJUAN_DIKIRIM,
        ]);

        SuratTugasNotificationService::notifyPengajuanBaru($penugasan);

        Http::assertSent(function ($request) {
            return str_contains($request->body(), 'Pengajuan Surat Tugas Baru') &&
                   str_contains($request->body(), 'Pak Kasubbag');
        });
    }

    public function test_notification_service_dispatches_disetujui_revisi_and_ditolak(): void
    {
        Http::fake([
            '*' => Http::response(['status' => 'success'], 200),
        ]);

        config(['services.whatsapp.aina.api_key' => 'test-api-key']);
        Setting::updateOrCreate(['key' => 'whatsapp_notification_enabled'], ['value' => '1']);

        $pegawaiPengaju = Pegawai::factory()->create([
            'nama' => 'Staff Pengaju',
            'panggilan' => 'Staff',
            'nomor_wa' => '089876543210',
        ]);

        $kegiatan = Kegiatan::factory()->create(['id' => 998878, 'nama' => 'Pelatihan Susenas']);

        $penugasan = Penugasan::create([
            'nip' => $pegawaiPengaju->nip,
            'kegiatan_id' => $kegiatan->id,
            'nip_pengaju' => $pegawaiPengaju->nip,
            'level_tujuan_penugasan' => Constants::LEVEL_PENUGASAN_KECAMATAN,
            'nama_tempat_tujuan' => 'Kecamatan Mempawah Timur',
            'tgl_mulai_tugas' => now()->toDateString(),
            'tgl_akhir_tugas' => now()->toDateString(),
            'tgl_pengajuan_tugas' => now()->toDateString(),
            'jenis_peserta' => Constants::JENIS_PESERTA_SURAT_TUGAS_PEGAWAI,
            'grup_id' => 12346,
            'jenis_surat_tugas' => Constants::PERJALAN_DINAS_DALAM_KOTA,
            'plh_id' => $pegawaiPengaju->nip,
        ]);

        RiwayatPengajuan::create([
            'penugasan_id' => $penugasan->id,
            'status' => Constants::STATUS_PENGAJUAN_DIKIRIM,
        ]);

        // 1. Test Revisi
        SuratTugasNotificationService::notifyPerluRevisi($penugasan, 'Mohon ganti lokasi ke kantor desa', 'Kasubbag');
        Http::assertSent(function ($request) {
            return str_contains($request->body(), 'Perlu Perbaikan Surat Tugas') &&
                   str_contains($request->body(), 'Mohon ganti lokasi ke kantor desa');
        });

        // 2. Test Disetujui
        SuratTugasNotificationService::notifyDisetujui($penugasan, 'Kasubbag');
        Http::assertSent(function ($request) {
            return str_contains($request->body(), 'Surat Tugas Disetujui') &&
                   str_contains($request->body(), 'Pelatihan Susenas');
        });

        // 3. Test Ditolak
        SuratTugasNotificationService::notifyDitolak($penugasan, 'Kasubbag');
        Http::assertSent(function ($request) {
            return str_contains($request->body(), 'Surat Tugas Ditolak');
        });
    }

    public function test_is_user_approver_recognizes_kasubbag_by_configured_nip_fallback(): void
    {
        Setting::updateOrCreate(['key' => 'st_approval_mode'], ['value' => 'ppspm_only']);

        $kasubbagNip = '198008112005021004';
        $pegawaiKasubbag = Pegawai::factory()->create([
            'nip' => $kasubbagNip,
            'nama' => 'Muhammad Adwin, S.Mn.',
            'panggilan' => 'Bg Adwin',
        ]);
        $userKasubbag = User::factory()->create(['email' => $pegawaiKasubbag->email]);
        // Note: user does NOT have the Spatie 'ppspm' role, only 'pegawai'
        $userKasubbag->assignRole('pegawai');

        Pengaturan::updateOrCreate(
            ['key' => 'NIP_KASUBBAG_TU'],
            ['nilai' => $kasubbagNip, 'deskripsi' => 'Kasubbag TU']
        );

        $this->assertTrue(Penugasan::isUserApprover($userKasubbag));
    }

    public function test_non_sppd_requires_approval_and_notifies_ppspm_in_follow_global_mode(): void
    {
        Http::fake([
            '*' => Http::response(['status' => 'success'], 200),
        ]);

        config(['services.whatsapp.aina.api_key' => 'test-api-key']);
        Setting::updateOrCreate(['key' => 'whatsapp_notification_enabled'], ['value' => '1']);
        Setting::updateOrCreate(['key' => 'st_approval_mode'], ['value' => 'ppspm_only']);
        Setting::updateOrCreate(['key' => 'st_non_sppd_mode'], ['value' => 'follow_global']);

        $kasubbagNip = '198008112005021004';
        $pegawaiKasubbag = Pegawai::factory()->create([
            'nip' => $kasubbagNip,
            'nama' => 'Muhammad Adwin, S.Mn.',
            'panggilan' => 'Bg Adwin',
            'nomor_wa' => '085346332883',
        ]);
        $userKasubbag = User::factory()->create(['email' => $pegawaiKasubbag->email]);
        $userKasubbag->assignRole('ppspm');

        // Submitter: Regular employee (Kak Sarah)
        $pegawaiSubmitter = Pegawai::factory()->create([
            'nip' => '199801012022032001',
            'nama' => 'Sarah Pratiwi',
            'panggilan' => 'Kak Sarah',
            'nomor_wa' => '081234567899',
        ]);
        $userSubmitter = User::factory()->create(['email' => $pegawaiSubmitter->email]);
        $userSubmitter->assignRole('pegawai');

        $this->actingAs($userSubmitter);

        // Assignee: Budiman Aller
        $pegawaiTertugas = Pegawai::factory()->create([
            'nip' => '199705052021021001',
            'nama' => 'Budiman Aller Silaban',
            'panggilan' => 'Bg Aller',
            'nomor_wa' => '081122334455',
        ]);

        $kegiatan = Kegiatan::factory()->create(['id' => 998879, 'nama' => 'Survei SHKK 2026']);

        $dataPengajuan = [
            'nama_kegiatan' => 'Survei SHKK 2026',
            'level_tujuan' => Constants::LEVEL_PENUGASAN_TANPA_LOKASI,
            'lokasi' => 'Mempawah Timur',
            'tanggal_mulai' => now()->addDay()->toDateString(),
            'tanggal_selesai' => now()->addDay()->toDateString(),
            'jenis_petugas' => 'PEGAWAI',
            'apakah_sppd' => 0, // NON_SPPD
            'nips' => [$pegawaiTertugas->nip],
            'mitras' => [],
        ];

        $success = Penugasan::ajukan($dataPengajuan);
        $this->assertTrue($success);

        $penugasan = Penugasan::where('nip', $pegawaiTertugas->nip)->latest('id')->first();
        $this->assertNotNull($penugasan);

        // Must NOT be auto-approved
        $this->assertEquals(Constants::STATUS_PENGAJUAN_DIKIRIM, $penugasan->riwayatPengajuan->status);

        // Notification must have been sent to Kasubbag TU / PPSPM (Bg Adwin)
        Http::assertSent(function ($request) {
            return str_contains($request->body(), 'Pengajuan Surat Tugas Baru') &&
                   str_contains($request->body(), '6285346332883');
        });

        // Kak Sarah cannot approve
        $this->assertFalse($penugasan->canSetujui());

        // Acting as Kasubbag TU (Bg Adwin) can approve
        $this->actingAs($userKasubbag);
        $this->assertTrue($penugasan->canSetujui());

        $penugasan->setujui();
        $penugasan->refresh();
        $this->assertEquals(Constants::STATUS_PENGAJUAN_DISETUJUI, $penugasan->riwayatPengajuan->status);

        // Approval notification must cite Bg Adwin, NOT Kak Sarah!
        Http::assertSent(function ($request) {
            return str_contains($request->body(), 'Surat Tugas Disetujui') &&
                   str_contains($request->body(), 'Bg Adwin') &&
                   ! str_contains($request->body(), 'disetujui oleh Kak Sarah');
        });
    }

    public function test_non_sppd_auto_approves_by_default_and_notifies_kasubbag_tu_only(): void
    {
        Http::fake([
            '*' => Http::response(['status' => 'success'], 200),
        ]);

        config(['services.whatsapp.aina.api_key' => 'test-api-key']);
        Setting::updateOrCreate(['key' => 'whatsapp_notification_enabled'], ['value' => '1']);
        Setting::updateOrCreate(['key' => 'st_approval_mode'], ['value' => 'delegasi_ppspm']);
        Setting::updateOrCreate(['key' => 'whatsapp_notify_st_tembusan_kepala'], ['value' => '1']);
        // Default mode: auto_approve_notify

        $kasubbagNip = '198008112005021004';
        $pegawaiKasubbag = Pegawai::factory()->create([
            'nip' => $kasubbagNip,
            'nama' => 'Muhammad Adwin, S.Mn.',
            'panggilan' => 'Bg Adwin',
            'nomor_wa' => '085346332883',
        ]);
        $userKasubbag = User::factory()->create(['email' => $pegawaiKasubbag->email]);
        $userKasubbag->assignRole('ppspm');

        $kepalaNip = '196908031992111001';
        $pegawaiKepala = Pegawai::factory()->create([
            'nip' => $kepalaNip,
            'nama' => 'Munawir, S.Si, M.M.',
            'panggilan' => 'Pak Munawir',
            'nomor_wa' => '081399887766',
        ]);
        $userKepala = User::factory()->create(['email' => $pegawaiKepala->email]);
        $userKepala->assignRole('kepala_satker');

        // Submitter: Regular employee (Kak Sarah)
        $pegawaiSubmitter = Pegawai::factory()->create([
            'nip' => '199801012022032002',
            'nama' => 'Sarah Pratiwi',
            'panggilan' => 'Kak Sarah',
            'nomor_wa' => '081234567899',
        ]);
        $userSubmitter = User::factory()->create(['email' => $pegawaiSubmitter->email]);
        $userSubmitter->assignRole('pegawai');

        $this->actingAs($userSubmitter);

        // Assignee: Budiman Aller
        $pegawaiTertugas = Pegawai::factory()->create([
            'nip' => '199705052021021002',
            'nama' => 'Budiman Aller Silaban',
            'panggilan' => 'Bg Aller',
            'nomor_wa' => '081122334455',
        ]);

        $kegiatan = Kegiatan::factory()->create(['id' => 998880, 'nama' => 'Rapat Internal SOTK']);

        $dataPengajuan = [
            'nama_kegiatan' => 'Rapat Internal SOTK',
            'level_tujuan' => Constants::LEVEL_PENUGASAN_TANPA_LOKASI,
            'lokasi' => 'Kantor BPS',
            'tanggal_mulai' => now()->addDay()->toDateString(),
            'tanggal_selesai' => now()->addDay()->toDateString(),
            'jenis_petugas' => 'PEGAWAI',
            'apakah_sppd' => 0, // NON_SPPD
            'nips' => [$pegawaiTertugas->nip],
            'mitras' => [],
        ];

        $success = Penugasan::ajukan($dataPengajuan);
        $this->assertTrue($success);

        $penugasan = Penugasan::where('nip', $pegawaiTertugas->nip)->latest('id')->first();
        $this->assertNotNull($penugasan);

        // 1. MUST be auto-approved
        $this->assertEquals(Constants::STATUS_PENGAJUAN_DISETUJUI, $penugasan->riwayatPengajuan->status);
        $this->assertNotNull($penugasan->surat_tugas_id);
        $this->assertNull($penugasan->surat_perjadin_id);

        // 2. Notification must be sent to Kasubbag TU ONLY (informational, not requesting approval)
        Http::assertSent(function ($request) {
            return str_contains($request->body(), 'Pemberitahuan Surat Tugas Non-SPPD') &&
                   str_contains($request->body(), '6285346332883') &&
                   str_contains($request->body(), 'disetujui otomatis') &&
                   ! str_contains($request->body(), 'Persetujuan Cepat (1-Klik)');
        });

        // 3. Kepala Satker must NOT receive notification for Non-SPPD auto-approve
        Http::assertNotSent(function ($request) {
            return str_contains($request->body(), '6281399887766');
        });

        // 4. Submitter & Personil must receive approval notification citing 'Sistem (Otomatis)'
        Http::assertSent(function ($request) {
            return str_contains($request->body(), 'Surat Tugas Disetujui') &&
                   str_contains($request->body(), 'Sistem (Otomatis)') &&
                   (str_contains($request->body(), '6281234567899') || str_contains($request->body(), '6281122334455'));
        });
    }

    public function test_non_sppd_auto_approve_silent_mode(): void
    {
        Http::fake([
            '*' => Http::response(['status' => 'success'], 200),
        ]);

        config(['services.whatsapp.aina.api_key' => 'test-api-key']);
        Setting::updateOrCreate(['key' => 'whatsapp_notification_enabled'], ['value' => '1']);
        Setting::updateOrCreate(['key' => 'st_approval_mode'], ['value' => 'delegasi_ppspm']);
        Setting::updateOrCreate(['key' => 'st_non_sppd_mode'], ['value' => 'auto_approve_silent']);

        $kasubbagNip = '198008112005021004';
        $pegawaiKasubbag = Pegawai::factory()->create([
            'nip' => $kasubbagNip,
            'nama' => 'Muhammad Adwin, S.Mn.',
            'panggilan' => 'Bg Adwin',
            'nomor_wa' => '085346332883',
        ]);
        $userKasubbag = User::factory()->create(['email' => $pegawaiKasubbag->email]);
        $userKasubbag->assignRole('ppspm');

        $pegawaiSubmitter = Pegawai::factory()->create([
            'nip' => '199801012022032003',
            'nama' => 'Sarah Pratiwi',
            'panggilan' => 'Kak Sarah',
            'nomor_wa' => '081234567899',
        ]);
        $userSubmitter = User::factory()->create(['email' => $pegawaiSubmitter->email]);
        $userSubmitter->assignRole('pegawai');

        $this->actingAs($userSubmitter);

        $kegiatan = Kegiatan::factory()->create(['id' => 998881, 'nama' => 'Evaluasi Bulanan']);

        $dataPengajuan = [
            'nama_kegiatan' => 'Evaluasi Bulanan',
            'level_tujuan' => Constants::LEVEL_PENUGASAN_TANPA_LOKASI,
            'lokasi' => 'Ruang Rapat',
            'tanggal_mulai' => now()->addDay()->toDateString(),
            'tanggal_selesai' => now()->addDay()->toDateString(),
            'jenis_petugas' => 'PEGAWAI',
            'apakah_sppd' => 0, // NON_SPPD
            'nips' => [$pegawaiSubmitter->nip],
            'mitras' => [],
        ];

        $success = Penugasan::ajukan($dataPengajuan);
        $this->assertTrue($success);

        $penugasan = Penugasan::where('nip', $pegawaiSubmitter->nip)->latest('id')->first();
        $this->assertEquals(Constants::STATUS_PENGAJUAN_DISETUJUI, $penugasan->riwayatPengajuan->status);

        // Kasubbag TU must NOT receive any message in silent mode
        Http::assertNotSent(function ($request) {
            return str_contains($request->body(), '6285346332883');
        });
    }

    public function test_sppd_still_requires_approval_when_non_sppd_is_auto_approve_notify(): void
    {
        Http::fake([
            '*' => Http::response(['status' => 'success'], 200),
        ]);

        config(['services.whatsapp.aina.api_key' => 'test-api-key']);
        Setting::updateOrCreate(['key' => 'whatsapp_notification_enabled'], ['value' => '1']);
        Setting::updateOrCreate(['key' => 'st_approval_mode'], ['value' => 'ppspm_only']);
        Setting::updateOrCreate(['key' => 'st_non_sppd_mode'], ['value' => 'auto_approve_notify']);

        $kasubbagNip = '198008112005021004';
        $pegawaiKasubbag = Pegawai::factory()->create([
            'nip' => $kasubbagNip,
            'nama' => 'Muhammad Adwin, S.Mn.',
            'panggilan' => 'Bg Adwin',
            'nomor_wa' => '085346332883',
        ]);
        $userKasubbag = User::factory()->create(['email' => $pegawaiKasubbag->email]);
        $userKasubbag->assignRole('ppspm');

        $pegawaiSubmitter = Pegawai::factory()->create([
            'nip' => '199801012022032004',
            'nama' => 'Sarah Pratiwi',
            'panggilan' => 'Kak Sarah',
            'nomor_wa' => '081234567899',
        ]);
        $userSubmitter = User::factory()->create(['email' => $pegawaiSubmitter->email]);
        $userSubmitter->assignRole('pegawai');

        $this->actingAs($userSubmitter);

        $kegiatan = Kegiatan::factory()->create(['id' => 998882, 'nama' => 'Supervisi Lapangan']);

        $dataPengajuan = [
            'nama_kegiatan' => 'Supervisi Lapangan',
            'level_tujuan' => Constants::LEVEL_PENUGASAN_TANPA_LOKASI,
            'lokasi' => 'Kecamatan Anjongan',
            'tanggal_mulai' => now()->addDay()->toDateString(),
            'tanggal_selesai' => now()->addDay()->toDateString(),
            'jenis_petugas' => 'PEGAWAI',
            'apakah_sppd' => 1, // SPPD!
            'nips' => [$pegawaiSubmitter->nip],
            'mitras' => [],
        ];

        $success = Penugasan::ajukan($dataPengajuan);
        $this->assertTrue($success);

        $penugasan = Penugasan::where('nip', $pegawaiSubmitter->nip)->latest('id')->first();
        $this->assertNotNull($penugasan);

        // SPPD must remain in DIKIRIM (requires approval)
        $this->assertEquals(Constants::STATUS_PENGAJUAN_DIKIRIM, $penugasan->riwayatPengajuan->status);

        // Notification must ask for approval (with quick approval link)
        Http::assertSent(function ($request) {
            return str_contains($request->body(), 'Pengajuan Surat Tugas Baru') &&
                   str_contains($request->body(), '6285346332883') &&
                   str_contains($request->body(), 'Persetujuan Cepat (1-Klik)');
        });
    }
}
