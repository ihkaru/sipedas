<?php

namespace Tests\Unit;

use App\Exports\MonitorHonorMitraExport;
use App\Exports\Sheets\MonitorHonorMitraDetailSheet;
use App\Exports\Sheets\MonitorHonorMitraSummarySheet;
use App\Filament\Pages\MonitorHonorMitra;
use App\Models\AlokasiHonor;
use App\Models\Honor;
use App\Models\KegiatanManmit;
use App\Models\Kemitraan;
use App\Models\Mitra;
use App\Models\Setting;
use Filament\Tables\Table;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class MonitorHonorMitraExportTest extends TestCase
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

    protected function createMitra(string $idSobat, string $nama, string $kec = '010', string $desa = '001', ?string $wa = '08123456789'): Mitra
    {
        $mitra = Mitra::create([
            'id_sobat' => $idSobat,
            'nama_1' => $nama,
            'nik' => '6104'.str_pad($idSobat, 12, '0', STR_PAD_LEFT),
            'alamat_prov' => '61',
            'alamat_kab' => '04',
            'alamat_kec' => $kec,
            'alamat_desa' => $desa,
            'nomor_wa' => $wa,
            'posisi' => 'Pendataan Lapangan',
        ]);

        Kemitraan::create([
            'mitra_id' => $mitra->id,
            'tahun' => 2026,
            'status' => 'AKTIF',
        ]);

        return $mitra;
    }

    public function test_export_generates_two_sheets_with_correct_titles(): void
    {
        $mitra = $this->createMitra('61040001', 'Budi Santoso');
        $records = collect([$mitra]);

        $export = new MonitorHonorMitraExport($records, 10, 2026, 4694000, 3353000);
        $sheets = $export->sheets();

        $this->assertCount(2, $sheets);
        $this->assertInstanceOf(MonitorHonorMitraDetailSheet::class, $sheets[0]);
        $this->assertInstanceOf(MonitorHonorMitraSummarySheet::class, $sheets[1]);

        $this->assertSame('Perencanaan & Alokasi Mitra', $sheets[0]->title());
        $this->assertSame('Rekap Kesiapan & Kuota', $sheets[1]->title());
    }

    public function test_detail_sheet_maps_mitra_planning_data_and_readiness_categories(): void
    {
        // 1. Mitra Bebas Tugas (Honor = 0)
        $mitraBebas = $this->createMitra('61040001', 'Mitra Bebas');
        $mitraBebas->total_honor_sensus = 0;
        $mitraBebas->total_honor_survei = 0;

        // 2. Mitra Ada Kuota (< 80%)
        $mitraAdaKuota = $this->createMitra('61040002', 'Mitra Ada Kuota');
        $mitraAdaKuota->total_honor_sensus = 0;
        $mitraAdaKuota->total_honor_survei = 1500000;

        // 3. Mitra Mendekati Limit (>= 80%)
        $mitraDekat = $this->createMitra('61040003', 'Mitra Mendekati');
        $mitraDekat->total_honor_sensus = 0;
        $mitraDekat->total_honor_survei = 3000000; // > 80% dari 3.353.000 (2.682.400)

        // 4. Mitra Over Limit
        $mitraOver = $this->createMitra('61040004', 'Mitra Over Limit');
        $mitraOver->total_honor_sensus = 0;
        $mitraOver->total_honor_survei = 3500000; // > 3.353.000

        $records = collect([$mitraBebas, $mitraAdaKuota, $mitraDekat, $mitraOver]);
        $alokasisByMitra = collect();

        $detailSheet = new MonitorHonorMitraDetailSheet($records, $alokasisByMitra, 10, 2026, 4694000, 3353000);

        $mapped1 = $detailSheet->map($mitraBebas);
        $this->assertSame('SIAP DITUGASKAN (BEBAS)', $mapped1[3]);
        $this->assertSame(3353000.0, (float) $mapped1[10]); // Sisa survei penuh
        $this->assertSame(3353000.0, (float) $mapped1[16]); // Maks honor tambahan

        $mapped2 = $detailSheet->map($mitraAdaKuota);
        $this->assertSame('BISA DITUGASKAN (ADA SISA KUOTA)', $mapped2[3]);
        $this->assertSame(1853000.0, (float) $mapped2[10]); // 3353000 - 1500000
        $this->assertSame(1853000.0, (float) $mapped2[16]);

        $mapped3 = $detailSheet->map($mitraDekat);
        $this->assertSame('PERHATIAN (MENDEKATI LIMIT)', $mapped3[3]);
        $this->assertSame(353000.0, (float) $mapped3[10]);

        $mapped4 = $detailSheet->map($mitraOver);
        $this->assertSame('TIDAK BISA (OVER LIMIT)', $mapped4[3]);
        $this->assertSame(0.0, (float) $mapped4[10]);
        $this->assertSame(0.0, (float) $mapped4[16]);
    }

    public function test_detail_sheet_identifies_sensus_and_activities(): void
    {
        $mitraSensus = $this->createMitra('61040005', 'Mitra Sensus');
        $mitraSensus->total_honor_sensus = 2000000;
        $mitraSensus->total_honor_survei = 0;

        $kegiatan = KegiatanManmit::create([
            'id' => 'KEG_SENSUS_01',
            'nama' => 'Sensus Pertanian 2026',
            'jenis_kegiatan' => 'SENSUS',
        ]);

        $honor = Honor::create([
            'id' => 'HONOR_01',
            'kegiatan_manmit_id' => $kegiatan->id,
            'nama_honor' => 'Honor Petugas Sensus',
            'jabatan' => 'Pencacah',
            'jenis_honor' => 'Honor Pendataan',
            'satuan_honor' => 'Dokumen',
            'harga_per_satuan' => 2000000,
            'tanggal_akhir_kegiatan' => '2026-10-31',
        ]);

        $alokasi = new AlokasiHonor([
            'id' => 999,
            'mitra_id' => $mitraSensus->id,
            'honor_id' => $honor->id,
            'total_honor' => 2000000,
            'tanggal_mulai_perjanjian' => '2026-10-01',
            'tanggal_akhir_perjanjian' => '2026-10-31',
        ]);
        $alokasi->setRelation('honor', $honor);
        $honor->setRelation('kegiatanManmit', $kegiatan);

        $alokasisByMitra = collect([
            $mitraSensus->id => collect([$alokasi]),
        ]);

        $detailSheet = new MonitorHonorMitraDetailSheet(collect([$mitraSensus]), $alokasisByMitra, 10, 2026, 4694000, 3353000);
        $mapped = $detailSheet->map($mitraSensus);

        $this->assertSame('PERHATIAN (SEDANG IKUT SENSUS)', $mapped[3]);
        $this->assertSame('YA', $mapped[7]);
        $this->assertStringContainsString('Sensus Pertanian 2026', $mapped[6]);
    }

    public function test_summary_sheet_aggregates_totals_and_kecamatans(): void
    {
        $mitra1 = $this->createMitra('61040001', 'Mitra A', '010');
        $mitra1->total_honor_sensus = 0;
        $mitra1->total_honor_survei = 0;

        $mitra2 = $this->createMitra('61040002', 'Mitra B', '010');
        $mitra2->total_honor_sensus = 0;
        $mitra2->total_honor_survei = 1000000;

        $records = collect([$mitra1, $mitra2]);
        $alokasisByMitra = collect();

        $summarySheet = new MonitorHonorMitraSummarySheet($records, $alokasisByMitra, 10, 2026, 4694000, 3353000);
        $rows = $summarySheet->collection();

        // Cari row total keseluruhan
        $totalRow = $rows->firstWhere(1, 'TOTAL KESELURUHAN');
        $this->assertNotNull($totalRow);
        $this->assertSame(2, $totalRow[2]); // 2 mitra
        $this->assertSame(1000000.0, (float) $totalRow[4]); // Total honor

        // Cari tabel kecamatan
        $kecRow = $rows->firstWhere(0, 'DISTRIBUSI KESIAPAN MITRA PER KECAMATAN');
        $this->assertNotNull($kecRow);
    }

    public function test_excel_export_can_store_spreadsheet(): void
    {
        Excel::fake();

        $mitra = $this->createMitra('61040001', 'Mitra Test');
        $export = new MonitorHonorMitraExport(collect([$mitra]), 10, 2026, 4694000, 3353000);

        Excel::store($export, 'test_perencanaan.xlsx');

        Excel::assertStored('test_perencanaan.xlsx');
    }

    public function test_status_ketersediaan_filter_applies_base_query_havings(): void
    {
        $page = new MonitorHonorMitra;
        $table = $page->table(new Table($page));
        $filter = $table->getFilter('status_ketersediaan');

        $this->assertNotNull($filter);

        // Test filter 'bebas'
        $q1 = Mitra::query();
        $filter->applyToBaseQuery($q1, ['value' => 'bebas']);
        $this->assertCount(1, $q1->getQuery()->havings ?? []);
        $this->assertStringContainsString('total_honor_survei = 0', $q1->getQuery()->havings[0]['sql']);

        // Test filter 'ada_kuota'
        $q2 = Mitra::query();
        $filter->applyToBaseQuery($q2, ['value' => 'ada_kuota']);
        $this->assertCount(1, $q2->getQuery()->havings ?? []);
        $this->assertStringContainsString('total_honor_survei < ?', $q2->getQuery()->havings[0]['sql']);

        // Test filter 'mendekati_limit'
        $q3 = Mitra::query();
        $filter->applyToBaseQuery($q3, ['value' => 'mendekati_limit']);
        $this->assertCount(1, $q3->getQuery()->havings ?? []);
        $this->assertStringContainsString('total_honor_survei >= ?', $q3->getQuery()->havings[0]['sql']);

        // Test filter 'over_limit'
        $q4 = Mitra::query();
        $filter->applyToBaseQuery($q4, ['value' => 'over_limit']);
        $this->assertCount(1, $q4->getQuery()->havings ?? []);
        $this->assertStringContainsString('total_honor_survei > ?', $q4->getQuery()->havings[0]['sql']);
    }
}
