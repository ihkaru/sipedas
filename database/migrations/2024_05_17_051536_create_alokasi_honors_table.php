<?php

use App\Models\NomorSurat;
use App\Supports\Constants;
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
        $columns = collect(Constants::COLUMN_ALOKASI_HONOR_IMPORT);
        $timestampsCol = collect(Constants::COLUMN_TIMESTAMP_ALOKASI_HONOR_IMPORT);
        Schema::create('alokasi_honors', function (Blueprint $table) use($columns,$timestampsCol) {
            $table->id();
            foreach ($columns as $c) {
                if($timestampsCol->contains($c)){
                    $table->timestamp($c)->nullable();
                    continue;
                }
                $table->string($c)->nullable();
            }
            $table->unsignedBigInteger("surat_perjanjian_kerja_id");
            $table->foreign("surat_perjanjian_kerja_id")->references("id")->on("nomor_surats");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alokasi_honors');
    }
};
