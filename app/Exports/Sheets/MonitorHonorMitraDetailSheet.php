<?php

namespace App\Exports\Sheets;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MonitorHonorMitraDetailSheet implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithCustomStartCell, WithEvents, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected Collection $records;

    protected Collection $alokasisByMitra;

    protected int $month;

    protected int $year;

    protected float $limitSensus;

    protected float $limitSurvei;

    protected ?string $kecamatanLabel;

    protected ?string $desaLabel;

    protected int $rowNumber = 0;

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

    public function collection(): Collection
    {
        return $this->records;
    }

    public function title(): string
    {
        return 'Perencanaan & Alokasi Mitra';
    }

    public function startCell(): string
    {
        return 'A6';
    }

    public function headings(): array
    {
        return [
            'No.',
            'ID Sobat',
            'Nama Lengkap',
            'Status Rekomendasi',
            'Keterangan Kesiapan',
            'Beban Kegiatan',
            'Daftar Kegiatan Sedang Berjalan',
            'Ada Sensus Aktif?',
            'Honor Survei Berjalan',
            'Batas SBML Survei',
            'Sisa Kuota Survei',
            '% Survei Terpakai',
            'Honor Sensus Berjalan',
            'Batas SBML Sensus',
            'Sisa Kuota Sensus',
            '% Sensus Terpakai',
            'Maks. Honor Tambahan Aman',
            'NIK',
            'No. WhatsApp / HP',
            'Link WhatsApp Langsung',
            'Kecamatan Domisili',
            'Desa / Kelurahan Domisili',
            'Posisi Terdaftar',
            'Status Kemitraan',
            'Email',
            'Alamat Detail',
        ];
    }

    public function map($mitra): array
    {
        $this->rowNumber++;

        $honorSensus = (float) ($mitra->total_honor_sensus ?? 0);
        $honorSurvei = (float) ($mitra->total_honor_survei ?? 0);
        $sisaSensus = max(0, $this->limitSensus - $honorSensus);
        $sisaSurvei = max(0, $this->limitSurvei - $honorSurvei);

        $alokasis = $this->alokasisByMitra->get($mitra->id, collect());
        $jmlKegiatan = $alokasis->count();
        $hasSensus = $alokasis->contains(function ($a) {
            return ($a->honor?->kegiatanManmit?->jenis_kegiatan ?? '') === 'SENSUS';
        });

        if ($alokasis->isEmpty()) {
            $kegiatanList = '-';
        } else {
            $kegiatanList = $alokasis->values()->map(function ($a, $idx) {
                $nama = $a->honor?->kegiatanManmit?->nama ?? 'Kegiatan';
                $jenis = $a->honor?->kegiatanManmit?->jenis_kegiatan ?? 'SURVEI';
                $mulai = $a->tanggal_mulai_perjanjian ? Carbon::parse($a->tanggal_mulai_perjanjian)->format('d/m') : '';
                $akhir = $a->tanggal_akhir_perjanjian ? Carbon::parse($a->tanggal_akhir_perjanjian)->format('d/m') : '';
                $tgl = ($mulai && $akhir) ? " ({$mulai} - {$akhir})" : '';
                $honorFmt = 'Rp '.number_format((float) $a->total_honor, 0, ',', '.');

                return ($idx + 1).". {$nama} [{$jenis}]{$tgl}: {$honorFmt}";
            })->implode("\n");
        }

        $isOverLimit = ($honorSensus > $this->limitSensus) || ($honorSurvei > $this->limitSurvei);
        $isNearLimit = (! $isOverLimit) && (($honorSensus >= $this->limitSensus * 0.8) || ($honorSurvei >= $this->limitSurvei * 0.8));

        if ($isOverLimit) {
            $statusRekomendasi = 'TIDAK BISA (OVER LIMIT)';
            $keteranganKapasitas = 'Akumulasi honor telah mencapai/melebihi batas SBML bulanan.';
        } elseif ($hasSensus) {
            $statusRekomendasi = 'PERHATIAN (SEDANG IKUT SENSUS)';
            $keteranganKapasitas = 'Sedang bertugas sensus. Dilarang bertabrakan jadwal/tanggal tugas.';
        } elseif ($isNearLimit) {
            $statusRekomendasi = 'PERHATIAN (MENDEKATI LIMIT)';
            $keteranganKapasitas = 'Kapasitas >= 80%. Sisa kuota survei: Rp '.number_format($sisaSurvei, 0, ',', '.');
        } elseif ($honorSurvei == 0 && $honorSensus == 0) {
            $statusRekomendasi = 'SIAP DITUGASKAN (BEBAS)';
            $keteranganKapasitas = 'Bebas tugas bulan ini. Kuota SBML utuh 100%.';
        } else {
            $statusRekomendasi = 'BISA DITUGASKAN (ADA SISA KUOTA)';
            $keteranganKapasitas = 'Tersedia sisa kuota survei: Rp '.number_format($sisaSurvei, 0, ',', '.');
        }

        $maxHonorTambahan = ($isOverLimit || $hasSensus) ? 0 : $sisaSurvei;

        $pctSurvei = $this->limitSurvei > 0 ? min(100, round(($honorSurvei / $this->limitSurvei) * 100, 1)) : 0;
        $pctSensus = $this->limitSensus > 0 ? min(100, round(($honorSensus / $this->limitSensus) * 100, 1)) : 0;

        $noWa = (string) ($mitra->nomor_wa ?: ($mitra->no_telp ?: '-'));
        $waUrl = $mitra->whatsapp_url ?: '-';
        $statusKemitraan = $mitra->kemitraans?->map(fn ($k) => "{$k->tahun}: {$k->status}")->implode(', ') ?: 'AKTIF';

        return [
            $this->rowNumber,
            $mitra->id_sobat ?? '-',
            $mitra->nama_1 ?? '-',
            $statusRekomendasi,
            $keteranganKapasitas,
            $jmlKegiatan.' Kegiatan',
            $kegiatanList,
            $hasSensus ? 'YA' : 'TIDAK',
            $honorSurvei,
            $this->limitSurvei,
            $sisaSurvei,
            $pctSurvei.'%',
            $honorSensus,
            $this->limitSensus,
            $sisaSensus,
            $pctSensus.'%',
            $maxHonorTambahan,
            $mitra->nik ? (string) $mitra->nik : '-',
            $noWa,
            $waUrl,
            $mitra->kecamatan_name ?? '-',
            $mitra->desa_name ?? '-',
            $mitra->posisi ?? '-',
            $statusKemitraan,
            $mitra->email ?? '-',
            $mitra->alamat_detail ?? '-',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_NUMBER,
            'I' => '"Rp "#,##0',
            'J' => '"Rp "#,##0',
            'K' => '"Rp "#,##0',
            'M' => '"Rp "#,##0',
            'N' => '"Rp "#,##0',
            'O' => '"Rp "#,##0',
            'Q' => '"Rp "#,##0',
            'R' => NumberFormat::FORMAT_TEXT,
            'S' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            6 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E40AF'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $monthName = $this->getMonthName($this->month);
                $filterDesc = $this->buildFilterDescription();

                $sheet->getRowDimension(6)->setRowHeight(28);

                $sheet->setCellValue('A1', 'DATA PERENCANAAN DAN MONITORING ALOKASI MITRA STATISTIK');
                $sheet->setCellValue('A2', "Periode: {$monthName} {$this->year} | {$filterDesc}");
                $sheet->setCellValue('A3', 'Batas SBML Sensus: Rp '.number_format($this->limitSensus, 0, ',', '.').' | Batas SBML Survei: Rp '.number_format($this->limitSurvei, 0, ',', '.'));
                $sheet->setCellValue('A4', 'Diekspor pada: '.now()->format('d/m/Y H:i:s').' WIB');

                $sheet->mergeCells('A1:L1');
                $sheet->mergeCells('A2:L2');
                $sheet->mergeCells('A3:L3');
                $sheet->mergeCells('A4:L4');

                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('1E3A8A');
                $sheet->getStyle('A2:A4')->getFont()->setSize(10)->getColor()->setRGB('475569');

                $sheet->freezePane('A7');

                $highestColumn = $sheet->getHighestColumn();
                $lastRow = max(6, $sheet->getHighestRow());
                $sheet->setAutoFilter("A6:{$highestColumn}6");

                $sheet->getStyle("G7:G{$lastRow}")->getAlignment()->setWrapText(true);

                for ($row = 7; $row <= $lastRow; $row++) {
                    $status = (string) $sheet->getCell("D{$row}")->getValue();

                    $bgColor = null;
                    $fontColor = null;

                    if (str_contains($status, 'SIAP DITUGASKAN')) {
                        $bgColor = 'DCFCE7';
                        $fontColor = '166534';
                    } elseif (str_contains($status, 'BISA DITUGASKAN')) {
                        $bgColor = 'DBEAFE';
                        $fontColor = '1E40AF';
                    } elseif (str_contains($status, 'SEDANG IKUT SENSUS')) {
                        $bgColor = 'FFEDD5';
                        $fontColor = '9A3412';
                    } elseif (str_contains($status, 'MENDEKATI LIMIT')) {
                        $bgColor = 'FEF9C3';
                        $fontColor = '854D0E';
                    } elseif (str_contains($status, 'OVER LIMIT')) {
                        $bgColor = 'FEE2E2';
                        $fontColor = '991B1B';
                    }

                    if ($bgColor && $fontColor) {
                        $sheet->getStyle("D{$row}")->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => $bgColor],
                            ],
                            'font' => [
                                'bold' => true,
                                'color' => ['rgb' => $fontColor],
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_CENTER,
                                'vertical' => Alignment::VERTICAL_CENTER,
                            ],
                        ]);
                    }

                    $waLink = (string) $sheet->getCell("T{$row}")->getValue();
                    if (str_starts_with($waLink, 'https://wa.me/')) {
                        $sheet->getCell("T{$row}")->getHyperlink()->setUrl($waLink);
                        $sheet->getStyle("T{$row}")->getFont()->getColor()->setRGB('2563EB');
                        $sheet->getStyle("T{$row}")->getFont()->setUnderline(true);
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

    protected function buildFilterDescription(): string
    {
        $parts = [];
        if ($this->kecamatanLabel) {
            $parts[] = "Kecamatan: {$this->kecamatanLabel}";
        }
        if ($this->desaLabel) {
            $parts[] = "Desa/Kel: {$this->desaLabel}";
        }

        return empty($parts) ? 'Cakupan: Seluruh Wilayah' : implode(', ', $parts);
    }
}
