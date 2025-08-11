<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\HonorImport;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Excel as ExcelExcel;

class HonorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Path ke file data Anda. Ganti nama file jika perlu.
        $fileLocation = database_path('data/honor.xlsx');

        if (!file_exists($fileLocation)) {
            $this->command->warn('File data honor.xlsx tidak ditemukan. Seeder dilewati.');
            return;
        }

        Schema::disableForeignKeyConstraints();
        \App\Models\Honor::truncate();
        Schema::enableForeignKeyConstraints();

        // Menggunakan kembali logika dari HonorImport
        Excel::import(new HonorImport(), $fileLocation, null, ExcelExcel::XLSX);

        $this->command->info('Seeding Honor dari file XLSX selesai.');
    }
}
