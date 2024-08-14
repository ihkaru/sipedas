<?php

namespace Database\Seeders;

use App\Imports\KegiatanManmitImport;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Excel as ExcelExcel;
use Maatwebsite\Excel\Facades\Excel;

class KegiatanManmitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if(env("MIGRATION_ENV","local")) $fileLocation = "./database/data/import_kegiatanmanmit.csv";
        Excel::import(new KegiatanManmitImport,$fileLocation,readerType: ExcelExcel::CSV);
    }
}
