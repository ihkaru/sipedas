<?php

namespace App\Exports;

use App\Models\Sipancong\Pengajuan;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Database\Eloquent\Builder;

class PengajuanExport implements FromQuery, WithHeadings, ShouldAutoSize, WithMapping, WithStyles, WithTitle {
    protected Builder $query;

    public function __construct(Builder $query) {
        $this->query = $query;
    }

    public function query(): Builder {
        return $this->query->with([
            'pegawai',
            'penanggungJawab',
            'posisiDokumen',
            'subfungsi',
            'statusPembayaran',
            'jenisDokumen',
            'statusPengajuanPpk',
            'statusPengajuanPpspm',
            'statusPengajuanBendahara',
        ]);
    }

    public function title(): string {
        return 'Data Pengajuan';
    }

    public function headings(): array {
        return [
            'No. Pengajuan',
            'Tanggal Pengajuan',
            'Uraian Pengajuan',
            'Nominal',
            'Pengaju',
            'NIP Pengaju',
            'Penanggung Jawab',
            'NIP PJ',
            'Sub Fungsi',
            'Jenis Dokumen',
            'Posisi Dokumen',
            'Status PPK',
            'Status PPSPM',
            'Status Bendahara',
            'Status Pembayaran',
            'Catatan PPK',
            'Catatan PPSPM',
            'Catatan Bendahara',
            'Tanggal Bayar',
            'Link Folder',
            'Terakhir Diupdate',
        ];
    }

    public function map($pengajuan): array {
        return [
            $pengajuan->nomor_pengajuan,
            $pengajuan->created_at?->format('d/m/Y'),
            $pengajuan->uraian_pengajuan,
            $pengajuan->nominal_pengajuan,
            $pengajuan->pegawai?->nama ?? '-',
            $pengajuan->nip_pengaju,
            $pengajuan->penanggungJawab?->nama ?? '-',
            $pengajuan->nip_penanggung_jawab,
            $pengajuan->subfungsi?->nama ?? '-',
            $pengajuan->jenisDokumen?->nama ?? '-',
            $pengajuan->posisiDokumen?->nama ?? '-',
            $pengajuan->statusPengajuanPpk?->nama ?? '-',
            $pengajuan->statusPengajuanPpspm?->nama ?? '-',
            $pengajuan->statusPengajuanBendahara?->nama ?? '-',
            $pengajuan->statusPembayaran?->nama ?? '-',
            $pengajuan->catatan_ppk ?? '-',
            $pengajuan->catatan_ppspm ?? '-',
            $pengajuan->catatan_bendahara ?? '-',
            $pengajuan->tanggal_bayar?->format('d/m/Y') ?? '-',
            $pengajuan->link_folder_dokumen ?? '-',
            $pengajuan->updated_at?->format('d/m/Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet): array {
        return [
            // Header row styling
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'],
                ],
            ],
        ];
    }
}
