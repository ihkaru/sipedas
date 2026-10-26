<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MonitorHonorMitraSummarySheet implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithCustomStartCell, WithEvents, WithHeadings, WithStyles, WithTitle
{
    protected Collection $records;

    protected Collection $alokasisByMitra;

    protected int $month;

    protected int $year;

    protected float $limitSensus;

    protected float $limitSurvei;

    protected ?string $kecamatanLabel;

    protected ?string $desaLabel;

    protected int $table1StartRow = 6;

    protected int $table1EndRow = 12;

    protected int $table2StartRow = 15;

    protected int $table2EndRow = 15;

    public function __construct(
        Collection $records,
        Collection $alokasisByMitra,
        int $month,
        int $year,
        float $limitSensus,
        float $limitSurvei,
        ?string $kecamatanLabel = null,
        ?string $desaLabel = null
    ) {
        $this->records = $records;
        $this->alokasisByMitra = $alokasisByMitra;
        $this->month = $month;
        $this->year = $year;
        $this->limitSensus = $limitSensus;
        $this->limitSurvei = $limitSurvei;
        $this->kecamatanLabel = $kecamatanLabel;
        $this->desaLabel = $desaLabel;
    }

    public function title(): string
    {
        return 'Rekap Kesiapan & Kuota';
    }

    public function startCell(): string
    {
        return 'A6';
    }

    public function headings(): array
    {
        return [
            'No.',
            'Kategori Status Kesiapan',
            'Jumlah Mitra',
            '% dari Total',
            'Total Honor Berjalan (Rp)',
            'Total Sisa Kuota Survei (Rp)',
            'Total Sisa Kuota Sensus (Rp)',
            'Rekomendasi Alokasi Tim Kegiatan',
        ];
    }

    public function collection(): Collection
    {
        $rows = collect();

        $stats = [
            'bebas' => ['label' => 'Siap Ditugaskan (Bebas / 0 Kegiatan)', 'count' => 0, 'honor' => 0.0, 'sisa_survei' => 0.0, 'sisa_sensus' => 0.0, 'desc' => 'Prioritas utama. Jadwal bebas & kuota 100% utuh.'],
            'ada_kuota' => ['label' => 'Bisa Ditugaskan (Ada Sisa Kuota)', 'count' => 0, 'honor' => 0.0, 'sisa_survei' => 0.0, 'sisa_sensus' => 0.0, 'desc' => 'Layak ditugaskan. Perhatikan tanggal agar tidak bentrok.'],
            'sensus' => ['label' => 'Perhatian (Sedang Ikut Sensus)', 'count' => 0, 'honor' => 0.0, 'sisa_survei' => 0.0, 'sisa_sensus' => 0.0, 'desc' => 'Dilarang tumpang-tindih tanggal dengan kegiatan Sensus.'],
            'mendekati' => ['label' => 'Perhatian (Mendekati Limit >=80%)', 'count' => 0, 'honor' => 0.0, 'sisa_survei' => 0.0, 'sisa_sensus' => 0.0, 'desc' => 'Kuota hampir habis. Hanya untuk honor kegiatan ringan.'],
            'over' => ['label' => 'Tidak Bisa Ditugaskan (Over Limit)', 'count' => 0, 'honor' => 0.0, 'sisa_survei' => 0.0, 'sisa_sensus' => 0.0, 'desc' => 'Melebihi/mencapai SBML. Dilarang menambah penugasan.'],
        ];

        $kecStats = [];

        foreach ($this->records as $mitra) {
            $honorSensus = (float) ($mitra->total_honor_sensus ?? 0);
            $honorSurvei = (float) ($mitra->total_honor_survei ?? 0);
            $sisaSensus = max(0, $this->limitSensus - $honorSensus);
            $sisaSurvei = max(0, $this->limitSurvei - $honorSurvei);
            $totalHonor = $honorSensus + $honorSurvei;

            $alokasis = $this->alokasisByMitra->get($mitra->id, collect());
            $hasSensus = $alokasis->contains(function ($a) {
                return ($a->honor?->kegiatanManmit?->jenis_kegiatan ?? '') === 'SENSUS';
            });

            $isOver = ($honorSensus > $this->limitSensus) || ($honorSurvei > $this->limitSurvei);
            $isNear = (! $isOver) && (($honorSensus >= $this->limitSensus * 0.8) || ($honorSurvei >= $this->limitSurvei * 0.8));

            if ($isOver) {
                $category = 'over';
            } elseif ($hasSensus) {
                $category = 'sensus';
            } elseif ($isNear) {
                $category = 'mendekati';
            } elseif ($honorSurvei == 0 && $honorSensus == 0) {
                $category = 'bebas';
            } else {
                $category = 'ada_kuota';
            }

            $stats[$category]['count']++;
            $stats[$category]['honor'] += $totalHonor;
            $stats[$category]['sisa_survei'] += $sisaSurvei;
            $stats[$category]['sisa_sensus'] += $sisaSensus;

            $kecName = $mitra->kecamatan_name ?: 'Tidak Terdefinisi';
            if (! isset($kecStats[$kecName])) {
                $kecStats[$kecName] = [
                    'total' => 0,
                    'bebas' => 0,
                    'ada_kuota' => 0,
                    'sensus' => 0,
                    'mendekati' => 0,
                    'over' => 0,
                    'sisa_survei' => 0.0,
                ];
            }
            $kecStats[$kecName]['total']++;
            $kecStats[$kecName][$category]++;
            $kecStats[$kecName]['sisa_survei'] += $sisaSurvei;
        }

        $totalMitra = max(1, $this->records->count());
        $sumCount = 0;
        $sumHonor = 0.0;
        $sumSisaSurvei = 0.0;
        $sumSisaSensus = 0.0;

        $no = 1;
        foreach ($stats as $cat => $data) {
            $pct = round(($data['count'] / $totalMitra) * 100, 1);
            $rows->push([
                $no++,
                $data['label'],
                $data['count'],
                $pct.'%',
                $data['honor'],
                $data['sisa_survei'],
                $data['sisa_sensus'],
                $data['desc'],
            ]);

            $sumCount += $data['count'];
            $sumHonor += $data['honor'];
            $sumSisaSurvei += $data['sisa_survei'];
            $sumSisaSensus += $data['sisa_sensus'];
        }

        $rows->push([
            '',
            'TOTAL KESELURUHAN',
            $sumCount,
            '100.0%',
            $sumHonor,
            $sumSisaSurvei,
            $sumSisaSensus,
            'Total kapasitas seluruh mitra terfilter',
        ]);

        $rows->push(['', '', '', '', '', '', '', '']);
        $rows->push(['', '', '', '', '', '', '', '']);

        $rows->push([
            'DISTRIBUSI KESIAPAN MITRA PER KECAMATAN',
            '', '', '', '', '', '', '',
        ]);

        $rows->push([
            'No.',
            'Nama Kecamatan',
            'Total Mitra',
            'Siap (Bebas)',
            'Ada Kuota',
            'Sedang Sensus',
            'Mendekati Limit',
            'Over Limit',
            '% Ketersediaan',
            'Total Sisa Kuota Survei (Rp)',
        ]);

        ksort($kecStats);
        $kecNo = 1;
        $totKecMitra = 0;
        $totKecBebas = 0;
        $totKecAda = 0;
        $totKecSensus = 0;
        $totKecDekat = 0;
        $totKecOver = 0;
        $totKecSisa = 0.0;

        foreach ($kecStats as $kec => $kData) {
            $availCount = $kData['bebas'] + $kData['ada_kuota'];
            $availPct = $kData['total'] > 0 ? round(($availCount / $kData['total']) * 100, 1) : 0;

            $rows->push([
                $kecNo++,
                $kec,
                $kData['total'],
                $kData['bebas'],
                $kData['ada_kuota'],
                $kData['sensus'],
                $kData['mendekati'],
                $kData['over'],
                $availPct.'%',
                $kData['sisa_survei'],
            ]);

            $totKecMitra += $kData['total'];
            $totKecBebas += $kData['bebas'];
            $totKecAda += $kData['ada_kuota'];
            $totKecSensus += $kData['sensus'];
            $totKecDekat += $kData['mendekati'];
            $totKecOver += $kData['over'];
            $totKecSisa += $kData['sisa_survei'];
        }

        $totAvail = $totKecBebas + $totKecAda;
        $totAvailPct = $totKecMitra > 0 ? round(($totAvail / $totKecMitra) * 100, 1) : 0;

        $rows->push([
            '',
            'TOTAL KABUPATEN / KOTA',
            $totKecMitra,
            $totKecBebas,
            $totKecAda,
            $totKecSensus,
            $totKecDekat,
            $totKecOver,
            $totAvailPct.'%',
            $totKecSisa,
        ]);

        return $rows;
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_NUMBER,
            'E' => '"Rp "#,##0',
            'F' => '"Rp "#,##0',
            'G' => '"Rp "#,##0',
            'J' => '"Rp "#,##0',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            6 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E40AF']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
            12 => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $monthName = $this->getMonthName($this->month);

                $sheet->setCellValue('A1', 'RINGKASAN KESIAPAN & KUOTA ALOKASI MITRA');
                $sheet->setCellValue('A2', "Periode: {$monthName} {$this->year} | Sensus SBML: Rp ".number_format($this->limitSensus, 0, ',', '.').' | Survei SBML: Rp '.number_format($this->limitSurvei, 0, ',', '.'));
                $sheet->setCellValue('A3', 'Diekspor pada: '.now()->format('d/m/Y H:i:s').' WIB');

                $sheet->mergeCells('A1:H1');
                $sheet->mergeCells('A2:H2');
                $sheet->mergeCells('A3:H3');

                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('1E3A8A');
                $sheet->getStyle('A2:A3')->getFont()->setSize(10)->getColor()->setRGB('475569');

                $highestRow = $sheet->getHighestRow();

                for ($r = 13; $r <= $highestRow; $r++) {
                    $valA = (string) $sheet->getCell("A{$r}")->getValue();
                    $valB = (string) $sheet->getCell("B{$r}")->getValue();

                    if (str_contains($valA, 'DISTRIBUSI KESIAPAN MITRA PER KECAMATAN')) {
                        $sheet->mergeCells("A{$r}:J{$r}");
                        $sheet->getStyle("A{$r}")->getFont()->setBold(true)->setSize(12)->getColor()->setRGB('1E3A8A');
                    } elseif ($valA === 'No.' && $valB === 'Nama Kecamatan') {
                        $sheet->getStyle("A{$r}:J{$r}")->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E40AF']],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                        ]);
                    } elseif ($valB === 'TOTAL KABUPATEN / KOTA') {
                        $sheet->getStyle("A{$r}:J{$r}")->applyFromArray([
                            'font' => ['bold' => true],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']],
                        ]);
                    }
                }
            },
        ];
    }

    protected function getMonthName(int $month): string
    {
        return [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ][$month] ?? '-';
    }
}
