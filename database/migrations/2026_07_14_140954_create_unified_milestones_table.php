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
        Schema::create('unified_milestones', function (Blueprint $table) {
            $table->id();
            $table->string('activity_id')->nullable()->index();
            $table->string('kategori')->nullable();
            $table->date('tanggal')->nullable()->index();
            $table->text('kegiatan')->nullable();
            $table->string('status')->nullable();
            $table->string('pic')->nullable();
            $table->json('attributes_json')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unified_milestones');
    }
};
