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
        Schema::create('sp_pengajuan', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_pengajuan', 50)->unique();
            $table->date('tanggal_pengajuan');
            $table->string('nomor_form_pembayaran', 50)->nullable();
            $table->string('nomor_detail_fa', 50)->nullable();
            $table->text('uraian_pengajuan')->nullable();
            $table->decimal('nominal_pengajuan', 15, 2);
            $table->string('link_folder_dokumen')->nullable();

            $table->foreignId('posisi_dokumen_id')->nullable()->constrained('sp_posisi_dokumen')->nullOnDelete();
            $table->foreignId('status_pengajuan_ppk_id')->nullable()->constrained('sp_status_pengajuan')->nullOnDelete();
            $table->foreignId('status_pengajuan_ppspm_id')->nullable()->constrained('sp_status_pengajuan')->nullOnDelete();
            $table->foreignId('status_pengajuan_bendahara_id')->nullable()->constrained('sp_status_pengajuan')->nullOnDelete();

            $table->text('catatan_ppk')->nullable();
            $table->text('catatan_ppspm')->nullable();
            $table->text('catatan_bendahara')->nullable();
            $table->text('tanggapan_pengaju_ke_ppk')->nullable();
            $table->text('tanggapan_pengaju_ke_ppspm')->nullable();
            $table->text('tanggapan_pengaju_ke_bendahara')->nullable();


            $table->decimal('nominal_dibayarkan', 15, 2)->nullable();
            $table->decimal('nominal_dikembalikan', 15, 2)->nullable();

            $table->foreignId('status_pembayaran_id')->nullable()->constrained('sp_status_pembayaran')->nullOnDelete();

            $table->date('tanggal_pembayaran')->nullable();

            $table->foreignId('jenis_dokumen_id')->nullable()->constrained('sp_jenis_dokumen')->nullOnDelete();

            $table->string('nomor_dokumen', 50)->nullable();
            $table->timestamps();

            // Indexes
            $table->index('nomor_pengajuan');
            $table->index('tanggal_pengajuan');
            $table->index('status_pengajuan_ppk_id');
            $table->index('status_pengajuan_ppspm_id');
            $table->index('status_pengajuan_bendahara_id');
            $table->index('status_pembayaran_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sp_pengajuan');
    }
};
