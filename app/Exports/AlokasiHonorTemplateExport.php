<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;

class AlokasiHonorTemplateExport implements WithHeadings
{
    /**
     * Mendefinisikan baris header untuk template Excel.
     */
    public function headings(): array
    {
        return [
            'id_mitra',
            'id_honor',
            'target_per_satuan_honor',
        ];
    }
}
