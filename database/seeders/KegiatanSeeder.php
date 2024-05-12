<?php

namespace Database\Seeders;

use App\Imports\KegiatanImport;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Excel as ExcelExcel;
use Maatwebsite\Excel\Facades\Excel;

class KegiatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fileLocation = "";
        if(env("MIGRATION_ENV","local")) $fileLocation = "./database/data/kegiatan.csv";
        // if(env("MIGRATION_ENV","production")) $fileLocation = "./../database/data/kegiatan.csv";
        Excel::import(new KegiatanImport,$fileLocation,readerType: ExcelExcel::CSV);
    }
}
