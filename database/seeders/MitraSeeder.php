<?php

namespace Database\Seeders;

use App\Imports\MitraImport;
use App\Imports\MitrasImport;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Excel as ExcelExcel;
use Maatwebsite\Excel\Facades\Excel;

class MitraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Path ke file data Anda. Ganti nama file jika perlu.
        $fileLocation2024 = database_path('data/mitra_2024.xlsx');
        $fileLocation2025 = database_path('data/mitra_2025.xlsx');
        $fileLocation2025tambahan = database_path('data/mitra_2025_tambahan.xlsx');

        if (!file_exists($fileLocation2024) || !file_exists($fileLocation2025) || !file_exists($fileLocation2025tambahan)) {
            $this->command->warn('File data mitra ada yang tidak ditemukan. Seeder dilewati.');
            return;
        }

        Schema::disableForeignKeyConstraints();
        \App\Models\Mitra::truncate();
        \App\Models\Kemitraan::truncate(); // Truncate juga tabel kemitraan
        Schema::enableForeignKeyConstraints();

        // Karena Importer butuh tahun dan status, kita berikan nilai default.
        $statusDefault = 'AKTIF'; // Pastikan nilai ini ada di ENUM atau konstanta Anda

        // Menggunakan kembali logika dari MitraImport
        Excel::import(new MitraImport(2024, $statusDefault), $fileLocation2024, null, ExcelExcel::XLSX);
        Excel::import(new MitraImport(2025, $statusDefault), $fileLocation2025, null, ExcelExcel::XLSX);
        Excel::import(new MitraImport(2025, $statusDefault), $fileLocation2025tambahan, null, ExcelExcel::XLSX);



        $this->command->info('Seeding Mitra dan Kemitraan dari file XLSX selesai.');
    }
}
