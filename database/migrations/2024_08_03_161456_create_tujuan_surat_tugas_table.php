<?php

use App\Models\Penugasan;
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
        Schema::create('tujuan_surat_tugas', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Penugasan::class);
            $table->string("level_tujuan_penugasan");
            $table->string('prov_id')->nullable()->default(null);
            $table->string('kabkot_id')->nullable()->default(null);
            $table->string('kecamatan_id')->nullable()->default(null);
            $table->string('desa_kel_id')->nullable()->default(null);
            $table->string("nama_tempat_tujuan")->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tujuan_surat_tugas');
    }
};
