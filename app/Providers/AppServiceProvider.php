<?php

namespace App\Providers;

use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Backward compatibility fallback for older Filament vendor environments
        if (!class_exists('Filament\Schemas\Components\Tabs\Tab')) {
            if (class_exists('Filament\Resources\Components\Tab')) {
                class_alias('Filament\Resources\Components\Tab', 'Filament\Schemas\Components\Tabs\Tab');
            } elseif (class_exists('Filament\Resources\Pages\ListRecords\Tab')) {
                class_alias('Filament\Resources\Pages\ListRecords\Tab', 'Filament\Schemas\Components\Tabs\Tab');
            }
        }
        if (!enum_exists('Filament\Tables\Enums\RecordActionsPosition') && enum_exists('Filament\Tables\Enums\ActionsPosition')) {
            class_alias('Filament\Tables\Enums\ActionsPosition', 'Filament\Tables\Enums\RecordActionsPosition');
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Octane Watch Mode Test
        Carbon::setLocale('id');
        FilamentAsset::register([
            Js::make('chart-js-plugins', Vite::asset('resources/js/filament-chart-js-plugins.js'))->module(),
        ]);

        // Observer: propagasi perubahan tanggal_akhir_kegiatan ke alokasi & nomor surat
        \App\Models\Honor::observe(\App\Observers\HonorObserver::class);
    }
}
