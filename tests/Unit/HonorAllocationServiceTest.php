<?php

namespace Tests\Unit;

use App\Models\AlokasiHonor;
use App\Models\Honor;
use App\Models\KegiatanManmit;
use App\Models\Kemitraan;
use App\Models\Mitra;
use App\Models\NomorSurat;
use App\Models\Setting;
use App\Services\HonorAllocationService;
use App\Supports\Constants;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class HonorAllocationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup SBML settings
        Setting::updateOrCreate(
            ['key' => 'STANDAR_BIAYA_MASUKAN_LAINNYA_NON_PNS_OB_PETUGAS_PENDATAAN_LAPANGAN_SENSUS'],
            ['value' => 4694000]
        );
        Setting::updateOrCreate(
            ['key' => 'STANDAR_BIAYA_MASUKAN_LAINNYA_NON_PNS_OB_PETUGAS_PENDATAAN_LAPANGAN_SURVEI'],
            ['value' => 3353000]
        );
    }

    protected function createMitra(string $idSobat = '61040001', string $nama = 'Budi Santoso', int $tahun = 2026, string $status = 'AKTIF'): Mitra
    {
        $mitra = Mitra::create([
            'id_sobat' => $idSobat,
            'nama_1' => $nama,
            'nik' => '6104000000000001',
        ]);

        Kemitraan::create([
            'mitra_id' => $mitra->id,
            'tahun' => $tahun,
            'status' => $status,
        ]);

        return $mitra;
    }

    protected function createKegiatanAndHonor(
        string $kegiatanId = 'KEG-001',
        string $honorId = 'HON-001',
        string $jenisKegiatan = 'SURVEI',
        string $tanggalAkhir = '2026-05-25',
        float $harga = 50000
    ): Honor {
        $kegiatan = KegiatanManmit::create([
            'id' => $kegiatanId,
            'nama' => 'Survei Uji Coba ' . $kegiatanId,
            'jenis_kegiatan' => $jenisKegiatan,
            'tgl_mulai_pelaksanaan' => '2026-05-01',
            'tgl_akhir_pelaksanaan' => '2026-05-31',
        ]);

        return Honor::create([
            'id' => $honorId,
            'kegiatan_manmit_id' => $kegiatan->id,
            'jabatan' => 'Pencacah',
            'jenis_honor' => 'Honor Pendataan',
            'satuan_honor' => 'Dokumen',
            'harga_per_satuan' => $harga,
            'tanggal_akhir_kegiatan' => $tanggalAkhir,
        ]);
    }

    public function test_resolve_mitra_by_id_and_id_sobat(): void
    {
        $mitra = $this->createMitra('61049999', 'Siti Rahma');

        $bySobat = HonorAllocationService::resolveMitra('61049999');
        $byId = HonorAllocationService::resolveMitra($mitra->id);

        $this->assertNotNull($bySobat);
        $this->assertEquals($mitra->id, $bySobat->id);
        $this->assertNotNull($byId);
        $this->assertEquals('61049999', $byId->id_sobat);
    }

    public function test_mitra_ineligibility_without_active_kemitraan(): void
    {
        // Mitra terdaftar hanya pada 2025
        $mitra = $this->createMitra('61048888', 'Agus Salim', 2025, 'AKTIF');
        $honor = $this->createKegiatanAndHonor('KEG-2026', 'HON-2026', 'SURVEI', '2026-05-25');

        $this->expectException(ValidationException::class);
        HonorAllocationService::allocate($mitra->id, $honor->id, 10.0);
    }

    public function test_dry_run_check_eligibility_does_not_persist_or_generate_numbers(): void
    {
        $mitra = $this->createMitra('61041111', 'Dewi Sartika', 2026, 'AKTIF');
        $honor = $this->createKegiatanAndHonor('KEG-DRY', 'HON-DRY', 'SURVEI', '2026-05-25', 100000);

        $nomorSuratCountBefore = NomorSurat::count();
        $alokasiCountBefore = AlokasiHonor::count();

        $check = HonorAllocationService::checkEligibility($mitra->id, $honor->id, 5.0);

        $this->assertTrue($check['eligible']);
        $this->assertEquals(500000, $check['kalkulasi']['total_honor']);
        $this->assertEquals($nomorSuratCountBefore, NomorSurat::count(), 'Dry run must not create NomorSurat.');
        $this->assertEquals($alokasiCountBefore, AlokasiHonor::count(), 'Dry run must not create AlokasiHonor.');
    }

    public function test_successful_allocation_generates_spk_and_bast_atomically(): void
    {
        $mitra = $this->createMitra('61042222', 'Rudi Hartono', 2026, 'AKTIF');
        $honor = $this->createKegiatanAndHonor('KEG-002', 'HON-002', 'SURVEI', '2026-05-25', 100000);

        $alokasi = HonorAllocationService::allocate($mitra->id, $honor->id, 10.0);

        $this->assertNotNull($alokasi->id);
        $this->assertEquals(1000000, $alokasi->total_honor);
        $this->assertNotNull($alokasi->surat_perjanjian_kerja_id);
        $this->assertNotNull($alokasi->surat_bast_id);

        $spk = NomorSurat::find($alokasi->surat_perjanjian_kerja_id);
        $bast = NomorSurat::find($alokasi->surat_bast_id);

        $this->assertEquals(Constants::JENIS_NOMOR_SURAT_PERJANJIAN_KERJA, $spk->jenis);
        $this->assertEquals(Constants::JENIS_NOMOR_SURAT_BAST, $bast->jenis);
        $this->assertEquals('2026-05-01', $alokasi->tanggal_mulai_perjanjian->toDateString());
        $this->assertEquals('2026-05-31', $alokasi->tanggal_akhir_perjanjian->toDateString());
    }

    public function test_survei_reuses_existing_spk_in_same_month(): void
    {
        $mitra = $this->createMitra('61043333', 'Hendra Setiawan', 2026, 'AKTIF');
        $honor1 = $this->createKegiatanAndHonor('KEG-SURV-1', 'HON-SURV-1', 'SURVEI', '2026-06-20', 50000);
        $honor2 = $this->createKegiatanAndHonor('KEG-SURV-2', 'HON-SURV-2', 'SURVEI', '2026-06-25', 60000);

        $alokasi1 = HonorAllocationService::allocate($mitra->id, $honor1->id, 5.0);
        $alokasi2 = HonorAllocationService::allocate($mitra->id, $honor2->id, 5.0);

        // SPK bulan Juni 2026 harus sama karena sama-sama survei
        $this->assertEquals($alokasi1->surat_perjanjian_kerja_id, $alokasi2->surat_perjanjian_kerja_id);

        // Namun BAST harus berbeda karena honor/kegiatan berbeda
        $this->assertNotEquals($alokasi1->surat_bast_id, $alokasi2->surat_bast_id);
    }

    public function test_mitra_ineligibility_on_sensus_schedule_overlap(): void
    {
        $mitra = $this->createMitra('61044444', 'Bambang Pamungkas', 2026, 'AKTIF');
        $honorSensus = $this->createKegiatanAndHonor('KEG-SENSUS-1', 'HON-SENSUS-1', 'SENSUS', '2026-07-20', 2000000);
        $honorLain = $this->createKegiatanAndHonor('KEG-SURV-JULI', 'HON-SURV-JULI', 'SURVEI', '2026-07-25', 100000);

        // Alokasikan ke sensus berhasil
        HonorAllocationService::allocate($mitra->id, $honorSensus->id, 1.0);

        // Alokasikan ke kegiatan lain di bulan yang sama (Juli 2026) harus gagal karena bentrok dengan sensus
        $this->expectException(ValidationException::class);
        HonorAllocationService::allocate($mitra->id, $honorLain->id, 5.0);
    }

    public function test_mitra_ineligibility_on_sbml_limit_exceeded(): void
    {
        $mitra = $this->createMitra('61045555', 'Eko Yuli', 2026, 'AKTIF');
        // Limit survei adalah Rp 3.353.000
        $honor = $this->createKegiatanAndHonor('KEG-BIG', 'HON-BIG', 'SURVEI', '2026-08-25', 1000000);

        // Coba alokasi 4 target = Rp 4.000.000 (melebihi limit Rp 3.353.000)
        $this->expectException(ValidationException::class);
        HonorAllocationService::allocate($mitra->id, $honor->id, 4.0);
    }
}
