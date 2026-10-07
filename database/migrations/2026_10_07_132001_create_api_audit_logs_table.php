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
        if (Schema::hasTable('api_audit_logs')) {
            return;
        }

        Schema::create('api_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('api_key_id')->nullable();
            $table->string('action');
            $table->string('method', 10);
            $table->string('endpoint');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('payload')->nullable();
            $table->integer('response_status')->default(200);
            $table->string('target_model')->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->json('state_before')->nullable();
            $table->json('state_after')->nullable();
            $table->boolean('is_reversible')->default(true);
            $table->boolean('is_rolled_back')->default(false);
            $table->timestamp('rolled_back_at')->nullable();
            $table->unsignedBigInteger('rolled_back_by_user_id')->nullable();
            $table->text('rollback_reason')->nullable();
            $table->timestamps();

            $table->foreign('api_key_id')->references('id')->on('api_keys')->nullOnDelete();
            $table->foreign('rolled_back_by_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_audit_logs');
    }
};
