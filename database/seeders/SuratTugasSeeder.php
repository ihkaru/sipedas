<?php

namespace Database\Seeders;

use App\Imports\SuratTugasImport;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Excel as ExcelExcel;
use Maatwebsite\Excel\Facades\Excel;

class SuratTugasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if(env("MIGRATION_ENV","local")) $fileLocation = "./database/data/import_surtug.csv";
        Excel::import(new SuratTugasImport,$fileLocation,readerType: ExcelExcel::CSV);
    }
}
