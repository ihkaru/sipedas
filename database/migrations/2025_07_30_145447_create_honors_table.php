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
        Schema::create('honors', function (Blueprint $table) {
            $table->string('id')->primary();

            $table->string('kegiatan_manmit_id');
            $table->foreign('kegiatan_manmit_id')
                ->references('id')
                ->on('kegiatan_manmits')
                ->onDelete('cascade');

            $table->string('jabatan');
            $table->string('jenis_honor');
            $table->string('satuan_honor');

            // Menggunakan DECIMAL untuk presisi harga
            $table->decimal('harga_per_satuan', 15, 2)->unsigned();

            $table->date('tanggal_akhir_kegiatan');

            // Tanggal pembayaran maksimal yang akan dihitung otomatis
            $table->date('tanggal_pembayaran_maksimal')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('honors');
    }
};
