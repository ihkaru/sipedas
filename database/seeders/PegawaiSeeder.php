<?php

namespace Database\Seeders;

use App\Models\Pegawai;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PegawaiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Pegawai::create([
            "nama"=>"Muhammad Adwin, S.Mn.",
            "id"=>"198008112005021004",
            "nip9"=>"340061823",
            "pangkat"=>"-",
            "golongan"=>"-",
            "jabatan"=>"Kasubbag Umum",
            "email"=>"muh.adwin@bps.go.id",
            "atasan_langsung_id"=>"198008112005021004",
            "unit_kerja"=>"BPS Kabupaten Mempawah"
        ]);
    }
}
