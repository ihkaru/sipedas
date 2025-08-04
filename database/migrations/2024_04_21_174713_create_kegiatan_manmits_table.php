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
        Schema::create('kegiatan_manmits', function (Blueprint $table) {
            $table->string("id")->primary();
            $table->string('nama');
            $table->timestamp('tgl_mulai_pelaksanaan')->nullable();
            $table->timestamp('tgl_akhir_pelaksanaan')->nullable();
            $table->timestamp('tgl_mulai_penawaran')->nullable();
            $table->timestamp('tgl_akhir_penawaran')->nullable();
            $table->enum('jenis_kegiatan', ['SENSUS', 'SURVEI'])->nullable()->default("SURVEI");
            $table->enum('frekuensi_kegiatan', ['SUBROUND', 'TAHUNAN', 'TRIWULANAN', 'BULANAN'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kegiatan_manmits');
    }
};
