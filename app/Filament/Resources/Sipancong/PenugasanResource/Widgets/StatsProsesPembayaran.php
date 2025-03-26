<?php

namespace App\Filament\Resources\Sipancong\PenugasanResource\Widgets;

use App\Services\Sipancong\PengajuanServices;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsProsesPembayaran extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make("Perlu Pemeriksaan PPK", "" . PengajuanServices::jumlahPerluPemeriksaanPpk()),
            Stat::make("Perlu Pemeriksaan Bendahara", "" . PengajuanServices::jumlahPerluPemeriksaanBendahara()),
            Stat::make("Perlu Pemeriksaan PPSPM", "" . PengajuanServices::jumlahPerluPemeriksaanPpspm()),
            Stat::make("Perlu Proses Pembayaran Bendahara", "" . PengajuanServices::jumlahPerluProsesBendahara()),
        ];
    }
    protected function getColumns(): int
    {
        return 4;
    }
}
