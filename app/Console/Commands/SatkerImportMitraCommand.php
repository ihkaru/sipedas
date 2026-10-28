<?php

namespace App\Console\Commands;

use App\Imports\MitraImport;
use App\Models\Mitra;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Excel as ExcelExcel;
use Maatwebsite\Excel\Facades\Excel;

class SatkerImportMitraCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'satker:import-mitra
        {--file= : Path ke berkas Excel (.xlsx) atau CSV Mitra}
        {--tahun=2026 : Tahun kemitraan}
        {--status=AKTIF : Status kemitraan (AKTIF/TIDAK_AKTIF)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data master Mitra Statistik BPS (dari ekspor SOBAT/KEPKA)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->components->info('Memulai import data master Mitra Statistik...');

        $filePath = $this->resolveFilePath();

        if (! $filePath || ! File::exists($filePath)) {
            $this->components->error('Berkas data mitra tidak ditemukan!');
            $this->components->warn('Letakkan berkas di [database/seeders/data/mitra.xlsx] atau [database/seeders/data/mitra.csv]');
            $this->components->warn('Gunakan template contoh di [database/seeders/data/mitra_sample.csv]');

            return Command::FAILURE;
        }

        $this->components->info("Membaca berkas: {$filePath}");

        $tahun = (int) $this->option('tahun');
        $status = (string) $this->option('status');
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        $readerType = ($extension === 'csv') ? ExcelExcel::CSV : ExcelExcel::XLSX;

        $countBefore = Mitra::count();
        Excel::import(new MitraImport($tahun, $status), $filePath, null, $readerType);
        $countAfter = Mitra::count();

        $imported = $countAfter - $countBefore;
        $this->components->success("Import Mitra Selesai! Total Mitra di database: {$countAfter} (Baru/Diperbarui: {$imported}) untuk Tahun {$tahun}.");

        return Command::SUCCESS;
    }

    /**
     * Resolve target file path.
     */
    protected function resolveFilePath(): ?string
    {
        if ($custom = $this->option('file')) {
            return base_path($custom);
        }

        $candidates = [
            database_path('seeders/data/mitra.xlsx'),
            database_path('seeders/data/mitra.csv'),
            database_path('data/mitra_2025.xlsx'),
            database_path('data/mitra_2024.xlsx'),
            database_path('seeders/data/mitra_sample.csv'),
        ];

        foreach ($candidates as $candidate) {
            if (File::exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
