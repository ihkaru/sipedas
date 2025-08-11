<?php

namespace Database\Seeders;

use App\Imports\AlokasiHonorImport;
use App\Models\AlokasiHonor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Excel as ExcelExcel;
use Maatwebsite\Excel\Facades\Excel;

class AlokasiHonorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fileLocation = database_path('data/alokasi_honor.xlsx');

        if (!file_exists($fileLocation)) {
            $this->command->warn('File data alokasi_honor.xlsx tidak ditemukan. Seeder dilewati.');
            return;
        }

        Schema::disableForeignKeyConstraints();
        AlokasiHonor::truncate();
        Schema::enableForeignKeyConstraints();

        // Menggunakan kembali logika dari HonorImport
        Excel::import(new AlokasiHonorImport(), $fileLocation, null, ExcelExcel::XLSX);

        $this->command->info('Seeding Alokasi Honor dari file XLSX selesai.');
    }
}
