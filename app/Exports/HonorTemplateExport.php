<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;

class HonorTemplateExport implements WithHeadings
{
    /**
     * Mendefinisikan baris header untuk template Excel.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID Kegiatan',
            'Jabatan',
            'Satuan Honor',
            'Harga per Satuan Honor',
            'Jenis Honor',
            'Tanggal Akhir Kegiatan',
        ];
    }
}
