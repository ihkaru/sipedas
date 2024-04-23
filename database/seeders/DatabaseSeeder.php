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

        User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@admin.com',
            'password'=>Hash::make("password123")
        ]);
        User::factory()->create([
            'name' => 'Ihza Fikri Zaki Karunia',
            'email' => 'ihzakarunia@bps.go.id',
            'password'=>Hash::make("fikrizaki2")
        ]);
        User::factory()->create([
            'name' => 'Muhammad Adwin, S.Mn.',
            'email' => 'muh.adwin@bps.go.id',
            'password'=>Hash::make("muh.adwin")
        ]);
    }
}
