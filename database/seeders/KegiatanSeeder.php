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
        Excel::import(new KegiatanImport,"./database/data/kegiatan.csv",readerType: ExcelExcel::CSV);
    }
}
