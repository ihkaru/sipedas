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
        Schema::create('riwayat_pengajuans', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Penugasan::class);
            $table->string("status");
            $table->text("catatan_ditolak")->nullable();
            $table->text("catatan_butuh_perbaikan")->nullable();
            $table->timestamp("last_status_timestamp")->nullable();
            $table->timestamp("tgl_dibatalkan")->nullable();
            $table->timestamp("tgl_arahan_revisi")->nullable();
            $table->timestamp("tgl_dikirim")->nullable();
            $table->timestamp("tgl_diterima")->nullable();
            $table->timestamp("tgl_dibuat")->nullable();
            $table->timestamp("tgl_dikumpulkan")->nullable();
            $table->timestamp("tgl_ditolak")->nullable();
            $table->timestamp("tgl_pencairan")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_pengajuans');
    }
};
