<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([PengaturanSeeder::class]);
        $this->call([AlokasiHonorSeeder::class]);
        $this->call([MitraSeeder::class]);
        $this->call([MasterSlsSeeder::class]);
        $this->call([NomorSuratSeeder::class]);
        $this->call([KegiatanSeeder::class]);
        $this->call([PegawaiSeeder::class]);
        $this->call([
            RoleSeeder::class,
            ShieldSeeder::class,
            TestAccountSeeder::class
        ]);
        $this->call([PlhSeeder::class]);

        $this->call([
            PenugasanSeeder::class
        ]);


    }
}
