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
        Schema::table("kegiatan_manmits", function (Blueprint $table) {
            $table->longText("template_kontrak")->nullable()->comment("Template khusus pasal-pasal kontrak untuk kegiatan ini");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("kegiatan_manmits", function (Blueprint $table) {
            $table->dropColumn("template_kontrak");
        });
    }
};
