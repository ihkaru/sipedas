<?php

namespace App\Filament\Pages;

use App\Models\Kegiatan;
use App\Models\Pegawai;
use App\Models\Penugasan;
use App\Supports\Constants;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;

class KalenderPerjadin extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static string $view = 'filament.pages.kalender-perjadin';

    public ?int $year = null;
    public ?int $month = null;
    public array $calendarData = [];
    public ?string $monthName = null;

    public ?string $selectedPegawai = null;
    public ?array $selectedKegiatans = [];

    public array $pegawaiOptions = [];
    public array $kegiatanOptions = [];

    public function mount(): void
    {
        $this->year = now()->year;
        $this->month = now()->month;

        $this->pegawaiOptions = Pegawai::pluck('nama', 'nip')->toArray();
        $this->kegiatanOptions = Kegiatan::orderBy('updated_at', 'desc')->pluck('nama', 'id')->toArray();

        $this->form->fill();
        $this->fetchCalendarData();
    }

    protected function getFormSchema(): array
    {
        return [
            Select::make('selectedPegawai')
                ->label('Pilih Pegawai')
                ->options($this->pegawaiOptions)
                ->live()
                ->searchable(),
            Select::make('selectedKegiatans')
                ->label('Pilih Kegiatan')
                ->options($this->kegiatanOptions)
                ->multiple()
                ->live()
                ->searchable(),
        ];
    }

    public function updatedSelectedPegawai(): void
    {
        $this->fetchCalendarData();
    }

    public function updatedSelectedKegiatans(): void
    {
        $this->fetchCalendarData();
    }

    public function nextMonth(): void
    {
        $date = Carbon::create($this->year, $this->month, 1)->addMonth();
        $this->year = $date->year;
        $this->month = $date->month;
        $this->fetchCalendarData();
    }

    public function previousMonth(): void
    {
        $date = Carbon::create($this->year, $this->month, 1)->subMonth();
        $this->year = $date->year;
        $this->month = $date->month;
        $this->fetchCalendarData();
    }

    public function fetchCalendarData(): void
    {
        $this->monthName = Carbon::create($this->year, $this->month, 1)->format('F');
        $startDate = Carbon::create($this->year, $this->month, 1)->startOfMonth();
        $endDate = Carbon::create($this->year, $this->month, 1)->endOfMonth();

        $penugasans = Penugasan::with(['pegawai', 'kegiatan'])
            ->when($this->selectedPegawai, function ($query) {
                return $query->where('nip', $this->selectedPegawai);
            })
            ->when($this->selectedKegiatans, function ($query) {
                return $query->whereIn('kegiatan_id', $this->selectedKegiatans);
            })
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('tgl_mulai_tugas', [$startDate, $endDate])
                    ->orWhereBetween('tgl_akhir_tugas', [$startDate, $endDate])
                    ->orWhere(function ($query) use ($startDate, $endDate) {
                        $query->where('tgl_mulai_tugas', '<=', $startDate)
                            ->where('tgl_akhir_tugas', '>=', $endDate);
                    });
            })
            ->whereIn('jenis_surat_tugas', [Constants::PERJALAN_DINAS_DALAM_KOTA, Constants::PERJALANAN_DINAS_LUAR_KOTA])
            ->whereHas('riwayatPengajuan', function ($query) {
                $query->whereIn('status', [
                    Constants::STATUS_PENGAJUAN_DISETUJUI,
                    Constants::STATUS_PENGAJUAN_DICETAK,
                    Constants::STATUS_PENGAJUAN_DIKUMPULKAN,
                    Constants::STATUS_PENGAJUAN_DICAIRKAN,
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
                        continue;
                    }
                    // Add only the employee name, ensuring no duplicates per day
                    if (!in_array($penugasan->pegawai->nama, $calendarData[$date->day])) {
                        $calendarData[$date->day][] = $penugasan->pegawai->nama;
                    }
                }
            }
        }

        $this->calendarData = $calendarData;
    }
}

