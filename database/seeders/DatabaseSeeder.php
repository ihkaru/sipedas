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
        // $this->call([AlokasiHonorSeeder::class]);
        // $this->call([MitraSeeder::class]);
        $this->call([MasterSlsSeeder::class]);
        $this->call([NomorSuratSeeder::class]);
        // $this->call([KegiatanManmitSeeder::class]);
        $this->call([KegiatanSeeder::class]);
        $this->call([PegawaiSeeder::class]);
        $this->call([
            RoleSeeder::class,
            ShieldSeeder::class,
            TestAccountSeeder::class
        ]);
        $this->call([PlhSeeder::class]);

        // $this->call([
        //     SuratTugasSeeder::class
        // ]);
        $this->call([
            MicrositeSeeder::class,
        ]);
        $this->call([
            SipancongSeeder::class,
            // PengajuanSeeder::class
        ]);

        // 1. Seed Mitra (tidak ada dependensi ke yang lain)
        $this->call([MitraSeeder::class]);

        // 2. Seed Kegiatan Manmit (harus ada sebelum Honor)
        $this->call([KegiatanManmitSeeder::class]);

        // 3. Seed Honor (bergantung pada KegiatanManmit)
        $this->call([HonorSeeder::class]);

        // 4. Seeder-seeder lain yang mungkin bergantung pada data master di atas
        $this->call([
            KegiatanSeeder::class, // Jika ini bergantung pada KegiatanManmit
            NomorSuratSeeder::class,
            AlokasiHonorSeeder::class, // Dijalankan terakhir, hanya untuk truncate
            SuratTugasSeeder::class,
            PengajuanSeeder::class
        ]);
    }
}
