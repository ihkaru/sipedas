<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pengaturan untuk otomatisasi PPK
        Setting::updateOrCreate(
            ['key' => 'ppk_instant_auto_approve'],
            ['value' => '0'] // Default: false (tidak aktif)
        );

        Setting::updateOrCreate(
            ['key' => 'ppk_auto_approve_days'],
            ['value' => '0'] // Default: 0 hari (tidak aktif)
        );

        // Anda bisa menambahkan pengaturan lain di sini di masa depan
        // Setting::updateOrCreate(
        //     ['key' => 'nama_aplikasi'],
        //     ['value' => 'DOKTER-V']
        // );
    }
}
