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
        Schema::create('kegiatans', function (Blueprint $table) {
            $table->id();
            $table->string("nama");
            $table->string("tgl_awal_perjadin");
            $table->string("tgl_akhir_perjadin");
            $table->string("pj_kegiatan_id")->nullable();
            $table->foreign("pj_kegiatan_id")->references("id")->on("pegawais");
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
