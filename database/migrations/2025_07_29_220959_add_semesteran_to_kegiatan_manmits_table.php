<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kegiatan_manmits', function (Blueprint $table) {
            // Menambahkan 'SEMESTERAN' ke daftar enum yang diizinkan
            $table->enum('frekuensi_kegiatan', ['SUBROUND', 'TAHUNAN', 'TRIWULANAN', 'BULANAN', 'SEMESTERAN', 'ADHOC', 'PERIODIK'])->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('kegiatan_manmits', function (Blueprint $table) {
            // Mengembalikan ke keadaan semula jika di-rollback
            $table->enum('frekuensi_kegiatan', ['SUBROUND', 'TAHUNAN', 'TRIWULANAN', 'BULANAN'])->nullable()->change();
        });
    }
};
