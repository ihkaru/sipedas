<?php

namespace App\Filament\Pages;

use App\Models\LaporanPerjadin;
use App\Models\Penugasan;
use App\Services\AIService;
use App\Supports\Constants;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Livewire\WithFileUploads;
use Exception;

class LaporanPerjadinPage extends Page
{
    use WithFileUploads;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $title = 'Laporan Perjalanan Dinas';
    protected static bool $shouldRegisterNavigation = false; // Accessible only from dashboard or direct links
    protected static ?string $slug = 'laporan-perjadin/{penugasanId?}';
    protected static string $view = 'filament.pages.laporan-perjadin-page';

    public static function canAccess(): bool
    {
        return true;
    }

    // Form inputs and selection state
    public ?int $selectedSuratTugasId = null;
    public ?string $selectedPelaksanaNip = null;
    public string $tanggalLaporan;
    public string $kegiatanNama = '';
    public array $harian = [];
    public array $photos = []; // Holds temp file uploads per day index

    // Dropdown list options
    public array $suratTugasOptions = [];
    public array $pelaksanaOptions = [];

    // State parameters
    public bool $isGenerating = false;
    public bool $isGenerated = false;
    public bool $isEditing = false;
    public bool $hasSaved = false;
    public ?string $errorMessage = null;

    // The generated AI report layout
    public array $reportData = [
        'ringkasan' => '',
        'kegiatan_harian' => [],
        'kesimpulan' => '',
        'tindak_lanjut' => '',
    ];

    public function getPenugasan(): ?Penugasan
    {
        if (!$this->selectedSuratTugasId || !$this->selectedPelaksanaNip) {
            return null;
        }

        return Penugasan::where('surat_tugas_id', $this->selectedSuratTugasId)
            ->where('nip', $this->selectedPelaksanaNip)
            ->with(['kegiatan', 'laporanPerjadin', 'pegawai'])
            ->first();
    }

    public function mount(?int $penugasanId = null): void
    {
        $this->tanggalLaporan = now()->toDateString();
        $this->loadSuratTugasOptions();

        if ($penugasanId) {
            $penugasan = Penugasan::find($penugasanId);
            if ($penugasan) {
                $this->selectedSuratTugasId = $penugasan->surat_tugas_id;
                $this->loadPelaksanaOptions();
                $this->selectedPelaksanaNip = $penugasan->nip;
                $this->loadPenugasanData();
            }
        }
    }

    /**
     * Load unique Surat Tugas options that have valid statuses.
     */
    protected function loadSuratTugasOptions(): void
    {
        $this->suratTugasOptions = Penugasan::whereNotNull('surat_tugas_id')
            ->whereHas('riwayatPengajuan', function ($q) {
                $q->whereIn('status', [
                    Constants::STATUS_PENGAJUAN_DISETUJUI,
                    Constants::STATUS_PENGAJUAN_DICETAK,
                    Constants::STATUS_PENGAJUAN_DIKUMPULKAN,
                    Constants::STATUS_PENGAJUAN_DICAIRKAN,
                ]);
            })
            ->where(function ($q) {
                $q->where('nip', auth()->user()->pegawai?->nip)
                  ->orWhere('nip_pengaju', auth()->user()->pegawai?->nip);
            })
            ->with(['kegiatan', 'suratTugas'])
            ->get()
            ->groupBy('surat_tugas_id')
            ->mapWithKeys(function ($group) {
                $first = $group->first();
                $nomor = $first->suratTugas?->nomor ?? 'Draft/Belum Ada Nomor';
                $label = "No: {$nomor} - {$first->kegiatan?->nama} (" . Carbon::parse($first->tgl_mulai_tugas)->format('d M') . " s.d " . Carbon::parse($first->tgl_akhir_tugas)->format('d M Y') . ")";
                return [$first->surat_tugas_id => $label];
            })
            ->toArray();
    }

    /**
     * Handle updating the selected Surat Tugas.
     */
    public function updatedSelectedSuratTugasId($value): void
    {
        $this->selectedPelaksanaNip = null;
        $this->penugasan = null;
        $this->isGenerated = false;
        $this->isEditing = false;
        $this->hasSaved = false;
        $this->harian = [];
        $this->photos = [];
        $this->kegiatanNama = '';

        if ($value) {
            $this->loadPelaksanaOptions();
            // Auto select if only one pelaksana is attached
            if (count($this->pelaksanaOptions) === 1) {
                $this->selectedPelaksanaNip = array_key_first($this->pelaksanaOptions);
                $this->loadPenugasanData();
            }
        } else {
            $this->pelaksanaOptions = [];
        }
    }

    /**
     * Load Pelaksana options based on the selected Surat Tugas.
     */
    protected function loadPelaksanaOptions(): void
    {
        if (!$this->selectedSuratTugasId) {
            $this->pelaksanaOptions = [];
            return;
        }

        $this->pelaksanaOptions = Penugasan::where('surat_tugas_id', $this->selectedSuratTugasId)
            ->with('pegawai')
            ->get()
            ->mapWithKeys(function ($p) {
                $name = $p->pegawai?->nama ?? $p->nip;
                return [$p->nip => "{$name} (NIP. {$p->nip})"];
            })
            ->toArray();
    }

    /**
     * Handle updating selected pelaksana.
     */
    public function updatedSelectedPelaksanaNip($value): void
    {
        $this->penugasan = null;
        $this->isGenerated = false;
        $this->isEditing = false;
        $this->hasSaved = false;
        $this->harian = [];
        $this->photos = [];
        $this->kegiatanNama = '';

        if ($value) {
            $this->loadPenugasanData();
        }
    }

