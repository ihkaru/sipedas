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
        Schema::table('preset_rute_perjadins', function (Blueprint $table) {
            $table->string('kantor_camat_nama')->nullable()->after('nama_kecamatan');
            $table->string('kantor_camat_alamat')->nullable()->after('kantor_camat_nama');
            $table->string('kantor_camat_koordinat')->nullable()->after('kantor_camat_alamat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('preset_rute_perjadins', function (Blueprint $table) {
            $table->dropColumn(['kantor_camat_nama', 'kantor_camat_alamat', 'kantor_camat_koordinat']);
        });
    }
};
