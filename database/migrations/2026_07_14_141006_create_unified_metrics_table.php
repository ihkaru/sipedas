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
        Schema::create('unified_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('activity_id')->nullable()->index();
            $table->string('metric_id')->nullable()->index();
            $table->string('label')->nullable();
            $table->string('target')->nullable();
            $table->string('completed')->nullable();
            $table->string('worked')->nullable();
            $table->string('percentage')->nullable();
            $table->json('context_json')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unified_metrics');
    }
};
