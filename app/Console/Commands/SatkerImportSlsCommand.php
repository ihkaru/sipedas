<?php

namespace App\Console\Commands;

use App\Imports\MasterSlsImport;
use App\Models\MasterSls;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Excel as ExcelExcel;
use Maatwebsite\Excel\Facades\Excel;

class SatkerImportSlsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'satker:import-sls {--file= : Path ke file CSV Master SLS Wilkerstat}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data Master SLS Wilkerstat BPS dari file CSV';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->components->info('Memulai import data Master SLS Wilkerstat...');

        $fileLocation = $this->resolveFilePath();

        if (! $fileLocation || ! File::exists($fileLocation)) {
            $this->components->error('Berkas CSV Master SLS tidak ditemukan!');
            $this->components->warn('Silakan letakkan berkas CSV di [database/seeders/data/master_sls.csv]');
            $this->components->warn('Atau rujuk template contoh di [database/seeders/data/master_sls_sample.csv]');
            $this->components->warn('Gunakan opsi [--file=/path/ke/file.csv] jika berkas berada di lokasi lain.');

            return Command::FAILURE;
        }

        $this->components->info("Membaca berkas: {$fileLocation}");

        $countBefore = MasterSls::count();
        Excel::import(new MasterSlsImport, $fileLocation, readerType: ExcelExcel::CSV);
        $countAfter = MasterSls::count();

        $imported = $countAfter - $countBefore;
        $this->components->success("Import Master SLS Selesai! Total SLS di database: {$countAfter} (Baru/Diperbarui: {$imported})");

        return Command::SUCCESS;
    }

    /**
     * Resolve the target file path.
     */
    protected function resolveFilePath(): ?string
    {
        if ($custom = $this->option('file')) {
            return base_path($custom);
        }

        $candidates = [
            database_path('seeders/data/master_sls.csv'),
            database_path('data/master_sls.csv'),
            database_path('seeders/data/master_sls_sample.csv'),
        ];

        foreach ($candidates as $candidate) {
            if (File::exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
