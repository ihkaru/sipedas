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
        if (! Schema::hasTable('kemitraans')) {
            return;
        }

        Schema::table('kemitraans', function (Blueprint $table) {
            if (! Schema::hasColumn('kemitraans', 'status_rekrutmen')) {
                $table->string('status_rekrutmen', 32)
                    ->default('TERVERIFIKASI')
                    ->after('status')
                    ->index();
            }

            if (! Schema::hasColumn('kemitraans', 'catatan')) {
                $table->text('catatan')
                    ->nullable()
                    ->after('status_rekrutmen');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('kemitraans')) {
            return;
        }

        Schema::table('kemitraans', function (Blueprint $table) {
            if (Schema::hasColumn('kemitraans', 'catatan')) {
                $table->dropColumn('catatan');
            }

            if (Schema::hasColumn('kemitraans', 'status_rekrutmen')) {
                $table->dropIndex(['status_rekrutmen']);
                $table->dropColumn('status_rekrutmen');
            }
        });
    }
};
