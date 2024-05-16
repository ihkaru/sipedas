<?php

namespace Database\Seeders;

use App\Imports\NomorSuratImport;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Excel as ExcelExcel;
use Maatwebsite\Excel\Facades\Excel;

class NomorSuratSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if(env("MIGRATION_ENV","local")) $fileLocation = "./database/data/nomor_surat.csv";
        // if(env("MIGRATION_ENV","production")) $fileLocation = "./../database/data/master_sls.csv";
        Excel::import(new NomorSuratImport,$fileLocation,readerType: ExcelExcel::CSV);
    }
}
