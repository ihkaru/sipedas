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
        Schema::create('alokasi_honors', function (Blueprint $table) {
            $table->id();

            // --- KUNCI ASING KE MITRA (DIBUAT EKSPLISIT) ---
            // 1. Definisikan kolom dengan tipe data yang sama persis dengan 'mitras.id' yaitu UNSIGNED BIGINT.
            $table->unsignedBigInteger('mitra_id');

            // --- KUNCI ASING KE HONOR (TETAP STRING) ---
            // 'honors.id' adalah string, jadi kolom ini juga harus string.
            $table->string('honor_id');

            // Data spesifik untuk alokasi ini
            $table->decimal('target_per_satuan_honor', 15, 2);
            $table->decimal('total_honor', 15, 2)->comment('Hasil kalkulasi dari (honor.harga_per_satuan * target_per_satuan_honor)');

            // Foreign Key untuk Dokumen (dibuat nullable dan eksplisit juga)
            $table->unsignedBigInteger("surat_perjanjian_kerja_id")->nullable();
            $table->unsignedBigInteger("surat_bast_id")->nullable();

            // Informasi tambahan
            $table->date('tanggal_mulai_perjanjian')->nullable();
            $table->date('tanggal_akhir_perjanjian')->nullable();

            $table->timestamps();

            // --- DEFINISI FOREIGN KEY DITEMPATKAN DI AKHIR ---
            // 2. Buat relasi foreign key setelah semua kolom didefinisikan.
            $table->foreign('mitra_id')->references('id')->on('mitras')->onDelete('cascade');
            $table->foreign('honor_id')->references('id')->on('honors')->onDelete('cascade');

            // Foreign key untuk surat (asumsi 'nomor_surats.id' adalah unsignedBigInteger)
            $table->foreign("surat_perjanjian_kerja_id")->references("id")->on("nomor_surats")->onDelete('set null');
            $table->foreign("surat_bast_id")->references("id")->on("nomor_surats")->onDelete('set null');

            $table->date('tanggal_penanda_tanganan_spk_oleh_petugas')->nullable(); // <-- TAMBAHKAN INI
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alokasi_honors');
    }
};
