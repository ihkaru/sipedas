<?php

namespace Database\Seeders;

use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@admin.com',
            'password'=>Hash::make("password123")
        ])->assignRole("super_admin");
        User::factory()->create([
            'name' => 'Ihza Fikri Zaki Karunia',
            'email' => 'ihzakarunia@bps.go.id',
            'password'=>Hash::make("fikrizaki2")
        ])->assignRole(["super_admin"]);
        User::factory()->create([
            'name' => 'Muhammad Adwin, S.Mn.',
            'email' => 'muh.adwin@bps.go.id',
            'password'=>Hash::make("muh.adwin")
        ])->assignRole(["operator_umum"]);
        User::factory()->create([
            'name' => 'Rifky Mullah Syadriawan, A.Md.Stat',
            'email' => 'mullahrifky@bps.go.id',
            'password'=>Hash::make("mullahrifky")
        ])->assignRole(["operator_umum"]);
        User::factory()->create([
            'name' => "Munawir, SE.,MM",
            'email' => "munawir@bps.go.id",
            'password'=>Hash::make("munawir123")
        ])->assignRole(["kepala_satker"]);

        foreach(Pegawai::get() as $p){
            User::updateOrCreate([
                'email'=>$p->email,
            ],[
                'name'=>$p->nama,
                'password'=>Hash::make(explode("@",$p->email)[0])
            ])->assignRole(["pegawai"]);
        }

    }
}
