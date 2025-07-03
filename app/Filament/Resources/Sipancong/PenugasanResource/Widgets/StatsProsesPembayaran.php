<?php

namespace App\Filament\Resources\Sipancong\PenugasanResource\Widgets;

use App\Services\Sipancong\PengajuanServices;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsProsesPembayaran extends BaseWidget
{
    protected function getStats(): array
    {
        $umum = PengajuanServices::jumlahSelesaiSubfungsi("Umum");
        $produksi = PengajuanServices::jumlahSelesaiSubfungsi("Produksi");
        $distribusi = PengajuanServices::jumlahSelesaiSubfungsi("Distribusi");
        $sosial = PengajuanServices::jumlahSelesaiSubfungsi("Sosial");
        $neraca = PengajuanServices::jumlahSelesaiSubfungsi("Neraca");
        $ipds = PengajuanServices::jumlahSelesaiSubfungsi("IPDS");
        return [
            Stat::make("Pengajuan Umum Selesai", "" . $umum == 0 ? "-" : ($umum . "%")),
            Stat::make("Pengajuan Produksi Selesai", "" . $produksi == 0 ? "-" : ($produksi . "%")),
            Stat::make("Pengajuan Distribusi Selesai", "" . $distribusi == 0 ? "-" : ($distribusi . "%")),
            Stat::make("Pengajuan Sosial Selesai", "" . $sosial == 0 ? "-" : ($sosial . "%")),
            Stat::make("Pengajuan Neraca Selesai", "" . $neraca == 0 ? "-" : ($neraca . "%")),
            Stat::make("Pengajuan IPDS Selesai", "" . $ipds == 0 ? "-" : ($ipds . "%"))
        ];
        // return [];
    }
    protected function getColumns(): int
    {
        return 4;
    }
}
