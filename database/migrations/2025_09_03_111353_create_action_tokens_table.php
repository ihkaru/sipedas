<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('action_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')->constrained('sp_pengajuan')->onDelete('cascade');
            $table->string('token', 64)->unique();
            $table->string('action'); // e.g., 'ppk_approve', 'ppspm_approve'
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('action_tokens');
    }
};
