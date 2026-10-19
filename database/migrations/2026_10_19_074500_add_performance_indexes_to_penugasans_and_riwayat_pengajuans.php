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
        Schema::table('riwayat_pengajuans', function (Blueprint $table) {
            $table->index(['penugasan_id', 'status'], 'idx_rp_penugasan_status');
            $table->index('status', 'idx_rp_status');
            $table->index('last_status_timestamp', 'idx_rp_last_status_ts');
        });

        Schema::table('penugasans', function (Blueprint $table) {
            $table->index('tgl_mulai_tugas', 'idx_penugasans_tgl_mulai');
            $table->index(['tgl_mulai_tugas', 'created_at'], 'idx_penugasans_tgl_created');
            $table->index('nip', 'idx_penugasans_nip');
            $table->index('id_sobat', 'idx_penugasans_id_sobat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('riwayat_pengajuans', function (Blueprint $table) {
            $table->dropIndex('idx_rp_penugasan_status');
            $table->dropIndex('idx_rp_status');
            $table->dropIndex('idx_rp_last_status_ts');
        });

        Schema::table('penugasans', function (Blueprint $table) {
            $table->dropIndex('idx_penugasans_tgl_mulai');
            $table->dropIndex('idx_penugasans_tgl_created');
            $table->dropIndex('idx_penugasans_nip');
            $table->dropIndex('idx_penugasans_id_sobat');
        });
    }
};
