<?php

namespace Tests\Feature;

use App\Models\Kegiatan;
use App\Models\Pegawai;
use App\Models\Pengaturan;
use App\Models\Penugasan;
use App\Models\RiwayatPengajuan;
use App\Models\Setting;
use App\Models\User;
use App\Supports\Constants;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SuratTugasQuickApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected Pegawai $pegawaiKasubbag;

    protected User $userKasubbag;

    protected Pegawai $pegawaiPengaju;

    protected User $userPengaju;

    protected Kegiatan $kegiatan;

    protected Penugasan $penugasan;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'kepala_satker', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'ppspm', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'pegawai', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        Setting::updateOrCreate(['key' => 'st_approval_mode'], ['value' => 'ppspm_only']);
        Setting::updateOrCreate(['key' => 'whatsapp_notification_enabled'], ['value' => '1']);
        config(['services.whatsapp.aina.api_key' => 'test-api-key']);

        $kasubbagNip = '198008112005021004';
        $this->pegawaiKasubbag = Pegawai::factory()->create([
            'nip' => $kasubbagNip,
            'nama' => 'Muhammad Adwin, S.Mn.',
            'panggilan' => 'Bg Adwin',
            'nomor_wa' => '085346332883',
        ]);
        $this->userKasubbag = User::factory()->create(['email' => $this->pegawaiKasubbag->email]);
        $this->userKasubbag->assignRole('ppspm');

        Pengaturan::updateOrCreate(
            ['key' => 'NIP_KASUBBAG_TU'],
            ['nilai' => $kasubbagNip, 'deskripsi' => 'Kasubbag TU']
        );

        $this->pegawaiPengaju = Pegawai::factory()->create([
            'nip' => '199801012022032001',
            'nama' => 'Sarah Pratiwi',
            'panggilan' => 'Kak Sarah',
            'nomor_wa' => '081234567899',
        ]);
        $this->userPengaju = User::factory()->create(['email' => $this->pegawaiPengaju->email]);
        $this->userPengaju->assignRole('pegawai');

        $this->kegiatan = Kegiatan::factory()->create(['id' => 999111, 'nama' => 'Survei SHKK 2026']);

        $this->penugasan = Penugasan::create([
            'nip' => $this->pegawaiPengaju->nip,
            'kegiatan_id' => $this->kegiatan->id,
            'nip_pengaju' => $this->pegawaiPengaju->nip,
            'level_tujuan_penugasan' => Constants::LEVEL_PENUGASAN_TANPA_LOKASI,
            'nama_tempat_tujuan' => 'Mempawah Timur',
            'tgl_mulai_tugas' => now()->addDay()->toDateString(),
            'tgl_akhir_tugas' => now()->addDay()->toDateString(),
            'tgl_pengajuan_tugas' => now()->toDateString(),
            'jenis_peserta' => Constants::JENIS_PESERTA_SURAT_TUGAS_PEGAWAI,
            'grup_id' => 778899,
            'jenis_surat_tugas' => Constants::NON_SPPD,
            'plh_id' => $this->pegawaiKasubbag->nip,
        ]);

        RiwayatPengajuan::create([
            'penugasan_id' => $this->penugasan->id,
            'status' => Constants::STATUS_PENGAJUAN_DIKIRIM,
        ]);
    }

    public function test_crawler_bot_preview_does_not_execute_approval(): void
    {
        Http::fake();

        $signedUrl = URL::temporarySignedRoute(
            'surat-tugas.quick-approve',
            now()->addDays(7),
            ['grup_id' => $this->penugasan->grup_id, 'nip' => $this->pegawaiKasubbag->nip]
        );

        // Simulasi request dari WhatsApp Link Preview bot
        $response = $this->withHeaders([
            'User-Agent' => 'WhatsApp/2.23.20.0 i',
        ])->get($signedUrl);

        $response->assertStatus(200);
        $response->assertSee('Persetujuan Cepat Surat Tugas — Dokter V');

        // Status penugasan WAJIB tetap DIKIRIM (tidak boleh auto-approved oleh bot preview)
        $this->penugasan->refresh();
        $this->assertEquals(Constants::STATUS_PENGAJUAN_DIKIRIM, $this->penugasan->riwayatPengajuan->status);
        $this->assertNull($this->penugasan->surat_tugas_id);
    }

    public function test_valid_signed_url_approves_penugasan_directly_without_login(): void
    {
        Http::fake([
            '*' => Http::response(['status' => 'success'], 200),
        ]);

        $signedUrl = URL::temporarySignedRoute(
            'surat-tugas.quick-approve',
            now()->addDays(7),
            ['grup_id' => $this->penugasan->grup_id, 'nip' => $this->pegawaiKasubbag->nip]
        );

        // Akses oleh browser manusia (tanpa sesi login / unauthenticated)
        $response = $this->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148 Safari/604.1',
        ])->get($signedUrl);

        $response->assertStatus(200);
        $response->assertSee('Surat Tugas Berhasil Disetujui!');
        $response->assertSee('Bg Adwin');

        // Verifikasi database: status menjadi disetujui & nomor ST terisi
        $this->penugasan->refresh();
        $this->assertEquals(Constants::STATUS_PENGAJUAN_DISETUJUI, $this->penugasan->riwayatPengajuan->status);
        $this->assertNotNull($this->penugasan->surat_tugas_id);

        // Verifikasi notifikasi WhatsApp persetujuan terkirim
        Http::assertSent(function ($request) {
            return str_contains($request->body(), 'Surat Tugas Disetujui') &&
                   str_contains($request->body(), 'Bg Adwin');
        });
    }

    public function test_already_approved_link_shows_friendly_status(): void
    {
        $signedUrl = URL::temporarySignedRoute(
            'surat-tugas.quick-approve',
            now()->addDays(7),
            ['grup_id' => $this->penugasan->grup_id, 'nip' => $this->pegawaiKasubbag->nip]
        );

        // Setujui sekali
        $this->withHeaders(['User-Agent' => 'Mozilla/5.0'])->get($signedUrl);

        // Buka untuk kedua kali
        $response = $this->withHeaders(['User-Agent' => 'Mozilla/5.0'])->get($signedUrl);

        $response->assertStatus(200);
        $response->assertSee('Surat Tugas Sudah Disetujui');
    }

    public function test_tampered_or_invalid_signature_is_rejected(): void
    {
        $signedUrl = URL::temporarySignedRoute(
            'surat-tugas.quick-approve',
            now()->addDays(7),
            ['grup_id' => $this->penugasan->grup_id, 'nip' => $this->pegawaiKasubbag->nip]
        );

        // Modifikasi URL untuk merusak signature
        $tamperedUrl = $signedUrl.'corrupted';

        $response = $this->withHeaders(['User-Agent' => 'Mozilla/5.0'])->get($tamperedUrl);

        $response->assertStatus(200);
        $response->assertSee('Tautan Tidak Valid atau Kedaluwarsa');

        // Status tidak berubah
        $this->penugasan->refresh();
        $this->assertEquals(Constants::STATUS_PENGAJUAN_DIKIRIM, $this->penugasan->riwayatPengajuan->status);
    }

    public function test_unauthorized_nip_cannot_approve(): void
    {
        // NIP bukan approver (Kak Sarah yang mencoba signed link atas NIP dirinya sendiri)
        $signedUrl = URL::temporarySignedRoute(
            'surat-tugas.quick-approve',
            now()->addDays(7),
            ['grup_id' => $this->penugasan->grup_id, 'nip' => $this->pegawaiPengaju->nip]
        );

        $response = $this->withHeaders(['User-Agent' => 'Mozilla/5.0'])->get($signedUrl);

        $response->assertStatus(200);
        $response->assertSee('Wewenang Tidak Sesuai');

        $this->penugasan->refresh();
        $this->assertEquals(Constants::STATUS_PENGAJUAN_DIKIRIM, $this->penugasan->riwayatPengajuan->status);
    }
}
