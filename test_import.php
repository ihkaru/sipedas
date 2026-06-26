<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Imports\MitraImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

try {
    echo "Starting import test in transaction...\n";
    $filePath = '/app/references/data/2026_6104_exportmitrakepka_2026-06-04_134209.xlsx';
    
    if (!file_exists($filePath)) {
        throw new Exception("File not found: " . $filePath);
    }
    
    DB::beginTransaction();
    
    $importer = new MitraImport(2026, 'AKTIF');
    Excel::import($importer, $filePath);
    
    echo "Import execution finished successfully!\n";
    
    // Let's count how many mitras are in database now, or how many rows were processed.
    $mitrasCount = \App\Models\Mitra::count();
    echo "Total Mitras in database inside transaction: " . $mitrasCount . "\n";
    
    // Print a few sample imported records
    $samples = \App\Models\Mitra::orderBy('id', 'desc')->limit(5)->get();
    echo "Sample imported records:\n";
    foreach ($samples as $index => $mitra) {
        echo " - #" . ($index + 1) . ": Sobat ID: {$mitra->sobat_id}, Name: {$mitra->nama_1}, Status: {$mitra->status_seleksi}, Prov: {$mitra->alamat_prov}, Kab: {$mitra->alamat_kab}, Kec: {$mitra->alamat_kec}, Desa: {$mitra->alamat_desa}, Lahir: {$mitra->tanggal_lahir}, SP: {$mitra->pernah_sp}, ST: {$mitra->pernah_st}\n";
    }
    
    DB::rollBack();
    echo "Transaction rolled back. Database remains clean.\n";
    
} catch (\Throwable $e) {
    if (DB::transactionLevel() > 0) {
        DB::rollBack();
    }
    echo "ERROR during import test:\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
