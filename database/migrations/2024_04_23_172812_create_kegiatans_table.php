<?php

use App\Models\KegiatanManmit;
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
        Schema::create('kegiatans', function (Blueprint $table) {
            $table->string("id")->primary();
            $table->string("nama");
            $table->string("nomor_surat_referensi")->nullable();
            $table->timestamp("tgl_awal_perjadin")->nullable();
            $table->timestamp("tgl_akhir_perjadin")->nullable();
            $table->string("pj_kegiatan_id")->nullable();
            $table->string("kegiatan_manmit_id")->nullable();
            $table->foreign("kegiatan_manmit_id")->references("id")->on('kegiatan_manmits');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kegiatans');
    }
};
