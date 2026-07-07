<?php

namespace App\Observers;

use App\Models\Honor;
use App\Services\HonorTanggalService;

class HonorObserver
{
    public function updated(Honor $honor): void
    {
        if (!$honor->wasChanged('tanggal_akhir_kegiatan')) {
            return;
        }

        HonorTanggalService::propagate($honor);
    }
}
