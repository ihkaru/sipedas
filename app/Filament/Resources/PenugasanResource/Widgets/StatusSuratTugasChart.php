<?php

namespace App\Filament\Resources\PenugasanResource\Widgets;

use App\Filament\Resources\PenugasanResource\Widgets\Concerns\HasStatusSuratTugasChartConfiguration;
use App\Models\RiwayatPengajuan;
use Filament\Widgets\ChartWidget;

class StatusSuratTugasChart extends ChartWidget
{
    use HasStatusSuratTugasChartConfiguration;

    protected ?string $heading = 'Jumlah Status Surat Tugas';
    protected string $color = 'primary';

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('operator_umum') ?? false;
    }

    protected function getData(): array
    {
        $statusCounts = RiwayatPengajuan::query()
            ->selectRaw('status, count(status) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        return $this->formatChartData($statusCounts);
    }
}
