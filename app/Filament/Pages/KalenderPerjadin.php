<?php

namespace App\Filament\Pages;

use App\Models\Pegawai;
use App\Models\Penugasan;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;

class KalenderPerjadin extends Page {
    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static string $view = 'filament.pages.kalender-perjadin';

    public $year;
    public $month;
    public $calendarData;
    public $monthName;
    public $selectedPegawai = null;
    public $pegawaiOptions = [];

    public function mount() {
        $this->year = now()->year;
        $this->month = now()->month;
        $this->pegawaiOptions = Pegawai::pluck('nama', 'nip')->toArray();
        $this->fetchCalendarData();
    }

    public function updatedSelectedPegawai() {
        $this->fetchCalendarData();
    }

    public function nextMonth() {
        $date = Carbon::create($this->year, $this->month, 1)->addMonth();
        $this->year = $date->year;
        $this->month = $date->month;
        $this->fetchCalendarData();
    }

    public function previousMonth() {
        $date = Carbon::create($this->year, $this->month, 1)->subMonth();
        $this->year = $date->year;
        $this->month = $date->month;
        $this->fetchCalendarData();
    }

    public function fetchCalendarData() {
        $this->monthName = Carbon::create($this->year, $this->month, 1)->format('F');
        $startDate = Carbon::create($this->year, $this->month, 1)->startOfMonth();
        $endDate = Carbon::create($this->year, $this->month, 1)->endOfMonth();

        $penugasans = Penugasan::with('pegawai')
            ->when($this->selectedPegawai, function ($query) {
                return $query->where('nip', $this->selectedPegawai);
            })
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('tgl_mulai_tugas', [$startDate, $endDate])
                    ->orWhereBetween('tgl_akhir_tugas', [$startDate, $endDate])
                    ->orWhere(function ($query) use ($startDate, $endDate) {
                        $query->where('tgl_mulai_tugas', '<=', $startDate)
                            ->where('tgl_akhir_tugas', '>=', $endDate);
                    });
            })
            ->whereIn('jenis_surat_tugas', ['PERJALAN_DINAS_DALAM_KOTA', 'PERJALANAN_DINAS_LUAR_KOTA'])
            ->whereHas('riwayatPengajuan', function ($query) {
                $query->whereIn('status', [
                    'STATUS_PENGAJUAN_DICETAK',
                    'STATUS_PENGAJUAN_DICAIRKAN',
                    'STATUS_PENGAJUAN_DISETUJUI',
                    'STATUS_PENGAJUAN_DIKUMPULKAN'
                ]);
            })
            ->get();

        $calendarData = [];
        for ($day = 1; $day <= $endDate->daysInMonth; $day++) {
            $calendarData[$day] = [];
        }

        foreach ($penugasans as $penugasan) {
            $start = Carbon::parse($penugasan->tgl_mulai_tugas);
            $end = Carbon::parse($penugasan->tgl_akhir_tugas);

            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                if ($date->year == $this->year && $date->month == $this->month) {
                    if (!$penugasan->pegawai) {
                        dd($penugasan);
                    }
                    $calendarData[$date->day][] = $penugasan->pegawai?->nama;
                }
            }
        }

        $this->calendarData = $calendarData;
    }
}
