<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penugasans', function (Blueprint $table) {
            // Drop FK jika masih ada (mungkin sudah tidak ada)
            $fks = DB::select("
                SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'penugasans'
                  AND CONSTRAINT_TYPE = 'FOREIGN KEY'
                  AND CONSTRAINT_NAME = 'penugasans_kegiatan_id_foreign'
            ");
            if (!empty($fks)) {
                $table->dropForeign(['kegiatan_id']);
            }

            // Add FK dengan ON UPDATE CASCADE
            $table->foreign('kegiatan_id')
                ->references('id')
                ->on('kegiatans')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('penugasans', function (Blueprint $table) {
            $table->dropForeign(['kegiatan_id']);
            $table->foreign('kegiatan_id')
                ->references('id')
                ->on('kegiatans');
        });
    }
};
