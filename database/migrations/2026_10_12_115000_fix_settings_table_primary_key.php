<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('settings')) {
            // Fix 0 id if any
            DB::statement("UPDATE settings SET id = (SELECT COALESCE(MAX(id), 0) + 1 FROM (SELECT id FROM settings) AS t) WHERE id = 0");

            // Add Primary Key if not exists
            try {
                DB::statement("ALTER TABLE settings ADD PRIMARY KEY (id)");
            } catch (\Throwable $e) {
                // Ignore if already primary key
            }

            // Modify id to AUTO_INCREMENT
            try {
                DB::statement("ALTER TABLE settings MODIFY id bigint unsigned AUTO_INCREMENT");
            } catch (\Throwable $e) {
                // Ignore if already auto increment
            }

            // Add UNIQUE index on key
            try {
                DB::statement("ALTER TABLE settings ADD UNIQUE (`key`)");
            } catch (\Throwable $e) {
                // Ignore if already unique
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No destructive reverse needed
    }
};
