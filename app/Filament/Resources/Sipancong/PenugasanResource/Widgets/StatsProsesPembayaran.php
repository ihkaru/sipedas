<?php

namespace App\Filament\Resources\Sipancong\PenugasanResource\Widgets;

use App\Supports\SipancongConstants as Constants;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StatsProsesPembayaran extends BaseWidget
{
    protected function getStats(): array
    {
        $rawStats = DB::table('sp_pengajuan')
            ->join('sp_sub_fungsi', 'sp_pengajuan.sub_fungsi_id', '=', 'sp_sub_fungsi.id')
            ->selectRaw('sp_sub_fungsi.nama, count(*) as total, sum(case when sp_pengajuan.posisi_dokumen_id = ' . Constants::POSISI_SELESAI . ' then 1 else 0 end) as selesai')
            ->groupBy('sp_sub_fungsi.nama')
            ->get()
            ->keyBy('nama');

        $calc = function (string $subfungsi) use ($rawStats): string {
            $row = $rawStats->get($subfungsi);
            if (!$row || (int)$row->total === 0) {
                return '-';
            }
            $pct = round(((int)$row->selesai / (int)$row->total) * 100);
            return $pct == 0 ? '-' : ($pct . '%');
        };

        return [
            Stat::make("Pengajuan Umum Selesai", $calc("Umum")),
            Stat::make("Pengajuan Produksi Selesai", $calc("Produksi")),
            Stat::make("Pengajuan Distribusi Selesai", $calc("Distribusi")),
            Stat::make("Pengajuan Sosial Selesai", $calc("Sosial")),
            Stat::make("Pengajuan Neraca Selesai", $calc("Neraca")),
            Stat::make("Pengajuan IPDS Selesai", $calc("IPDS")),
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }
}
