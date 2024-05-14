<?php

use App\Models\Kegiatan;
use App\Models\Pegawai;
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
        Schema::create('penugasans', function (Blueprint $table) {
            $table->id();
            $table->string("nip");
            $table->string("kegiatan_id");
            $table->foreign("kegiatan_id")->references("id")->on("kegiatans");
            $table->timestamp("tgl_pengajuan_tugas")->nullable();
            $table->timestamp("tgl_mulai_tugas")->nullable();
            $table->timestamp("tgl_akhir_tugas")->nullable();
            $table->unsignedSmallInteger("tbh_hari_jalan_awal");
            $table->unsignedSmallInteger("tbh_hari_jalan_akhir");
            $table->string("prov_id");
            // $table->foreign("prov_id")->references("prov_id")->on("master_sls");
            $table->string("kabkot_id")->nullable();
            // $table->foreign("kabkot_id")->references("kabkot_id")->on("master_sls");
            $table->string("kecamatan_id")->nullable();
            // $table->foreign("kecamatan_id")->references("kecamatan_id")->on("master_sls");
            $table->string("desa_kel_id")->nullable();
            // $table->foreign("desa_kel_id")->references("desa_kel_id")->on("master_sls");
            $table->string("jenis_surat_tugas");
            $table->string("surat_tugas_id");
            $table->foreignIdFor(Pegawai::class,"plh_id");
            $table->string("transportasi");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penugasans');
    }
};
