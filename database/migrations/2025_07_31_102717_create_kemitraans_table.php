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
        Schema::create('kemitraans', function (Blueprint $table) {
            $table->id();

            // Foreign key ke tabel mitras
            $table->unsignedBigInteger('mitra_id');
            $table->foreign('mitra_id')->references('id')->on('mitras')->onDelete('cascade');

            // Tahun kemitraan ini berlaku
            $table->year('tahun');

            // Status untuk kemitraan di tahun tersebut
            $table->enum('status', ['AKTIF', 'TIDAK_AKTIF', 'BLACKLISTED'])->default('AKTIF');

            // Pastikan seorang mitra hanya punya satu entri per tahun
            $table->unique(['mitra_id', 'tahun']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kemitraans');
    }
};
