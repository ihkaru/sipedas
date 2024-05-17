<?php

namespace Database\Seeders;

use App\Imports\AlokasiHonorImport;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Excel as ExcelExcel;
use Maatwebsite\Excel\Facades\Excel;

class AlokasiHonorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if(env("MIGRATION_ENV","local")) $fileLocation = "./database/data/alokasi_honor.csv";
        // if(env("MIGRATION_ENV","production")) $fileLocation = "./../database/data/master_sls.csv";
        Excel::import(new AlokasiHonorImport,$fileLocation,readerType: ExcelExcel::CSV);
    }
}
