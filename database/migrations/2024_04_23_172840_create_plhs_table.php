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
        Schema::create('plhs', function (Blueprint $table) {
            $table->id();
            $table->string("pegawai_peganti_id")->nullable();
            $table->foreign("pegawai_peganti_id")->references("nip")->on("pegawais");
            $table->string("pegawai_diganti_id")->nullable();
            $table->foreign("pegawai_diganti_id")->references("nip")->on("pegawais");
            $table->timestamp("tgl_mulai")->nullable();
            $table->timestamp("tgl_selesai")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plhs');
    }
};
