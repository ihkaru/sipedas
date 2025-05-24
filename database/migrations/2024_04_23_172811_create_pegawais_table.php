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
        Schema::create('pegawais', function (Blueprint $table) {
            $table->string("nama");
            $table->string("nip")->unique()->primary();
            $table->string("nip9")->unique();
            $table->string("golongan");
            $table->string("pangkat");
            $table->string("jabatan");
            $table->string("email")->unique();
            $table->string("atasan_langsung_id")->nullable();
            $table->foreign("atasan_langsung_id")->references("nip")->on("pegawais");
            $table->string("unit_kerja");
            $table->string("nomor_wa")->nullable();
            $table->string("panggilan");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pegawais');
    }
};
