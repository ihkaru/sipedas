<?php

namespace App\Exports;

use App\Models\Penugasan;
use App\Supports\Constants;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PenugasanDisetujuiExport implements FromCollection, WithHeadings, WithColumnWidths, WithMapping, WithStyles, WithTitle
{
    protected Builder|Collection|null $queryOrRecords;
    protected int $rowNumber = 0;

    public function __construct(Builder|Collection|null $queryOrRecords = null)
    {
        @ini_set('max_execution_time', '300');
        @set_time_limit(300);
        $this->queryOrRecords = $queryOrRecords;
    }

    public function collection(): Collection
    {
        @ini_set('max_execution_time', '300');
        @set_time_limit(300);

        $eagerLoads = [
            'pegawai',
            'mitra',
            'kegiatan',
            'riwayatPengajuan',
            'suratTugas',
            'tujuanSuratTugas.provinsi',
            'tujuanSuratTugas.kabkot',
            'tujuanSuratTugas.kecamatan',
            'tujuanSuratTugas.desa',
            'plh',
        ];

        if ($this->queryOrRecords instanceof Collection) {
            return $this->queryOrRecords;
        }

        if ($this->queryOrRecords instanceof Builder) {
            return (clone $this->queryOrRecords)
                ->with($eagerLoads)
                ->get();
        }

        return Penugasan::query()
            ->whereHas('riwayatPengajuan', function ($query) {
                $query->whereIn('status', [
                    Constants::STATUS_PENGAJUAN_DISETUJUI,
                    Constants::STATUS_PENGAJUAN_DICETAK,
                    Constants::STATUS_PENGAJUAN_DIKUMPULKAN,
                    Constants::STATUS_PENGAJUAN_DICAIRKAN,
                ]);
            })
            ->with($eagerLoads)
            ->orderBy('tgl_mulai_tugas', 'desc')
            ->get();
    }

    public function title(): string
    {
        return 'Surat Tugas Disetujui';
    }

    public function headings(): array
    {
        return [
            'No.',
            'No. Surat Tugas',
            'Nama Petugas',
            'Jenis Petugas',
            'NIP / NIK / ID Sobat',
            'Nama Kegiatan',
            'Lokasi Penugasan',
            'Tanggal Diajukan',
            'Tanggal Mulai Tugas',
            'Tanggal Selesai Tugas',
            'Tanggal Perjadin',
            'Lama (Hari)',
            'Jenis Surat Tugas',
            'Transportasi',
            'Status',
            'Penyetuju (PLH)',
            'Tanggal Dibuat',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 25,
            'C' => 30,
            'D' => 15,
            'E' => 22,
            'F' => 35,
            'G' => 30,
            'H' => 15,
            'I' => 15,
            'J' => 15,
            'K' => 25,
            'L' => 12,
            'M' => 20,
            'N' => 20,
            'O' => 25,
            'P' => 30,
            'Q' => 18,
        ];
    }

    public function map($record): array
    {
        $this->rowNumber++;

        $noSuratTugas = $record->suratTugas->nomor_surat_tugas ?? ($record->surat_tugas_id ? "ID: {$record->surat_tugas_id}" : '-');
        $nipOrSobat = $record->is_mitra ? ($record->id_sobat ?? '-') : ($record->nip ?? '-');

        $tglPengajuan = $record->tgl_pengajuan_tugas ? Carbon::parse($record->tgl_pengajuan_tugas)->format('d/m/Y') : '-';
        $tglMulai = $record->tgl_mulai_tugas ? Carbon::parse($record->tgl_mulai_tugas)->format('d/m/Y') : '-';
        $tglAkhir = $record->tgl_akhir_tugas ? Carbon::parse($record->tgl_akhir_tugas)->format('d/m/Y') : '-';
        $tglDibuat = $record->created_at ? Carbon::parse($record->created_at)->format('d/m/Y H:i') : '-';

        $statusRaw = $record->riwayatPengajuan?->status;
        $statusLabel = Constants::STATUS_PENGAJUAN_OPTIONS[$statusRaw] ?? $statusRaw ?? '-';

        return [
            $this->rowNumber,
            $noSuratTugas,
            $record->tertugas ?? '-',
            $record->jenis_petugas ?? '-',
            $nipOrSobat,
            $record->kegiatan->nama ?? '-',
            $record->tujuan_penugasan ?: '-',
            $tglPengajuan,
            $tglMulai,
            $tglAkhir,
            $record->tgl_perjadin ?? '-',
            $record->lama_perjadin ?? 1,
            $record->jenis_surat ?? '-',
            $record->jenis_transportasi ?? '-',
            $statusLabel,
            $record->plh->nama ?? '-',
            $tglDibuat,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E40AF'], // Dark Blue
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }
}
