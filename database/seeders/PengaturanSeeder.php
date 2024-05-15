<?php

namespace Database\Seeders;

use App\Models\Pengaturan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PengaturanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Pengaturan::create([
            "nilai"=>13,
            "key"=>"SURTUG_JML_HARI_BATAS_PENGEMBALIAN",
            "deskripsi"=>"Jumlah hari untuk batas pengembalian dari tanggal terakhir penugasan"
        ]);
        Pengaturan::create([
            "nilai"=>10,
            "key"=>"SURTUG_JML_HARI_MULAI_PENGINGAT_SURTUG",
            "deskripsi"=>"Jumlah hari untuk memulai pengingat pengembalian dari tanggal terakhir penugasan"
        ]);
        Pengaturan::create([
            "nilai"=>17,
            "key"=>"SURTUG_JML_HARI_BATAS_PENCAIRAN",
            "deskripsi"=>"Jumlah hari untuk batas pencairan dari tanggal terakhir penugasan"
        ]);
        Pengaturan::create([
            "nilai"=>"196908031992111001",
            "key"=>"ID_PLH_DEFAULT",
            "deskripsi"=>"NIP PLH Default"
        ]);
        Pengaturan::create([
            "nilai"=>"KALIMANTAN BARAT",
            "key"=>"NAMA_PROVINSI",
            "deskripsi"=>"Nama Provinsi Satuan Kerja"
        ]);

        Pengaturan::create([
            "nilai"=>"KABUPATEN MEMPAWAH",
            "key"=>"NAMA_KAKO",
            "deskripsi"=>"Nama Kabupaten Satuan Kerja"
        ]);
        Pengaturan::create([
            "nilai"=>"KAKO",
            "key"=>"LEVEL_SATUAN_KERJA",
            "deskripsi"=>"Level satuan kerja: PROVINSI, KAKO"
        ]);
        Pengaturan::create([
            "nilai"=>"199306062016021001",
            "key"=>"NIP_PPK_SATER",
            "deskripsi"=>"NIP dari PPK Satker"
        ]);
    }
}
