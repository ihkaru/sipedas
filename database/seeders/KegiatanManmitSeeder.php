<?php

namespace Database\Seeders;

use App\Imports\KegiatanManmitFromUploadImport;
use App\Imports\KegiatanManmitImport;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Excel as ExcelExcel;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;

class KegiatanManmitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (env("MIGRATION_ENV", "local")) $fileLocation = "./database/data/import_kegiatanmanmit.csv";
        Excel::import(new KegiatanManmitImport, $fileLocation, readerType: ExcelExcel::CSV);
        // Path ke file data Anda. Ganti nama file jika perlu.
        $fileLocation = database_path('data/kegiatan_manmit.xlsx');

        // Pastikan file ada sebelum mencoba mengimpor
        if (!file_exists($fileLocation)) {
            $this->command->warn('File data kegiatan_manmit.xlsx tidak ditemukan. Seeder dilewati.');
            return;
        }

        // Nonaktifkan foreign key check untuk truncate, lalu aktifkan kembali.
        Schema::disableForeignKeyConstraints();
        \App\Models\KegiatanManmit::truncate();
        Schema::enableForeignKeyConstraints();

        // Menggunakan kembali logika dari importer yang sudah ada
        Excel::import(new KegiatanManmitFromUploadImport(), $fileLocation, null, ExcelExcel::XLSX);

        $this->command->info('Seeding Kegiatan Manmit dari file XLSX selesai.');
    }
}
