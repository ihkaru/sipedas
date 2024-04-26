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
            $table->foreignIdFor(Kegiatan::class);
            $table->string("jenis_perjadin");
            $table->timestamp("tgl_mulai_tugas")->nullable();
            $table->timestamp("tgl_akhir_tugas")->nullable();
            $table->unsignedSmallInteger("tbh_hari_jalan_awal");
            $table->unsignedSmallInteger("tbh_hari_jalan_akhir");
            $table->string("prov_id");
            $table->string("kabkot_id");
            $table->string("kecamatan_id");
            $table->string("desa_kel_id");
            $table->string("jenis_surat_tugas");
            $table->string("surat_tugas_id");
            $table->foreignIdFor(Pegawai::class);
            $table->string("transportasi");
            $table->string("status");
            $table->timestamp("tgl_surat_pengajuan")->nullable();
            $table->timestamp("tgl_surat_diterima")->nullable();
            $table->timestamp("tgl_surat_cetak")->nullable();
            $table->timestamp("tgl_surat_kembali")->nullable();
            $table->timestamp("tgl_pencairan")->nullable();
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
