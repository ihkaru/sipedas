<?php

namespace Database\Seeders;

use App\Imports\MitrasImport;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Excel as ExcelExcel;
use Maatwebsite\Excel\Facades\Excel;

class MitraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if(env("MIGRATION_ENV","local")) $fileLocation = "./database/data/mitra.csv";
        // if(env("MIGRATION_ENV","production")) $fileLocation = "./../database/data/master_sls.csv";
        Excel::import(new MitrasImport,$fileLocation,readerType: ExcelExcel::CSV);
    }
}
