<?php

namespace Database\Seeders;

use App\Models\Pegawai;
use App\Models\Pengaturan;
use App\Models\Plh;
use App\Supports\Constants;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use PHPUnit\TextUI\Configuration\Constant;

class PlhSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $now = now();
        $plh = Pegawai::find("198008112005021004");
        $pegawaiDigantikan = Pegawai::find(Pengaturan::key("ID_PLH_DEFAULT")->nilai);
        Plh::jadikanPlh($plh,$pegawaiDigantikan,now()->format("d-m-Y"),now()->addDay(1)->format("d-m-Y"));
        Plh::jadikanPlh($plh,$pegawaiDigantikan,now()->addDay(3)->format("d-m-Y"),now()->addDay(4)->format("d-m-Y"));
    }
}
