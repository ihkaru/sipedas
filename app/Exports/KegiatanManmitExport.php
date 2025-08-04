<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;

class KegiatanManmitExport implements WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function headings(): array
    {
        // Ini adalah header yang akan dilihat oleh pengguna di file Excel
        return [
            'ID Kegiatan',
            'Nama Kegiatan',
            'Jenis Kegiatan',
            'Tanggal Mulai',
            'Tanggal Selesai',
            'Frekuensi Kegiatan',
        ];
    }
}
