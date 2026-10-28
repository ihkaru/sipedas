<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Pemeliharaan storage rutin: memotong log yang membengkak (>50MB) dan menghapus log lama (>14 hari)
Schedule::call(function () {
    $logPath = storage_path('logs');
    if (is_dir($logPath)) {
        $files = glob($logPath . '/*.log') ?: [];
        $fourteenDaysAgo = time() - (14 * 86400);
        foreach ($files as $file) {
            if (is_file($file)) {
                if (filemtime($file) < $fourteenDaysAgo) {
                    @unlink($file);
                } elseif (filesize($file) > 50 * 1024 * 1024) {
                    @file_put_contents($file, '');
                }
            }
        }
    }
})->daily()->name('storage:prune-logs');
