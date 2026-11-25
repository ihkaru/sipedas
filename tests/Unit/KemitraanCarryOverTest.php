<?php

namespace Tests\Unit;

use App\Imports\MitraImport;
use App\Models\Honor;
use App\Models\KegiatanManmit;
use App\Models\Kemitraan;
use App\Models\Mitra;
use App\Models\Setting;
use App\Services\HonorAllocationService;
use App\Services\KemitraanCarryOverService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KemitraanCarryOverTest extends TestCase
{
    use RefreshDatabase;

    public function test_carry_over_creates_provisional_2027_and_skips_blacklisted_or_existing(): void
    {
        $mitraAktif = Mitra::create(['id_sobat' => '61040001', 'nama_1' => 'Mitra Pelatihan']);
        Kemitraan::create(['mitra_id' => $mitraAktif->id, 'tahun' => 2026, 'status' => Kemitraan::STATUS_AKTIF]);

        $mitraBlacklisted = Mitra::create(['id_sobat' => '61040002', 'nama_1' => 'Mitra Blacklisted']);
        Kemitraan::create(['mitra_id' => $mitraBlacklisted->id, 'tahun' => 2026, 'status' => Kemitraan::STATUS_BLACKLISTED]);

        $mitraExisting = Mitra::create(['id_sobat' => '61040003', 'nama_1' => 'Mitra Sudah Ada']);
        Kemitraan::create(['mitra_id' => $mitraExisting->id, 'tahun' => 2026, 'status' => Kemitraan::STATUS_AKTIF]);
        Kemitraan::create([
            'mitra_id' => $mitraExisting->id,
            'tahun' => 2027,
            'status' => Kemitraan::STATUS_AKTIF,
            'status_rekrutmen' => Kemitraan::REKRUTMEN_TERVERIFIKASI,
        ]);

        $service = app(KemitraanCarryOverService::class);
        $result = $service->carryOver(
            collect([$mitraAktif, $mitraBlacklisted, $mitraExisting]),
            2026,
            2027,
            Kemitraan::REKRUTMEN_BELUM,
            'Peserta pelatihan 2026 untuk kegiatan 2027'
        );

        $this->assertSame([
            'created' => 1,
            'skipped_existing' => 1,
            'skipped_blacklisted' => 1,
        ], $result);

        $this->assertDatabaseHas('kemitraans', [
            'mitra_id' => $mitraAktif->id,
            'tahun' => 2027,
            'status' => Kemitraan::STATUS_AKTIF,
            'status_rekrutmen' => Kemitraan::REKRUTMEN_BELUM,
            'catatan' => 'Peserta pelatihan 2026 untuk kegiatan 2027',
        ]);

        $this->assertDatabaseMissing('kemitraans', [
            'mitra_id' => $mitraBlacklisted->id,
            'tahun' => 2027,
        ]);
    }

    public function test_mitra_badge_highlights_belum_rekrutmen_status(): void
    {
        $mitra = Mitra::create(['id_sobat' => '61040010', 'nama_1' => 'Siti Aminah']);
        Kemitraan::create([
            'mitra_id' => $mitra->id,
            'tahun' => 2027,
            'status' => Kemitraan::STATUS_AKTIF,
            'status_rekrutmen' => Kemitraan::REKRUTMEN_BELUM,
            'catatan' => 'Belum lulus rekrutmen 2027',
        ]);

        $badgeHtml = $mitra->fresh('kemitraans')->kemitraans_status;

        $this->assertStringContainsString('fi-color-warning', $badgeHtml);
        $this->assertStringContainsString('2027: AKTIF (Belum Rekrutmen)', $badgeHtml);
        $this->assertStringContainsString('Belum lulus rekrutmen 2027', $badgeHtml);
    }

    public function test_mitra_import_upgrades_provisional_to_terverifikasi(): void
    {
        $mitra = Mitra::create(['id_sobat' => '61040020', 'nama_1' => 'Andi Pratama']);
        Kemitraan::create([
            'mitra_id' => $mitra->id,
            'tahun' => 2027,
            'status' => Kemitraan::STATUS_AKTIF,
            'status_rekrutmen' => Kemitraan::REKRUTMEN_BELUM,
            'catatan' => 'Carry-over sementara',
        ]);

        $importer = new MitraImport(2027, Kemitraan::STATUS_AKTIF, Kemitraan::REKRUTMEN_TERVERIFIKASI);
        $importer->model([
            'sobat_id' => '61040020',
            'nama_lengkap' => 'Andi Pratama',
        ]);

        $this->assertDatabaseHas('kemitraans', [
            'mitra_id' => $mitra->id,
            'tahun' => 2027,
            'status' => Kemitraan::STATUS_AKTIF,
            'status_rekrutmen' => Kemitraan::REKRUTMEN_TERVERIFIKASI,
            'catatan' => null,
        ]);
    }

    public function test_reconcile_non_verified_deactivates_remaining_provisional_records(): void
    {
        $mitraGagal = Mitra::create(['id_sobat' => '61040030', 'nama_1' => 'Budi Gagal']);
        Kemitraan::create([
            'mitra_id' => $mitraGagal->id,
            'tahun' => 2027,
            'status' => Kemitraan::STATUS_AKTIF,
            'status_rekrutmen' => Kemitraan::REKRUTMEN_BELUM,
            'catatan' => 'Carry-over SPK Pelatihan',
        ]);

        $mitraLulus = Mitra::create(['id_sobat' => '61040031', 'nama_1' => 'Cici Lulus']);
        Kemitraan::create([
            'mitra_id' => $mitraLulus->id,
            'tahun' => 2027,
            'status' => Kemitraan::STATUS_AKTIF,
            'status_rekrutmen' => Kemitraan::REKRUTMEN_TERVERIFIKASI,
        ]);

        $affected = app(KemitraanCarryOverService::class)->reconcileNonVerified(2027);

        $this->assertSame(1, $affected);
        $this->assertDatabaseHas('kemitraans', [
            'mitra_id' => $mitraGagal->id,
            'tahun' => 2027,
            'status' => Kemitraan::STATUS_TIDAK_AKTIF,
            'status_rekrutmen' => Kemitraan::REKRUTMEN_TIDAK_LULUS,
        ]);
        $this->assertDatabaseHas('kemitraans', [
            'mitra_id' => $mitraLulus->id,
            'tahun' => 2027,
            'status' => Kemitraan::STATUS_AKTIF,
            'status_rekrutmen' => Kemitraan::REKRUTMEN_TERVERIFIKASI,
        ]);
    }

    public function test_provisional_mitra_remains_eligible_for_2027_spk_allocation(): void
    {
        Setting::updateOrCreate(
            ['key' => 'STANDAR_BIAYA_MASUKAN_LAINNYA_NON_PNS_OB_PETUGAS_PENDATAAN_LAPANGAN_SURVEI'],
            ['value' => 3353000]
        );

        $mitra = Mitra::create(['id_sobat' => '61040040', 'nama_1' => 'Dedi Pelatihan']);
        Kemitraan::create([
            'mitra_id' => $mitra->id,
            'tahun' => 2027,
            'status' => Kemitraan::STATUS_AKTIF,
            'status_rekrutmen' => Kemitraan::REKRUTMEN_BELUM,
        ]);

        $kegiatan = KegiatanManmit::create([
            'id' => 'KEG-2027-001',
            'nama' => 'Pelatihan Petugas 2027',
            'jenis_kegiatan' => 'SURVEI',
            'tgl_mulai_pelaksanaan' => '2027-01-10',
            'tgl_akhir_pelaksanaan' => '2027-01-20',
        ]);

        $honor = Honor::create([
            'id' => 'HON-2027-001',
            'kegiatan_manmit_id' => $kegiatan->id,
            'jabatan' => 'Pencacah',
            'jenis_honor' => 'Honor Pendataan',
            'satuan_honor' => 'O-K',
            'harga_per_satuan' => 100000,
            'tanggal_akhir_kegiatan' => '2027-01-20',
        ]);

        $eligibility = HonorAllocationService::validateEligibility($mitra, $honor, 1);

        $this->assertTrue($eligibility['eligible'], $eligibility['message']);
    }
}
