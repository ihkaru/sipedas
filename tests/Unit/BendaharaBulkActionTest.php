<?php

namespace Tests\Unit;

use App\Models\Pegawai;
use App\Models\Sipancong\Pengajuan;
use App\Services\Sipancong\PengajuanServices;
use App\Supports\SipancongConstants as Constants;
use Database\Seeders\SipancongSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BendaharaBulkActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SipancongSeeder::class);
    }

    public function test_pengaju_relation_returns_pegawai(): void
    {
        $pegawai = Pegawai::create([
            'nip' => '199001012020121001',
            'nip9' => '199001012',
            'nama' => 'Ahmad Fauzi',
            'panggilan' => 'Fauzi',
            'golongan' => 'III/a',
            'pangkat' => 'Penata Muda',
            'jabatan' => 'Pranata Komputer',
            'email' => 'fauzi@bps.go.id',
            'unit_kerja' => 'BPS Kabupaten',
            'nomor_wa' => '6281234567890',
        ]);

        $pengajuan = Pengajuan::create([
            'nomor_pengajuan' => 'PJ-2026-001',
            'tanggal_pengajuan' => '2026-10-08',
            'uraian_pengajuan' => 'Transport Lokal Tim Evaluasi',
            'nominal_pengajuan' => 350000,
            'nip_pengaju' => $pegawai->nip,
            'posisi_dokumen_id' => Constants::POSISI_PENGAJU,
        ]);

        $this->assertNotNull($pengajuan->pengaju);
        $this->assertEquals('Ahmad Fauzi', $pengajuan->pengaju->nama);
        $this->assertEquals('6281234567890', $pengajuan->pengaju->nomor_wa);
    }

    public function test_bulk_pemeriksaan_bendahara_only_affects_eligible_records_in_bendahara_position(): void
    {
        // 1. Record di meja Bendahara dan belum verifikasi (eligible)
        $p1 = Pengajuan::create([
            'nomor_pengajuan' => 'PJ-2026-002',
            'tanggal_pengajuan' => '2026-10-08',
            'uraian_pengajuan' => 'Pengadaan ATK',
            'nominal_pengajuan' => 500000,
            'posisi_dokumen_id' => Constants::POSISI_BENDAHARA,
            'status_pengajuan_ppk_id' => Constants::STATUS_DISETUJUI_TANPA_CATATAN,
            'status_pengajuan_ppspm_id' => Constants::STATUS_DISETUJUI_TANPA_CATATAN,
            'status_pengajuan_bendahara_id' => null,
        ]);

        // 2. Record di meja Bendahara yang pernah ditolak (revisi - eligible)
        $p2 = Pengajuan::create([
            'nomor_pengajuan' => 'PJ-2026-003',
            'tanggal_pengajuan' => '2026-10-08',
            'uraian_pengajuan' => 'Konsumsi Rapat',
            'nominal_pengajuan' => 300000,
            'posisi_dokumen_id' => Constants::POSISI_BENDAHARA,
            'status_pengajuan_ppk_id' => Constants::STATUS_DISETUJUI_TANPA_CATATAN,
            'status_pengajuan_ppspm_id' => Constants::STATUS_DISETUJUI_TANPA_CATATAN,
            'status_pengajuan_bendahara_id' => Constants::STATUS_DITOLAK,
        ]);

        // 3. Record yang masih di meja PPK (tidak boleh disentuh bendahara)
        $p3 = Pengajuan::create([
            'nomor_pengajuan' => 'PJ-2026-004',
            'tanggal_pengajuan' => '2026-10-08',
            'uraian_pengajuan' => 'Perjadin Luar Kota',
            'nominal_pengajuan' => 1200000,
            'posisi_dokumen_id' => Constants::POSISI_PPK,
            'status_pengajuan_ppk_id' => null,
        ]);

        // Eksekusi verifikasi terpilih
        $processed = PengajuanServices::bulkPemeriksaanBendahara(collect([$p1, $p2, $p3]));

        $this->assertEquals(2, $processed);

        // Verifikasi p1 & p2 disetujui
        $freshP1 = $p1->fresh();
        $this->assertEquals(Constants::STATUS_DISETUJUI_TANPA_CATATAN, $freshP1->status_pengajuan_bendahara_id);
        $this->assertEquals(Constants::POSISI_BENDAHARA, $freshP1->posisi_dokumen_id);

        $freshP2 = $p2->fresh();
        $this->assertEquals(Constants::STATUS_DISETUJUI_TANPA_CATATAN, $freshP2->status_pengajuan_bendahara_id);

        // p3 tidak tersentuh sama sekali
        $freshP3 = $p3->fresh();
        $this->assertEquals(Constants::POSISI_PPK, $freshP3->posisi_dokumen_id);
        $this->assertNull($freshP3->status_pengajuan_bendahara_id);
    }

    public function test_bulk_pemrosesan_bendahara_pays_verified_records_and_moves_to_selesai(): void
    {
        // 1. Dokumen yang sudah lolos semua verifikasi (PPK ok, PPSPM ok, Bendahara ok)
        $pVerified = Pengajuan::create([
            'nomor_pengajuan' => 'PJ-2026-005',
            'tanggal_pengajuan' => '2026-10-08',
            'uraian_pengajuan' => 'Honor Tim Evaluasi',
            'nominal_pengajuan' => 1500000,
            'posisi_dokumen_id' => Constants::POSISI_BENDAHARA,
            'status_pengajuan_ppk_id' => Constants::STATUS_DISETUJUI_TANPA_CATATAN,
            'status_pengajuan_ppspm_id' => Constants::STATUS_DISETUJUI_TANPA_CATATAN,
            'status_pengajuan_bendahara_id' => Constants::STATUS_DISETUJUI_TANPA_CATATAN,
        ]);

        // 2. Dokumen di meja bendahara tapi belum diverifikasi oleh bendahara
        $pUnverified = Pengajuan::create([
            'nomor_pengajuan' => 'PJ-2026-006',
            'tanggal_pengajuan' => '2026-10-08',
            'uraian_pengajuan' => 'Honor Tim Survei',
            'nominal_pengajuan' => 800000,
            'posisi_dokumen_id' => Constants::POSISI_BENDAHARA,
            'status_pengajuan_ppk_id' => Constants::STATUS_DISETUJUI_TANPA_CATATAN,
            'status_pengajuan_ppspm_id' => Constants::STATUS_DISETUJUI_TANPA_CATATAN,
            'status_pengajuan_bendahara_id' => null, // belum diverifikasi
        ]);

        $count = PengajuanServices::bulkPemrosesanBendahara(
            records: collect([$pVerified, $pUnverified]),
            data: [
                'status_pembayaran_id' => Constants::PEMBAYARAN_SUDAH_CMS,
                'tanggal_pembayaran' => '2026-10-08',
            ],
            sendNotification: false
        );

        // Hanya 1 record yang lolos kualifikasi pembayaran
        $this->assertEquals(1, $count);

        $freshVerified = $pVerified->fresh();
        $this->assertEquals(Constants::POSISI_SELESAI, $freshVerified->posisi_dokumen_id);
        $this->assertEquals(Constants::PEMBAYARAN_SUDAH_CMS, $freshVerified->status_pembayaran_id);
        $this->assertEquals(1500000, $freshVerified->nominal_dibayarkan);
        $this->assertEquals(0, $freshVerified->nominal_dikembalikan);
        $this->assertEquals('2026-10-08', $freshVerified->tanggal_pembayaran);

        // pUnverified tetap di Bendahara dan belum dibayar
        $freshUnverified = $pUnverified->fresh();
        $this->assertEquals(Constants::POSISI_BENDAHARA, $freshUnverified->posisi_dokumen_id);
        $this->assertNull($freshUnverified->status_pembayaran_id);
    }

    public function test_bulk_pay_bendahara_filters_by_year_and_verification(): void
    {
        $currentYear = (int)now()->year;

        // Record tahun sekarang (verified)
        $pCurrent = Pengajuan::create([
            'nomor_pengajuan' => 'PJ-2026-007',
            'tanggal_pengajuan' => '2026-10-08',
            'uraian_pengajuan' => 'Bahan Makanan Diklat',
            'nominal_pengajuan' => 2000000,
            'posisi_dokumen_id' => Constants::POSISI_BENDAHARA,
            'status_pengajuan_ppk_id' => Constants::STATUS_DISETUJUI_TANPA_CATATAN,
            'status_pengajuan_ppspm_id' => Constants::STATUS_DISETUJUI_TANPA_CATATAN,
            'status_pengajuan_bendahara_id' => Constants::STATUS_DISETUJUI_TANPA_CATATAN,
            'created_at' => now(),
        ]);

        $countPending = PengajuanServices::countPendingPaymentBendahara($currentYear);
        $this->assertGreaterThanOrEqual(1, $countPending);

        $processed = PengajuanServices::bulkPayBendahara(
            year: $currentYear,
            data: [
                'status_pembayaran_id' => Constants::PEMBAYARAN_SUDAH_TUNAI,
                'tanggal_pembayaran' => now()->toDateString(),
            ],
            sendNotification: false
        );

        $this->assertGreaterThanOrEqual(1, $processed);
        $fresh = $pCurrent->fresh();
        $this->assertEquals(Constants::POSISI_SELESAI, $fresh->posisi_dokumen_id);
        $this->assertEquals(Constants::PEMBAYARAN_SUDAH_TUNAI, $fresh->status_pembayaran_id);
        $this->assertEquals(2000000, $fresh->nominal_dibayarkan);
    }
}
