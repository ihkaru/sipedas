<?php

namespace Database\Seeders;

use App\Models\Pegawai;
use App\Models\Plh;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlhSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $now = now();
        $plh = Pegawai::find("198008112005021004");
        Plh::jadikanPlh($plh,now()->format("d-m-Y"),now()->addDay(1)->format("d-m-Y"));
        Plh::jadikanPlh($plh,now()->addDay(3)->format("d-m-Y"),now()->addDay(4)->format("d-m-Y"));
    }
}
