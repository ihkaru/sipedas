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
        Schema::create('preset_rute_perjadins', function (Blueprint $table) {
            $table->id();
            $table->string('kec_id')->nullable()->index();
            $table->string('nama_kecamatan');
            $table->string('jarak_kategori')->default('Sedang'); // Dalam Kota, Dekat, Sedang, Jauh, Terjauh
            $table->integer('estimasi_menit')->default(30);
            $table->json('steps'); // Array of 5 standardized chronological steps
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preset_rute_perjadins');
    }
};
