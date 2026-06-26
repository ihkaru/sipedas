<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AlokasiHonor;
use App\Models\Mitra;
use Carbon\Carbon;

function checkOverlaps($mitraId, $startDate, $endDate) {
    return AlokasiHonor::query()
        ->where('mitra_id', $mitraId)
        ->where(function($query) use ($startDate, $endDate) {
            $query->where('tanggal_mulai_perjanjian', '<=', $endDate)
                  ->where('tanggal_akhir_perjanjian', '>=', $startDate);
        })
        ->with('honor.kegiatanManmit')
        ->get();
}

// Check some random mitra's current allocations
$mitras = Mitra::has('alokasiHonors')->limit(5)->get();

foreach ($mitras as $mitra) {
    echo "Mitra: {$mitra->nama_1}\n";
    foreach ($mitra->alokasiHonors as $alokasi) {
        echo " - [{$alokasi->tanggal_mulai_perjanjian->format('Y-m-d')} s/d {$alokasi->tanggal_akhir_perjanjian->format('Y-m-d')}] ";
        echo "{$alokasi->honor->kegiatanManmit->nama} ({$alokasi->honor->kegiatanManmit->jenis_kegiatan})\n";
    }
    echo "\n";
}
