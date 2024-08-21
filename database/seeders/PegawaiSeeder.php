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
            "nama"=>"Munawir, SE.,MM",
            "nip"=>"196908031992111001",
            "nip9"=>"340013391",
            "pangkat"=>"IV",
            "golongan"=>"b",
            "jabatan"=>"Kepala BPS Kabupaten Mempawah",
            "email"=>"munawir@bps.go.id",
            "atasan_langsung_id"=>"196908031992111001",
            "unit_kerja"=>"BPS Kabupaten Mempawah"
        ]);
        Pegawai::create([
            "nama"=>"Muhammad Adwin, S.Mn.",
            "nip"=>"198008112005021004",
            "nip9"=>"340017573",
            "pangkat"=>"III",
            "golongan"=>"b",
            "jabatan"=>"Kepala Sub Bagian Umum",
            "email"=>"muh.adwin@bps.go.id",
            "atasan_langsung_id"=>"196908031992111001",
            "unit_kerja"=>"BPS Kabupaten Mempawah"
        ]);
        Pegawai::create([
            "nama"=>"Rifky Mullah Syadriawan A.Md.Stat.",
            "nip"=>"200110202023021003",
            "nip9"=>"340062230",
            "pangkat"=>"II",
            "golongan"=>"c",
            "jabatan"=>"Pengatur",
            "email"=>"mullahrifky@bps.go.id",
            "atasan_langsung_id"=>"196908031992111001",
            "unit_kerja"=>"BPS Kabupaten Mempawah"
        ]);
        Pegawai::create([
            "nama"=>"Ihza Fikri Zaki Karunia, S.Tr.Stat.",
            "nip"=>"199910282023021005",
            "nip9"=>"340061823",
            "pangkat"=>"III",
            "golongan"=>"a",
            "jabatan"=>"Pranata Komputer Ahli Pertama",
            "email"=>"ihzakarunia@bps.go.id",
            "atasan_langsung_id"=>"196908031992111001",
            "unit_kerja"=>"BPS Kabupaten Mempawah"
        ]);
        Pegawai::create([
            "nama"=>"Abdul Karim",
            "nip"=>"196901192007011004",
            "nip9"=>"340019770",
            "pangkat"=>"II",
            "golongan"=>"c",
            "jabatan"=>"Pengolah Data",
            "email"=>"abdul.karim@bps.go.id",
            "atasan_langsung_id"=>"196908031992111001",
            "unit_kerja"=>"BPS Kabupaten Mempawah"
        ]);
        Pegawai::create([
            "nama"=>"Budiman Aller Silaban S.Tr.Stat.",
            "nip"=>"200001062023021001",
            "nip9"=>"340061717",
            "pangkat"=>"III",
            "golongan"=>"a",
            "jabatan"=>"Statistisi Ahli Pertama",
            "email"=>"allersilaban@bps.go.id",
            "atasan_langsung_id"=>"196908031992111001",
            "unit_kerja"=>"BPS Kabupaten Mempawah"
        ]);
        Pegawai::create([
            "nama"=>"Arief Yuandi SST",
            "nip"=>"199306062016021001",
            "nip9"=>"340057298",
            "pangkat"=>"III",
            "golongan"=>"c",
            "jabatan"=>"Pranata Komputer Ahli Muda",
            "email"=>"arief.yuandi@bps.go.id",
            "atasan_langsung_id"=>"196908031992111001",
            "unit_kerja"=>"BPS Kabupaten Mempawah"
        ]);
        Pegawai::create([
            "nama"=>"Arini Faurizah S.Tr.Stat",
            "nip"=>"199605222019012001",
            "nip9"=>"340058620",
            "pangkat"=>"III",
            "golongan"=>"b",
            "jabatan"=>"Statistisi Ahli Pertama",
            "email"=>"arinif@bps.go.id",
            "atasan_langsung_id"=>"196908031992111001",
            "unit_kerja"=>"BPS Kabupaten Mempawah"
        ]);
        Pegawai::create([
            "nama"=>"Arsita Indah Wahyuni A.Md.Stat.",
            "nip"=>"199905272023022001",
            "nip9"=>"340062135",
            "pangkat"=>"II",
            "golongan"=>"c",
            "jabatan"=>"Pengatur",
            "email"=>"arsita.indah@bps.go.id",
            "atasan_langsung_id"=>"196908031992111001",
            "unit_kerja"=>"BPS Kabupaten Mempawah"
        ]);
        Pegawai::create([
            "nama"=>"Firmansyah S.E.",
            "nip"=>"198103182011011003",
            "nip9"=>"340055280",
            "pangkat"=>"III",
            "golongan"=>"d",
            "jabatan"=>"Statistisi Ahli Pertama",
            "email"=>"firmansyah2@bps.go.id",
            "atasan_langsung_id"=>"196908031992111001",
            "unit_kerja"=>"BPS Kabupaten Mempawah"
        ]);
        Pegawai::create([
            "nama"=>"Listio Jati Nandhiko S.Tr.Stat.",
            "nip"=>"199612302019011001",
            "nip9"=>"340058814",
            "pangkat"=>"III",
            "golongan"=>"b",
            "jabatan"=>"Statistisi Ahli Pertama",
            "email"=>"listiojati@bps.go.id",
            "atasan_langsung_id"=>"196908031992111001",
            "unit_kerja"=>"BPS Kabupaten Mempawah"
        ]);
        Pegawai::create([
            "nama"=>"Maria Sintauli Hutauruk S.Tr.Stat.",
            "nip"=>"199608042019012001",
            "nip9"=>"340058823",
            "pangkat"=>"III",
            "golongan"=>"b",
            "jabatan"=>"Statistisi Ahli Pertama",
            "email"=>"mariash@bps.go.id",
            "atasan_langsung_id"=>"196908031992111001",
            "unit_kerja"=>"BPS Kabupaten Mempawah"
        ]);
        Pegawai::create([
            "nama"=>"Najia Helmiah S.Tr.Stat.",
            "nip"=>"200002042022012003",
            "nip9"=>"340060805",
            "pangkat"=>"III",
            "golongan"=>"a",
            "jabatan"=>"Statistisi Ahli Pertama",
            "email"=>"najia.helmiah@bps.go.id",
            "atasan_langsung_id"=>"196908031992111001",
            "unit_kerja"=>"BPS Kabupaten Mempawah"
        ]);
        Pegawai::create([
            "nama"=>"Safira Nurrosyid S.Tr.Stat.",
            "nip"=>"199606062019122001",
            "nip9"=>"340059760 ",
            "pangkat"=>"III",
            "golongan"=>"a",
            "jabatan"=>"Statistisi Ahli Pertama",
            "email"=>"safira.nurrosyid@bps.go.id",
            "atasan_langsung_id"=>"196908031992111001",
            "unit_kerja"=>"BPS Kabupaten Mempawah"
        ]);
        Pegawai::create([
            "nama"=>"Sarah Pratiwi S.Tr.Stat.",
            "nip"=>"199605162019012001",
            "nip9"=>"340058975",
            "pangkat"=>"III",
            "golongan"=>"b",
            "jabatan"=>"Statistisi Ahli Pertama",
            "email"=>"sarah.pratiwi@bps.go.id",
            "atasan_langsung_id"=>"196908031992111001",
            "unit_kerja"=>"BPS Kabupaten Mempawah"
        ]);
        Pegawai::create([
            "nama"=>"Syarifah Apriani ST",
            "nip"=>"198204042005022005",
            "nip9"=>"340017570",
            "pangkat"=>"III",
            "golongan"=>"c",
            "jabatan"=>"Statistisi Ahli Muda",
            "email"=>"sy.apriani@bps.go.id",
            "atasan_langsung_id"=>"196908031992111001",
            "unit_kerja"=>"BPS Kabupaten Mempawah"
        ]);
        Pegawai::create([
            "nama"=>"Wantri Mukti Lestari S.Si.",
            "nip"=>"199101072019032001",
            "nip9"=>"340059242",
            "pangkat"=>"III",
            "golongan"=>"b",
            "jabatan"=>"Bendahara Pengeluaran",
            "email"=>"wantri.mukti@bps.go.id",
            "atasan_langsung_id"=>"196908031992111001",
            "unit_kerja"=>"BPS Kabupaten Mempawah"
        ]);
        Pegawai::create([
            "nama"=>"Yulfi Ramanda SE.",
            "nip"=>"198007142011011003",
            "nip9"=>"340055305",
            "pangkat"=>"III",
            "golongan"=>"b",
            "jabatan"=>"Statistisi Ahli Pertama",
            "email"=>"yulfi.ramanda@bps.go.id",
            "atasan_langsung_id"=>"196908031992111001",
            "unit_kerja"=>"BPS Kabupaten Mempawah"
        ]);
        // $pegawais = Pegawai::factory(20)->create();
    }
}
