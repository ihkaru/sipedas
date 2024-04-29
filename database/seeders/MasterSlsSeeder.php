<?php

namespace Database\Seeders;

use App\Imports\MasterSlsImport;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Excel as ExcelExcel;
use Maatwebsite\Excel\Facades\Excel;

class MasterSlsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Excel::import(new MasterSlsImport,"./database/data/master_sls.csv",readerType: ExcelExcel::CSV);
    }
}
