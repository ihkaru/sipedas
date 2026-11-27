<?php

namespace App\Filament\Resources\PenugasanResource\Widgets;

use App\Filament\Resources\PenugasanResource\Widgets\Concerns\HasStatusSuratTugasChartConfiguration;
use App\Models\RiwayatPengajuan;
use Filament\Widgets\ChartWidget;

class StatusSuratTugasAndaChart extends ChartWidget
{
    use HasStatusSuratTugasChartConfiguration;

    protected ?string $heading = 'Jumlah Status Surat Tugas Anda';

    protected string $color = 'primary';

    public ?string $filter = 'tahun_ini';

    protected function getData(): array
    {
        $nip = auth()->user()?->pegawai?->nip ?? '';

        $query = RiwayatPengajuan::query()
            ->whereHas('penugasan.pegawai', function ($query) use ($nip): void {
                $query->where('nip', $nip);
            });

        $this->applyPeriodFilter($query);

        $statusCounts = $query
            ->selectRaw('status, count(status) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        return $this->formatChartData($statusCounts);
    }
}
