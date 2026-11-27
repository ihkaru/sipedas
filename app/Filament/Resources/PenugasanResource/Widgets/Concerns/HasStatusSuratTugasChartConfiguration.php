<?php

namespace App\Filament\Resources\PenugasanResource\Widgets\Concerns;

use App\Models\RiwayatPengajuan;
use App\Supports\Constants;
use Filament\Support\RawJs;
use Illuminate\Database\Eloquent\Builder;

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

    /**
     * Warna semantik per kategori status surat tugas (harmonisasi Filament v5).
     *
     * @var array<string, string>
     */
    public const STATUS_COLORS = [
        Constants::STATUS_PENGAJUAN_DIKIRIM => '#0ea5e9',
        Constants::STATUS_PENGAJUAN_DIBATALKAN => '#94a3b8',
        Constants::STATUS_PENGAJUAN_PERLU_REVISI => '#f59e0b',
        Constants::STATUS_PENGAJUAN_DITOLAK => '#f43f5e',
        Constants::STATUS_PENGAJUAN_DISETUJUI => '#10b981',
        Constants::STATUS_PENGAJUAN_DICETAK => '#6366f1',
        Constants::STATUS_PENGAJUAN_DIKUMPULKAN => '#8b5cf6',
        Constants::STATUS_PENGAJUAN_DICAIRKAN => '#14b8a6',
    ];

    protected ?string $maxHeight = '300px';

    protected function getType(): string
    {
        return 'bar';
    }

    /**
     * Filter periode di header chart agar skala data tetap proporsional.
     *
     * @return array<string, string>
     */
    protected function getFilters(): ?array
    {
        $tahun = now()->year;
        $bulan = now()->locale('id')->translatedFormat('F Y');

        return [
            'tahun_ini' => "Tahun {$tahun}",
            'bulan_ini' => "Bulan {$bulan}",
            'semua' => 'Semua Periode',
        ];
    }

    /**
     * Terapkan filter periode berdasarkan tanggal pengajuan surat tugas.
     *
     * @param  Builder<RiwayatPengajuan>  $query
     * @return Builder<RiwayatPengajuan>
     */
    protected function applyPeriodFilter(Builder $query): Builder
    {
        $activeFilter = $this->filter ?? 'tahun_ini';

        if ($activeFilter === 'bulan_ini') {
            return $query->whereHas('penugasan', function (Builder $penugasanQuery): void {
                $penugasanQuery->whereYear('tgl_pengajuan_tugas', now()->year)
                    ->whereMonth('tgl_pengajuan_tugas', now()->month);
            });
        }

        if ($activeFilter === 'tahun_ini') {
            return $query->whereHas('penugasan', function (Builder $penugasanQuery): void {
                $penugasanQuery->whereYear('tgl_pengajuan_tugas', now()->year);
            });
        }

        return $query;
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
        $keys = array_keys(self::STATUS_ORDER);
        $labels = array_values(self::STATUS_ORDER);
        $data = array_map(
            fn (string $key): int => (int) ($counts[$key] ?? 0),
            $keys
        );
        $backgroundColors = array_map(
            fn (string $key): string => self::STATUS_COLORS[$key] ?? '#14b8a6',
            $keys
        );

        return [
            'datasets' => [
                [
                    'label' => 'Status Surat Tugas',
                    'data' => $data,
                    'backgroundColor' => $backgroundColors,
                    'borderRadius' => 6,
                    'borderSkipped' => 'bottom',
                    'maxBarThickness' => 44,
                ],
            ],
            'labels' => $labels,
        ];
    }

    /**
     * Konfigurasi opsi Chart.js v4 + plugin datalabels sesuai best practice Filament v5:
     * - Tanpa stepSize: 1 agar sumbu Y tidak menumpuk ratusan garis pada data besar.
     * - Headroom grace + layout padding agar datalabels tidak terpotong.
     * - Adaptif light/dark mode dan menyembunyikan angka 0.
     */
    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<'JS'
        {
            layout: {
                padding: {
                    top: 20,
                    right: 8,
                    bottom: 0,
                    left: 4,
                },
            },
            plugins: {
                legend: {
                    display: false,
                },
                datalabels: {
                    anchor: 'end',
                    align: 'top',
                    offset: 4,
                    clamp: true,
                    clip: false,
                    display: (context) => {
                        const val = context.dataset.data[context.dataIndex];
                        return val !== null && val !== undefined && val > 0;
                    },
                    formatter: (value) => (value > 0 ? Number(value).toLocaleString('id-ID') : ''),
                    color: () => (typeof document !== 'undefined' && document.documentElement.classList.contains('dark') ? '#cbd5e1' : '#334155'),
                    font: {
                        weight: '600',
                        size: 11,
                    },
                },
                tooltip: {
                    enabled: true,
                    callbacks: {
                        label: (context) => `Jumlah: ${Number(context.parsed.y ?? 0).toLocaleString('id-ID')} surat tugas`,
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grace: '15%',
                    ticks: {
                        precision: 0,
                        maxTicksLimit: 6,
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
                        maxRotation: 35,
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
