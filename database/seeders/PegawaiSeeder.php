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
            "nama"=>"Muh Saichudin S.Si, M.Si",
            "nip"=>"197109071992111001",
            "nip9"=>"340013380 ",
            "pangkat"=>"-",
            "golongan"=>"-",
            "jabatan"=>"Kepala BPS Provinsi Kalimantan Barat",
            "email"=>"saichudin@bps.go.id",
            "unit_kerja"=>"BPS Provinsi Kalimantan Barat"
        ]);
        Pegawai::create([
            "nama"=>"Munawir, SE.,MM",
            "nip"=>"196908031992111001",
            "nip9"=>"340013391",
            "pangkat"=>"-",
            "golongan"=>"-",
            "jabatan"=>"Kepala BPS Kabupaten Mempawah",
            "email"=>"munawir@bps.go.id",
            "atasan_langsung_id"=>"196908031992111001",
            "unit_kerja"=>"BPS Kabupaten Mempawah"
        ]);
        Pegawai::create([
            "nama"=>"Muhammad Adwin, S.Mn.",
            "nip"=>"198008112005021004",
            "nip9"=>"340061823",
            "pangkat"=>"-",
            "golongan"=>"-",
            "jabatan"=>"Kasubbag Umum",
            "email"=>"muh.adwin@bps.go.id",
            "atasan_langsung_id"=>"198008112005021004",
            "unit_kerja"=>"BPS Kabupaten Mempawah"
        ]);
        Pegawai::create([
            "nama"=>"Rifky Mullah Syadriawan A.Md.Stat.",
            "nip"=>"200110202023021003",
            "nip9"=>"340062230",
            "pangkat"=>"-",
            "golongan"=>"-",
            "jabatan"=>"-",
            "email"=>"mullahrifky@bps.go.id",
            "atasan_langsung_id"=>"198008112005021004",
            "unit_kerja"=>"BPS Kabupaten Mempawah"
        ]);


        Pegawai::factory(20)->create();
    }
}
