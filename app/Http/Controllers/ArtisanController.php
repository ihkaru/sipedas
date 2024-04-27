<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Composer;
use Illuminate\Support\Facades\Artisan;

class ArtisanController extends Controller
{
    public function run()
    {
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

    public function migration()
    {
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
            Artisan::call("storage:link", [], $output);
            return $output->fetch();
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
    }
}
