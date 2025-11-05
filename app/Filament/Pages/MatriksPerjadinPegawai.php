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
use Illuminate\Support\Collection;

class MatriksPerjadinPegawai extends Page implements HasForms {
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.matriks-perjadin-pegawai';
    protected static ?string $title = 'Matriks Perjadin Pegawai';

    public ?array $selectedKegiatans = [];
        public ?string $selectedPegawai = null;
        public ?array $selectedStatuses = [];
    
        public array $kegiatanOptions = [];
        public array $pegawaiOptions = [];
        public array $statusOptions = [];
    
        public array $tableData = [];
        public array $dateColumns = [];
        public array $summaryTableData = [];
        public array $kegiatanColumns = [];
    
        public function mount(): void
        {
            $this->kegiatanOptions = Kegiatan::orderBy('updated_at', 'desc')->pluck('nama', 'id')->toArray();
            $this->pegawaiOptions = Pegawai::pluck('nama', 'nip')->toArray();
            $this->statusOptions = Constants::STATUS_PENGAJUAN_OPTIONS;
            $this->selectedStatuses = [
                Constants::STATUS_PENGAJUAN_DISETUJUI,
                Constants::STATUS_PENGAJUAN_DICETAK,
                Constants::STATUS_PENGAJUAN_DIKUMPULKAN,
                Constants::STATUS_PENGAJUAN_DICAIRKAN,
            ];
            $this->form->fill([
                'selectedStatuses' => $this->selectedStatuses,
            ]);
        }



    protected function getFormSchema(): array {

        return [

            Select::make('selectedKegiatans')

                ->label('Pilih Kegiatan')

                ->options($this->kegiatanOptions)

                ->multiple()

                ->live()

                ->searchable(),

            Select::make('selectedPegawai')

                ->label('Pilih Pegawai')

                ->options($this->pegawaiOptions)

                ->live()

                                ->searchable()

                                ->nullable(),

                            Select::make('selectedStatuses')

                                ->label('Pilih Status Pengajuan')

                                ->options($this->statusOptions)

                                ->multiple()

                                ->live()

                                ->searchable(),

                        ];
    }



    public function updatedSelectedKegiatans(): void {

        $this->fetchTableData();
    }



        public function updatedSelectedPegawai(): void



        {



            $this->fetchTableData();



        }



    



        public function updatedSelectedStatuses(): void



        {



            $this->fetchTableData();



        }



    



        public function fetchTableData(): void



        {



            if (empty($this->selectedKegiatans) || empty($this->selectedStatuses)) {



                $this->tableData = [];



                $this->dateColumns = [];



                $this->summaryTableData = [];



                $this->kegiatanColumns = [];



                return;



            }



    



            // Define the columns for the summary table based on selected kegiatans



            $this->kegiatanColumns = Kegiatan::whereIn('id', $this->selectedKegiatans)



                ->orderBy('nama')



                ->pluck('nama', 'id')



                ->toArray();



    



            $penugasans = Penugasan::with(['pegawai', 'kegiatan', 'tujuanSuratTugas'])



                ->whereIn('kegiatan_id', $this->selectedKegiatans)



                ->when($this->selectedPegawai, function ($query) {



                    return $query->where('nip', $this->selectedPegawai);



                })



                ->whereIn('jenis_surat_tugas', [



                    Constants::PERJALAN_DINAS_DALAM_KOTA,



                    Constants::PERJALANAN_DINAS_LUAR_KOTA



                ])



                ->whereHas('riwayatPengajuan', function ($query) {



                    $query->whereIn('status', $this->selectedStatuses);



                })



                ->orderBy('tgl_mulai_tugas')



                ->get();



    



            $this->processPenugasans($penugasans);



        }

    private function processPenugasans(Collection $penugasans): void {
        $tableData = [];
        $summaryData = [];
        $allDates = collect();

        foreach ($penugasans as $penugasan) {
            if (!$penugasan->pegawai) {
                continue;
            }

            $nip = $penugasan->pegawai->nip;
            $tujuan = $penugasan->tujuanPenugasan;
            $kegiatanId = $penugasan->kegiatan_id;

            // Initialize data structure for the first table (per-pegawai)
            if (!isset($tableData[$nip])) {
                $tableData[$nip] = [
                    'nama' => $penugasan->pegawai->nama,
                    'total_perjadin' => 0,
                    'dates' => [],
                ];
            }

            // Initialize data structure for the second table (summary by tujuan)
            if (!isset($summaryData[$tujuan])) {
                $summaryData[$tujuan] = [
                    'tujuan' => $tujuan,
                    'total_perjadin' => 0,
                    'kegiatan_counts' => array_fill_keys(array_keys($this->kegiatanColumns), 0),
                ];
            }

            $currentDate = Carbon::parse($penugasan->tgl_mulai_tugas);
            $endDate = Carbon::parse($penugasan->tgl_akhir_tugas);

            while ($currentDate->lte($endDate)) {
                $dateString = $currentDate->toDateString();
                $allDates->push($dateString);

                // Populate data for the first table
                if (!isset($tableData[$nip]['dates'][$dateString])) {
                    $tableData[$nip]['dates'][$dateString] = $penugasan->kegiatan->nama . ' (' . $tujuan . ')';
                    $tableData[$nip]['total_perjadin']++;
                }

                // Populate data for the second table
                $summaryData[$tujuan]['total_perjadin']++;
                if (isset($summaryData[$tujuan]['kegiatan_counts'][$kegiatanId])) {
                    $summaryData[$tujuan]['kegiatan_counts'][$kegiatanId]++;
                }

                $currentDate->addDay();
            }
        }

        $this->dateColumns = $allDates->unique()->sort()->values()->toArray();
        $this->tableData = array_values($tableData);
        $this->summaryTableData = array_values($summaryData);
    }
}
