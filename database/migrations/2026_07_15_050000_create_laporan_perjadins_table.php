<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('laporan_perjadins', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('penugasan_id')->unique();
            $table->foreign('penugasan_id')->references('id')->on('penugasans')->cascadeOnDelete();
            $table->date('tanggal_laporan');
            $table->json('isi_kegiatan')->nullable();
            $table->json('hasil_kegiatan')->nullable();
            $table->json('foto_dokumentasi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_perjadins');
    }
};
