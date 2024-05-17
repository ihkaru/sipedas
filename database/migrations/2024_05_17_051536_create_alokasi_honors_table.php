<?php

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
        $columns = Constants::COLUMN_ALOKASI_HONOR_IMPORT;
        Schema::create('alokasi_honors', function (Blueprint $table) use($columns) {
            $table->id();
            foreach ($columns as $c) {
                $table->string($c)->nullable();
            }
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
