<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Composer;
use Illuminate\Support\Facades\Artisan;

class ArtisanController extends Controller {
    public function run() {
        if (!request("secret")) {
            abort(401, "Unauthorized Access");
        }
        if (request("secret") != "123password123") {
            abort(401, "Unauthorized Access");
        }
        $exitCode = collect([]);
        $exitCode->push($this->migration());

        return $this->responsejson(200, ["exitCode" => $exitCode]);
    }

    public function migration() {
        $migration = collect([]);
        $output = new \Symfony\Component\Console\Output\BufferedOutput;

        if (request("freshseed")) {
            Artisan::call("migrate:fresh --seed", [], $output);
            return $output->fetch();
        }
        if (request("fresh")) {
            Artisan::call("migrate:fresh", [], $output);
            return $output->fetch();
        }
        if (request("migrate")) {
            Artisan::call("migrate", [], $output);
            return $output->fetch();
        }
        if (request("migrate-fresh")) {
            Artisan::call("migrate:fresh", [], $output);
            return $output->fetch();
        }
        if (request("seed")) {
            Artisan::call("db:seed", [], $output);
            return $output->fetch();
        }
        if (request("cache-clear")) {
            Artisan::call("cache:clear", [], $output);
            return $output->fetch();
        }
        if (request("config-clear")) {
            Artisan::call("config:clear", [], $output);
            return $output->fetch();
        }

        if (request("view-clear")) {
            Artisan::call("view:clear", [], $output);
            return $output->fetch();
        }
        if (request("filament-upgrade")) {
            Artisan::call("filament:upgrade", [], $output);
            return $output->fetch();
        }
        if (request("route-cache")) {
            Artisan::call("route:cache", [], $output);
            return $output->fetch();
        }
        if (request("storage-link")) {
            $msg = [];
            try {
                Artisan::call("storage:link", [], $output);
                $msg[] = $output->fetch();
            } catch (\Throwable $e) {
                $msg[] = "Artisan storage:link notice: " . $e->getMessage();
            }

            // Target storage directory
            $target = storage_path('app/public');

            // Potential cPanel document root paths for subdomain
            $possiblePublicRoots = array_filter([
                $_SERVER['DOCUMENT_ROOT'] ?? null,
                base_path('../public_html/admin.dvlp.asia'),
                base_path('../public_html'),
            ]);

            foreach (array_unique($possiblePublicRoots) as $docRoot) {
                if (is_dir($docRoot)) {
                    $linkPath = rtrim($docRoot, '/') . '/storage';
                    try {
                        if (is_link($linkPath)) {
                            @unlink($linkPath);
                        } elseif (is_dir($linkPath)) {
                            @rename($linkPath, $linkPath . '_backup_' . date('Ymd_His'));
                        } elseif (file_exists($linkPath)) {
                            @unlink($linkPath);
                        }

                        if (@symlink($target, $linkPath)) {
                            $msg[] = "Successfully linked: {$linkPath} -> {$target}";
                        } else {
                            $msg[] = "Notice: Symlink function not permitted or failed for {$linkPath}";
                        }
                    } catch (\Throwable $ex) {
                        $msg[] = "Symlink notice for {$linkPath}: " . $ex->getMessage();
                    }
                }
            }

            return implode("\n", $msg);
        }
        if (request("clean-livewire-assets")) {
            $deleted = [];
            $targets = [
                public_path('vendor/livewire'),
                ($_SERVER['DOCUMENT_ROOT'] ?? '') . '/vendor/livewire',
                base_path('../public_html/admin.dvlp.asia/vendor/livewire'),
            ];
            foreach (array_unique(array_filter($targets)) as $dir) {
                if (is_dir($dir)) {
                    \Illuminate\Support\Facades\File::deleteDirectory($dir);
                    $deleted[] = "Deleted: {$dir}";
                }
            }
            return empty($deleted) ? "No published livewire assets found." : implode("\n", $deleted);
        }
        if (request("dump-autoload")) {
            app()->make(Composer::class)->dumpAutoloads();
            // Artisan::call("dump-autoload",[],$output);
            return $output->fetch();
        }
        if (request("install")) {
            // app()->make(Composer::class)->dumpAutoloads();
            // Artisan::call("dump-autoload",[],$output);
            $extra = [];
            $composer = app()->make(Composer::class)->findComposer();
            $command = array_merge($composer, ['install'], $extra);
            app()->make(Composer::class)->run($command);
            return $output->fetch();
        }
        if (request("icons-cache")) {
            Artisan::call("icons:cache", [], $output);
            return $output->fetch();
        }
        if (request("icons-clear")) {
            Artisan::call("icons:clear", [], $output);
            return $output->fetch();
        }
        if (request("config-clear")) {
            Artisan::call("config:clear", [], $output);
            return $output->fetch();
        }
        if (request("filament-optimize")) {
            Artisan::call("filament:optimize", [], $output);
            return $output->fetch();
        }
        if (request("filament-cache-components")) {
            Artisan::call("filament:cache-components", [], $output);
            return $output->fetch();
        }
    }
    public function clearAllCache() {
        if (!request("secret")) {
            abort(401, "Unauthorized Access");
        }
        if (request("secret") != "123password123") {
            abort(401, "Unauthorized Access");
        }
        
        Artisan::call('optimize:clear');
        Artisan::call('filament:clear-cached-components');

        // Cleanup obsolete published livewire assets if present
        $targets = [
            public_path('vendor/livewire'),
            ($_SERVER['DOCUMENT_ROOT'] ?? '') . '/vendor/livewire',
            base_path('../public_html/admin.dvlp.asia/vendor/livewire'),
        ];
        foreach (array_unique(array_filter($targets)) as $dir) {
            if (is_dir($dir)) {
                \Illuminate\Support\Facades\File::deleteDirectory($dir);
            }
        }
        
        return "All caches cleared and obsolete livewire assets cleaned!";
    }
}
