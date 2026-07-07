<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_ppks', function (Blueprint $table) {
            $table->id();
            $table->string('nip_ppk');
            $table->foreign('nip_ppk')->references('nip')->on('pegawais')->onDelete('cascade');
            $table->date('tgl_mulai');
            $table->date('tgl_selesai')->nullable()->comment('NULL = masih aktif sebagai PPK');
            $table->text('keterangan')->nullable()->comment('Alasan pergantian atau catatan tambahan');
            $table->timestamps();
        });

        // Seed data historis dari logika hardcoded sebelumnya
        DB::table('riwayat_ppks')->insert([
            [
                'nip_ppk'     => '199306062016021001', // Arief Yuandi
                'tgl_mulai'   => '2024-01-01',
                'tgl_selesai' => '2026-03-31',
                'keterangan'  => 'Data historis — dipindahkan dari kode hardcoded.',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'nip_ppk'     => '200001062023021001', // Budiman Aller Silaban
                'tgl_mulai'   => '2026-04-01',
                'tgl_selesai' => null,
                'keterangan'  => 'PPK aktif per 1 April 2026.',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_ppks');
    }
};
