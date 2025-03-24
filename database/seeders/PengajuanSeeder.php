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
        Excel::import(new PengajuanPembayaranImport, $fileLocation, readerType: ExcelExcel::CSV);
    }
}
