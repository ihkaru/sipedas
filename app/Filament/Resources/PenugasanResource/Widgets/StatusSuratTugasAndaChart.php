<?php

namespace App\Filament\Resources\PenugasanResource\Widgets;

use App\Models\RiwayatPengajuan;
use App\Supports\Constants;
use Filament\Widgets\ChartWidget;

class StatusSuratTugasAndaChart extends ChartWidget
{
    protected static ?string $heading = 'Jumlah Status Surat Tugas Anda';
    protected static string $color = 'primary';


    protected function getData(): array
    {
        $riwayatPengajuan = RiwayatPengajuan::whereHas('penugasan',function($query){
            $query->whereHas('pegawai',function($query){
                $query->where('id',auth()->user()->pegawai?->id ?? "");
            });
        })
            ->selectRaw('status,count(status) as jumlah')->groupBy('status')->get();
        $labels = [
            "Dikirim",
            "Dibatalkan",
            "Perlu Revisi",
            "Ditolak",
            "Disetujui",
            "Dicetak",
            "Dikumpulkan",
            "Dicairkan",
        ];
        $data = [
            $riwayatPengajuan->where('status','STATUS_PENGAJUAN_DIKIRIM')->first()?->jumlah ?? 0,
            $riwayatPengajuan->where('status','STATUS_PENGAJUAN_DIBATALKAN')->first()?->jumlah ?? 0,
            $riwayatPengajuan->where('status','STATUS_PENGAJUAN_PERLU_REVISI')->first()?->jumlah ?? 0,
            $riwayatPengajuan->where('status','STATUS_PENGAJUAN_DITOLAK')->first()?->jumlah ?? 0,
            $riwayatPengajuan->where('status','STATUS_PENGAJUAN_DISETUJUI')->first()?->jumlah ?? 0,
            $riwayatPengajuan->where('status','STATUS_PENGAJUAN_DICETAK')->first()?->jumlah ?? 0,
            $riwayatPengajuan->where('status','STATUS_PENGAJUAN_DIKUMPULKAN')->first()?->jumlah ?? 0,
            $riwayatPengajuan->where('status','STATUS_PENGAJUAN_DICAIRKAN')->first()?->jumlah ?? 0,
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Status Surat Tugas',
                    'data' => $data,


                ],

            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'datalabels' => [
                    'color'=> '#36A2EB'
                ]
            ],
            'scales'=>[
                'y'=> [
                    'ticks'=>[
                        'precision'=>0
                    ]
                ]
            ]
        ];
    }
}