    /**
     * Load core assignment data and existing reports if they exist.
     */
    protected function loadPenugasanData(): void
    {
        if (!$this->selectedSuratTugasId || !$this->selectedPelaksanaNip) {
            return;
        }

        $penugasan = $this->getPenugasan();

        if (!$penugasan) {
            return;
        }

        $this->kegiatanNama = $penugasan->kegiatan?->nama ?? '';

        // If report already exists, load directly into preview mode
        if ($penugasan->laporanPerjadin) {
            $laporan = $penugasan->laporanPerjadin;
            $this->tanggalLaporan = $laporan->tanggal_laporan->toDateString();
            $this->harian = $laporan->isi_kegiatan ?? [];
            $this->reportData = $laporan->hasil_kegiatan ?? [
                'ringkasan' => '',
                'kegiatan_harian' => [],
                'kesimpulan' => '',
                'tindak_lanjut' => '',
            ];
            $this->isGenerated = true;
            $this->hasSaved = true;
            return;
        }

        // Otherwise generate dynamic form dates
        $dates = Penugasan::generateDateRange(
            Carbon::parse($penugasan->tgl_mulai_tugas),
            Carbon::parse($penugasan->tgl_akhir_tugas)
        );

        $this->harian = [];
        foreach ($dates as $date) {
            $this->harian[] = [
                'tanggal' => $date,
                'waktu_mulai' => '08:00',
                'waktu_selesai' => '12:00',
                'koordinat' => '',
                'uraian_draft' => '',
                'kendala' => '',
                'foto' => [],
                'gunakan_timestamp' => false,
            ];
        }
        $this->photos = array_fill(0, count($dates), []);
    }

    /**
     * Trigger the AI text generator.
     */
    public function generateReport(AIService $aiService): void
    {
        $this->validate([
            'selectedSuratTugasId' => 'required',
            'selectedPelaksanaNip' => 'required',
            'tanggalLaporan' => 'required|date',
            'harian.*.uraian_draft' => 'required|string|min:5',
        ], [
            'harian.*.uraian_draft.required' => 'Uraian kegiatan per tanggal wajib diisi.',
            'harian.*.uraian_draft.min' => 'Uraian kegiatan minimal 5 karakter.',
        ]);

        $this->isGenerating = true;
        $this->errorMessage = null;

        try {
            // Map inputs for the prompt
            $rawInput = array_map(function ($day) {
                return [
                    'tanggal' => $day['tanggal'],
                    'waktu' => "{$day['waktu_mulai']} s.d {$day['waktu_selesai']}",
                    'koordinat' => $day['koordinat'],
                    'uraian_draf' => $day['uraian_draft'],
                    'kendala_draf' => $day['kendala'] ?: 'Tidak ada kendala',
                ];
            }, $this->harian);

            $this->reportData = $aiService->generateStructuredReport($rawInput, $this->kegiatanNama);
            $this->isGenerated = true;

        } catch (Exception $e) {
            $this->errorMessage = "Gagal memproses AI: " . $e->getMessage();
            Notification::make()
                ->title('Gagal Memanggil AI Service')
                ->body($e->getMessage())
                ->danger()
                ->send();
        } finally {
            $this->isGenerating = false;
        }
    }

    /**
     * Save the report draft and final AI polished structure.
     */
    public function saveReport(): void
    {
        $penugasan = $this->getPenugasan();
        if (!$penugasan) {
            return;
        }

        // Process file uploads
        $processedHarian = [];
        $allPhotos = [];

        foreach ($this->harian as $idx => $day) {
            $dayPhotos = $day['foto'] ?? [];

            if (!empty($this->photos[$idx])) {
                foreach ($this->photos[$idx] as $uploadedFile) {
                    $path = $uploadedFile->store('laporan-perjadin', 'public');
                    $dayPhotos[] = $path;
                    $allPhotos[] = $path;
                }
            }

            $processedHarian[] = [
                'tanggal' => $day['tanggal'],
                'waktu_mulai' => $day['waktu_mulai'],
                'waktu_selesai' => $day['waktu_selesai'],
                'koordinat' => $day['koordinat'],
                'uraian_draft' => $day['uraian_draft'],
                'kendala' => $day['kendala'],
                'foto' => $dayPhotos,
                'gunakan_timestamp' => $day['gunakan_timestamp'] ?? false,
            ];
        }

        $this->harian = $processedHarian;
        $this->photos = array_fill(0, count($this->harian), []); // Reset uploads state

        $laporan = LaporanPerjadin::updateOrCreate(
            ['penugasan_id' => $penugasan->id],
            [
                'tanggal_laporan' => $this->tanggalLaporan,
                'isi_kegiatan' => $processedHarian,
                'hasil_kegiatan' => $this->reportData,
                'foto_dokumentasi' => array_merge($penugasan->laporanPerjadin?->foto_dokumentasi ?? [], $allPhotos),
            ]
        );

        $this->hasSaved = true;
        $this->isEditing = false;

        Notification::make()
            ->title('Laporan Perjalanan Dinas berhasil disimpan')
            ->success()
            ->send();
    }

    /**
     * Toggle editing mode on preview screen.
     */
    public function toggleEditMode(): void
    {
        $this->isEditing = !$this->isEditing;
    }

    /**
     * Reset the form and return to input mode.
     */
    public function editDraft(): void
    {
        $this->isGenerated = false;
        $this->hasSaved = false;
        $this->isEditing = false;
    }
}
