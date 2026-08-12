<?php

namespace Database\Seeders;

use App\Imports\PengajuanPembayaranImport;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Excel as ExcelExcel;
use Maatwebsite\Excel\Facades\Excel;

class PengajuanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (env("MIGRATION_ENV", "local")) $fileLocation = "./database/data/pengajuan.csv";
        if (!file_exists($fileLocation)) {
            $this->command->warn("File $fileLocation does not exist, skipping Pengajuan seeding.");
            return;
        }
        Excel::import(new PengajuanPembayaranImport, $fileLocation, readerType: ExcelExcel::CSV);
    }
}
