<?php

namespace App\Filament\Resources\PenugasanResource\Widgets\Concerns;

use App\Supports\Constants;
use Filament\Support\RawJs;

trait HasStatusSuratTugasChartConfiguration
{
    /**
     * Mapping urutan status surat tugas beserta label tampilannya pada chart.
     *
     * @var array<string, string>
     */
    public const STATUS_ORDER = [
        Constants::STATUS_PENGAJUAN_DIKIRIM => 'Dikirim',
        Constants::STATUS_PENGAJUAN_DIBATALKAN => 'Dibatalkan',
        Constants::STATUS_PENGAJUAN_PERLU_REVISI => 'Perlu Revisi',
        Constants::STATUS_PENGAJUAN_DITOLAK => 'Ditolak',
        Constants::STATUS_PENGAJUAN_DISETUJUI => 'Disetujui',
        Constants::STATUS_PENGAJUAN_DICETAK => 'Dicetak',
        Constants::STATUS_PENGAJUAN_DIKUMPULKAN => 'Dikumpulkan',
        Constants::STATUS_PENGAJUAN_DICAIRKAN => 'Dicairkan',
    ];

    protected function getType(): string
    {
        return 'bar';
    }

    /**
     * Format agregasi status ke dalam struktur data Chart.js.
     *
     * @param  iterable<string, int|numeric>|array<string, int|numeric>  $statusCounts
     * @return array<string, mixed>
     */
    protected function formatChartData(iterable $statusCounts): array
    {
        $counts = is_array($statusCounts) ? $statusCounts : iterator_to_array($statusCounts);
        $labels = array_values(self::STATUS_ORDER);
        $data = array_map(
            fn (string $key): int => (int) ($counts[$key] ?? 0),
            array_keys(self::STATUS_ORDER)
        );

        return [
            'datasets' => [
                [
                    'label' => 'Status Surat Tugas',
                    'data' => $data,
                    'borderRadius' => 6,
                    'maxBarThickness' => 48,
                ],
            ],
            'labels' => $labels,
        ];
    }

    /**
     * Konfigurasi opsi Chart.js dan plugin datalabels agar rapi, tidak bertumpuk,
     * dan menyembunyikan angka 0 pada kategori kosong.
     */
    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<JS
        {
            plugins: {
                legend: {
                    display: false,
                },
                datalabels: {
                    anchor: 'end',
                    align: 'top',
                    offset: 4,
                    display: (context) => {
                        const val = context.dataset.data[context.dataIndex];
                        return val !== null && val !== undefined && val > 0;
                    },
                    formatter: (value) => (value > 0 ? value : ''),
                    color: () => (typeof document !== 'undefined' && document.documentElement.classList.contains('dark') ? '#cbd5e1' : '#334155'),
                    font: {
                        weight: '600',
                        size: 11,
                    },
                },
                tooltip: {
                    enabled: true,
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grace: '12%',
                    ticks: {
                        precision: 0,
                        stepSize: 1,
                    },
                    grid: {
                        display: true,
                    },
                },
                x: {
                    grid: {
                        display: false,
                    },
                    ticks: {
                        autoSkip: false,
                        maxRotation: 45,
                        minRotation: 0,
                    },
                },
            },
            elements: {
                bar: {
                    borderRadius: 6,
                },
            },
        }
        JS);
    }
}
