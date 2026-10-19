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
        Schema::table('sp_pengajuan', function (Blueprint $table) {
            $table->index('nip_pengaju', 'idx_sp_pengajuan_nip_pengaju');
            $table->index('nip_penanggung_jawab', 'idx_sp_pengajuan_nip_penanggung_jawab');
            $table->index(['posisi_dokumen_id', 'nip_pengaju'], 'idx_sp_pengajuan_posisi_nip_pengaju');
            $table->index(['created_at', 'posisi_dokumen_id'], 'idx_sp_pengajuan_created_posisi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sp_pengajuan', function (Blueprint $table) {
            $table->dropIndex('idx_sp_pengajuan_nip_pengaju');
            $table->dropIndex('idx_sp_pengajuan_nip_penanggung_jawab');
            $table->dropIndex('idx_sp_pengajuan_posisi_nip_pengaju');
            $table->dropIndex('idx_sp_pengajuan_created_posisi');
        });
    }
};
