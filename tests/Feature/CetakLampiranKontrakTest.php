<?php

namespace Tests\Feature;

use App\Filament\Actions\CetakUlangLampiranKontrakAction;
use App\Models\AlokasiHonor;
use App\Models\Honor;
use App\Models\KegiatanManmit;
use App\Models\Kemitraan;
use App\Models\Mitra;
use App\Models\NomorSurat;
use App\Models\Setting;
use App\Services\LampiranKontrakService;
use App\Supports\Constants;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CetakLampiranKontrakTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::updateOrCreate(
            ['key' => 'STANDAR_BIAYA_MASUKAN_LAINNYA_NON_PNS_OB_PETUGAS_PENDATAAN_LAPANGAN_SENSUS'],
            ['value' => 4694000]
        );
        Setting::updateOrCreate(
            ['key' => 'STANDAR_BIAYA_MASUKAN_LAINNYA_NON_PNS_OB_PETUGAS_PENDATAAN_LAPANGAN_SURVEI'],
            ['value' => 3353000]
        );
    }

    private function createMitra(string $idSobat, string $nama, int $tahun = 2026): Mitra
    {
        $mitra = Mitra::create([
            'id_sobat' => $idSobat,
            'nama_1' => $nama,
            'nik' => str_pad($idSobat, 16, '0', STR_PAD_RIGHT),
        ]);

        Kemitraan::create([
            'mitra_id' => $mitra->id,
            'tahun' => $tahun,
            'status' => 'AKTIF',
        ]);

        return $mitra;
    }

    private function createHonor(
        string $kegiatanId,
        string $namaKegiatan,
        string $jenisKegiatan,
        string $honorId,
        string $tanggalAkhir,
        float $harga = 100000
    ): Honor {
        $kegiatan = KegiatanManmit::create([
            'id' => $kegiatanId,
            'nama' => $namaKegiatan,
            'jenis_kegiatan' => $jenisKegiatan,
            'tgl_mulai_pelaksanaan' => '2026-01-01',
            'tgl_akhir_pelaksanaan' => '2026-12-31',
        ]);

        return Honor::create([
            'id' => $honorId,
            'kegiatan_manmit_id' => $kegiatan->id,
            'jabatan' => 'PPL',
            'jenis_honor' => 'PENDATAAN',
            'satuan_honor' => 'Dokumen',
            'harga_per_satuan' => $harga,
            'tanggal_akhir_kegiatan' => $tanggalAkhir,
        ]);
    }

    private function createSpk(int $nomor, string $tanggal = '2026-10-01', int $tahun = 2026): NomorSurat
    {
        return NomorSurat::create([
            'nomor' => $nomor,
            'sub_nomor' => null,
            'tanggal_nomor' => $tanggal,
            'jenis' => Constants::JENIS_NOMOR_SURAT_PERJANJIAN_KERJA,
            'tahun' => $tahun,
        ]);
    }

    private function createAlokasi(
        Mitra $mitra,
        Honor $honor,
        ?NomorSurat $spk,
        float $target,
        string $mulai = '2026-10-01',
        string $akhir = '2026-10-31'
    ): AlokasiHonor {
        return AlokasiHonor::withoutEvents(function () use ($mitra, $honor, $spk, $target, $mulai, $akhir): AlokasiHonor {
            return AlokasiHonor::create([
                'mitra_id' => $mitra->id,
                'honor_id' => $honor->id,
                'surat_perjanjian_kerja_id' => $spk?->id,
                'target_per_satuan_honor' => $target,
                'total_honor' => $target * (float) $honor->harga_per_satuan,
                'tanggal_mulai_perjanjian' => $mulai,
                'tanggal_akhir_perjanjian' => $akhir,
                'tanggal_penanda_tanganan_spk_oleh_petugas' => $mulai,
            ]);
        });
    }

    public function test_filters_lampiran_by_tanggal_akhir_kegiatan_and_jenis_kegiatan(): void
    {
        $mitraA = $this->createMitra('61040001', 'Andi Pratama');
        $mitraB = $this->createMitra('61040002', 'Budi Santoso');

        $honorSurvei1 = $this->createHonor('SRV-01', '(SRV-01) Survei Harga Konsumen', 'SURVEI', 'HON-SRV-01', '2026-10-15', 50000);
        $honorSurvei2 = $this->createHonor('SRV-02', '(SRV-02) Survei Ubinan', 'SURVEI', 'HON-SRV-02', '2026-10-28', 75000);
        $honorSensus = $this->createHonor('SNS-01', '(SNS-01) Sensus Ekonomi 2026', 'SENSUS', 'HON-SNS-01', '2026-10-20', 200000);
        $honorBulanLain = $this->createHonor('SRV-NOV', '(SRV-NOV) Survei November', 'SURVEI', 'HON-SRV-NOV', '2026-11-10', 60000);

        $spkSurveiA = $this->createSpk(10, '2026-10-01');
        $spkSensusB = $this->createSpk(11, '2026-10-01');
        $spkNovA = $this->createSpk(12, '2026-11-01');

        // Mitra A: 2 kegiatan SURVEI di Oktober dalam 1 SPK gabungan
        $this->createAlokasi($mitraA, $honorSurvei1, $spkSurveiA, 4);
        $this->createAlokasi($mitraA, $honorSurvei2, $spkSurveiA, 2);

        // Mitra B: 1 kegiatan SENSUS di Oktober
        $this->createAlokasi($mitraB, $honorSensus, $spkSensusB, 3);

        // Mitra A: 1 kegiatan SURVEI di November (harus dikecualikan untuk filter Oktober)
        $this->createAlokasi($mitraA, $honorBulanLain, $spkNovA, 5, '2026-11-01', '2026-11-30');

        // Alokasi tanpa nomor SPK (harus dikecualikan)
        $this->createAlokasi($mitraB, $honorSurvei1, null, 1);

        // 1. Filter ALL di Oktober 2026 -> 2 lampiran (1 SURVEI gabungan + 1 SENSUS)
        $summaryAll = LampiranKontrakService::getSummary(2026, 10, 'ALL');
        $this->assertSame(2, $summaryAll['total_lampiran']);
        $this->assertSame(3, $summaryAll['total_alokasi']);
        $this->assertSame(2, $summaryAll['total_mitra']);
        $this->assertEquals(950000.0, $summaryAll['total_honor']);

        // 2. Filter SURVEI di Oktober 2026 -> 1 lampiran gabungan dengan 2 kegiatan
        $surveiGroups = LampiranKontrakService::getGroupedLampiran(2026, 10, 'SURVEI');
        $this->assertCount(1, $surveiGroups);
        $this->assertSame('SURVEI', $surveiGroups->first()['jenis_kegiatan']);
        $this->assertCount(2, $surveiGroups->first()['alokasi']);
        $this->assertEquals(350000.0, $surveiGroups->first()['total_honor']);

        // 3. Filter SENSUS di Oktober 2026 -> 1 lampiran khusus sensus
        $sensusGroups = LampiranKontrakService::getGroupedLampiran(2026, 10, 'SENSUS');
        $this->assertCount(1, $sensusGroups);
        $this->assertSame('SENSUS', $sensusGroups->first()['jenis_kegiatan']);
        $this->assertCount(1, $sensusGroups->first()['alokasi']);
        $this->assertEquals(600000.0, $sensusGroups->first()['total_honor']);
    }

    public function test_http_endpoint_renders_only_lampiran_without_contract_articles(): void
    {
        $mitra = $this->createMitra('61040010', 'Citra Lestari');
        $honorSurvei = $this->createHonor('SRV-HTTP', '(SRV-HTTP) Survei Sosial Ekonomi', 'SURVEI', 'HON-SRV-HTTP', '2026-10-25', 100000);
        $honorSensus = $this->createHonor('SNS-HTTP', '(SNS-HTTP) Sensus Pertanian', 'SENSUS', 'HON-SNS-HTTP', '2026-10-26', 150000);

        $spkSurvei = $this->createSpk(101, '2026-10-01');
        $spkSensus = $this->createSpk(102, '2026-10-01');

        $this->createAlokasi($mitra, $honorSurvei, $spkSurvei, 2);
        $this->createAlokasi($mitra, $honorSensus, $spkSensus, 3);

        // Request filter SURVEI
        $responseSurvei = $this->get(route('cetak.kontrak.lampiran', [
            'tahun' => 2026,
            'bulan' => 10,
            'jenis_kegiatan' => 'SURVEI',
        ]));

        $responseSurvei->assertOk();
        $responseSurvei->assertSee('Lampiran 1.');
        $responseSurvei->assertSee('Daftar Kegiatan');
        $responseSurvei->assertSee('(SRV-HTTP) Survei Sosial Ekonomi');
        $responseSurvei->assertDontSee('(SNS-HTTP) Sensus Pertanian');
        $responseSurvei->assertDontSee('Pasal 1');

        // Request filter SENSUS
        $responseSensus = $this->get(route('cetak.kontrak.lampiran', [
            'tahun' => 2026,
            'bulan' => 10,
            'jenis_kegiatan' => 'SENSUS',
        ]));

        $responseSensus->assertOk();
        $responseSensus->assertSee('(SNS-HTTP) Sensus Pertanian');
        $responseSensus->assertDontSee('(SRV-HTTP) Survei Sosial Ekonomi');
        $responseSensus->assertDontSee('Pasal 1');
    }

    public function test_http_endpoint_rejects_invalid_period_and_handles_empty_result(): void
    {
        $this->get(route('cetak.kontrak.lampiran', ['tahun' => 2026, 'bulan' => 13]))
            ->assertStatus(400);

        $this->get(route('cetak.kontrak.lampiran', ['tahun' => 2026, 'bulan' => 2, 'jenis_kegiatan' => 'SURVEI']))
            ->assertOk()
            ->assertSee('Tidak ada data lampiran kontrak');
    }

    public function test_http_endpoint_streams_and_downloads_real_pdf_for_all_mitra_in_month(): void
    {
        $mitraA = $this->createMitra('61040021', 'Dedi Kurniawan');
        $mitraB = $this->createMitra('61040022', 'Eka Putri');

        $honorSurvei = $this->createHonor('SRV-PDF', '(SRV-PDF) Survei Industri Mikro', 'SURVEI', 'HON-SRV-PDF', '2026-10-29', 120000);
        $spkA = $this->createSpk(201, '2026-10-01');
        $spkB = $this->createSpk(202, '2026-10-01');

        $this->createAlokasi($mitraA, $honorSurvei, $spkA, 2);
        $this->createAlokasi($mitraB, $honorSurvei, $spkB, 3);

        $streamResponse = $this->get(route('cetak.kontrak.lampiran', [
            'tahun' => 2026,
            'bulan' => 10,
            'jenis_kegiatan' => 'SURVEI',
            'format' => 'pdf',
        ]));

        $streamResponse->assertOk();
        $streamResponse->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $streamResponse->getContent());

        $downloadResponse = $this->get(route('cetak.kontrak.lampiran', [
            'tahun' => 2026,
            'bulan' => 10,
            'jenis_kegiatan' => 'SURVEI',
            'format' => 'pdf',
            'download' => 1,
        ]));

        $downloadResponse->assertOk();
        $downloadResponse->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('attachment;', (string) $downloadResponse->headers->get('content-disposition'));
        $this->assertStringStartsWith('%PDF-', $downloadResponse->getContent());
    }

    public function test_filament_reusable_action_can_be_constructed(): void
    {
        $action = CetakUlangLampiranKontrakAction::make();

        $this->assertSame('cetak_ulang_lampiran_kontrak', $action->getName());
        $this->assertSame('Cetak Ulang Lampiran Kontrak', $action->getLabel());
    }
}
