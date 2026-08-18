<?php

namespace App\Filament\Pages;

use App\Models\LaporanPerjadin;
use App\Models\Penugasan;
use App\Models\PresetRutePerjadin;
use App\Services\AIService;
use App\Supports\Constants;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use Exception;

class LaporanPerjadinPage extends Page
{
    use WithFileUploads;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $title = 'Laporan Perjalanan Dinas';
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $slug = 'laporan-perjadin/{penugasanId?}';
    protected string $view = 'filament.pages.laporan-perjadin-page';

    public static function canAccess(): bool
    {
        return true;
    }

    // Form inputs and selection state
    public ?int $selectedSuratTugasId = null;
    public ?string $selectedPelaksanaNip = null;
    public string $tanggalLaporan;
    public string $kegiatanNama = '';
    public string $nomorSuratTugas = '';
    public string $nomorSpd = '';
    public string $modaTransportasi = 'Kendaraan Pribadi';
    public string $daerahDikunjungi = '';
    public string $modeLaporan = 'harian'; // 'harian' (Default) or 'periodik'
    public ?string $minDate = null;
    public ?string $maxDate = null;
    public ?string $periodeStr = '';

    // Standard Itinerary Preset for the detected Kecamatan
    public array $presetSteps = [];

    // Dynamic daily items with Sequential Stops (Titik Singgah Berurutan)
    public array $harian = [];
    public array $photos = []; // Holds temp file uploads per day index

    // Periodic items
    public array $periodikData = [
        'cakupan_wilayah' => '',
        'uraian_draft' => '',
        'kendala' => '',
        'titik_kegiatan' => [
            ['nama_titik' => 'Titik Utama / Posko', 'koordinat' => '', 'uraian' => '', 'foto' => []]
        ],
        'foto' => [],
    ];
    public array $periodikPhotos = [];

    // Dropdown list options
    public array $suratTugasOptions = [];
    public array $pelaksanaOptions = [];

    // State parameters
    public bool $isGenerating = false;
    public bool $isGenerated = false;
    public bool $isEditing = false;
    public bool $hasSaved = false;
    public bool $hasDraft = false;
    public ?string $draftSavedAt = null;
    public ?string $errorMessage = null;

    // The generated AI report layout (5-column official BPS standard format)
    public array $reportData = [
        'dasar_pelaksanaan' => '',
        'moda_transportasi' => '',
        'daerah_dikunjungi' => '',
        'tabel_kegiatan' => [],
        'ringkasan' => '',
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
            ->with(['kegiatan', 'laporanPerjadin', 'pegawai', 'suratTugas', 'tujuanSuratTugas'])
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

    protected function loadSuratTugasOptions(): void
    {
        $user = auth()->user();
        $isAdmin = $user?->hasRole('super_admin') || $user?->hasRole('operator_umum') || $user?->hasRole('kepala_satker');

        $query = Penugasan::whereNotNull('surat_tugas_id')
            ->whereHas('riwayatPengajuan', function ($q) {
                $q->whereIn('status', [
                    Constants::STATUS_PENGAJUAN_DISETUJUI,
                    Constants::STATUS_PENGAJUAN_DICETAK,
                    Constants::STATUS_PENGAJUAN_DIKUMPULKAN,
                    Constants::STATUS_PENGAJUAN_DICAIRKAN,
                ]);
            });

        if (!$isAdmin) {
            $userNip = $user?->pegawai?->nip;
            $query->where(function ($q) use ($userNip) {
                $q->where('nip', $userNip)
                  ->orWhere('nip_pengaju', $userNip);
            });
        }

        $this->suratTugasOptions = $query
            ->with(['kegiatan', 'suratTugas', 'suratPerjadin'])
            ->get()
            ->groupBy('surat_tugas_id')
            ->mapWithKeys(function ($group) {
                $first = $group->first();
                $nomorST = $first->suratTugas?->nomor_surat_tugas ?? ($first->suratTugas?->nomor ? "B-{$first->suratTugas->nomor}" : 'Draft/Belum Ada Nomor');
                $nomorSPD = $first->suratPerjadin?->nomor_surat_perjadin ? " (SPD: {$first->suratPerjadin->nomor_surat_perjadin})" : '';
                $label = "ST: {$nomorST}{$nomorSPD} - {$first->kegiatan?->nama} (" . Carbon::parse($first->tgl_mulai_tugas)->format('d M') . " s.d " . Carbon::parse($first->tgl_akhir_tugas)->format('d M Y') . ")";
                return [$first->surat_tugas_id => $label];
            })
            ->toArray();
    }

    public function updatedSelectedSuratTugasId($value): void
    {
        $this->selectedPelaksanaNip = null;
        $this->isGenerated = false;
        $this->isEditing = false;
        $this->hasSaved = false;
        $this->hasDraft = false;
        $this->draftSavedAt = null;
        $this->harian = [];
        $this->photos = [];
        $this->periodikPhotos = [];
        $this->kegiatanNama = '';
        $this->nomorSuratTugas = '';
        $this->nomorSpd = '';
        $this->modaTransportasi = 'Kendaraan Pribadi';
        $this->daerahDikunjungi = '';

        if ($value) {
            $this->loadPelaksanaOptions();
            if (count($this->pelaksanaOptions) === 1) {
                $this->selectedPelaksanaNip = array_key_first($this->pelaksanaOptions);
                $this->loadPenugasanData();
            }
        } else {
            $this->pelaksanaOptions = [];
        }
    }

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

    public function updatedSelectedPelaksanaNip($value): void
    {
        $this->isGenerated = false;
        $this->isEditing = false;
        $this->hasSaved = false;
        $this->hasDraft = false;
        $this->draftSavedAt = null;
        $this->harian = [];
        $this->photos = [];
        $this->periodikPhotos = [];

        if ($value) {
            $this->loadPenugasanData();
        }
    }

    public function setModeLaporan(string $mode): void
    {
        $this->modeLaporan = $mode;
    }

    /**
     * Add a dynamic day entry with Smart Sequential Stops.
     */
    public function addHarian(?string $date = null): void
    {
        $nextDate = $date;
        if (!$nextDate) {
            if (!empty($this->harian)) {
                $lastDate = end($this->harian)['tanggal'];
                $candidate = Carbon::parse($lastDate)->addDay();
                if ($this->maxDate && $candidate->gt(Carbon::parse($this->maxDate))) {
                    $nextDate = $this->maxDate;
                } else {
                    $nextDate = $candidate->toDateString();
                }
            } else {
                $nextDate = $this->minDate ?? now()->toDateString();
            }
        }

        $presetModel = PresetRutePerjadin::getPresetForKecamatan($this->daerahDikunjungi);
        $dayPresets = PresetRutePerjadin::getStepsForKecamatan($this->daerahDikunjungi, $nextDate);
        $startTime = !empty($dayPresets[0]['waktu']) ? explode('-', $dayPresets[0]['waktu'])[0] : '08.00';
        $endTime = !empty($dayPresets[5]['waktu']) ? explode('-', $dayPresets[5]['waktu'])[1] : '15.15';

        $camatName = $presetModel?->kantor_camat_nama ?? 'Kantor Camat (Visum SPPD & Koordinasi)';
        $camatCoord = $presetModel?->kantor_camat_koordinat ?? '';

        $this->harian[] = [
            'tanggal' => $nextDate,
            'waktu_mulai' => trim($startTime),
            'waktu_selesai' => trim($endTime),
            'titik_kegiatan' => [
                [
                    'kategori' => 'kantor_camat',
                    'nama_titik' => $camatName . ' (Visum SPPD)',
                    'koordinat' => $camatCoord,
                    'uraian' => "Perjalanan menuju {$camatName} untuk koordinasi pelaksanaan tugas dan penandatanganan/cap visum SPPD.",
                    'kendala' => '',
                    'foto' => [],
                ],
                [
                    'kategori' => 'lapangan',
                    'nama_titik' => 'Lokasi Lapangan / Sampel 1',
                    'koordinat' => '',
                    'uraian' => '',
                    'kendala' => '',
                    'foto' => [],
                ],
            ],
            'gunakan_timestamp' => true,
        ];
    }

    public function removeHarian(int $index): void
    {
        if (count($this->harian) <= 1) {
            Notification::make()
                ->title('Minimal harus ada 1 tanggal kegiatan')
                ->warning()
                ->send();
            return;
        }

        unset($this->harian[$index]);
        $this->harian = array_values($this->harian);
    }

    /**
     * Add a sequential stop / activity spot to a specific date.
     */
    public function addTitikKegiatan(int $dayIndex): void
    {
        $spotNumber = count($this->harian[$dayIndex]['titik_kegiatan'] ?? []) + 1;
        $this->harian[$dayIndex]['titik_kegiatan'][] = [
            'kategori' => 'lapangan',
            'nama_titik' => "Lokasi Lapangan / Sampel {$spotNumber}",
            'koordinat' => '',
            'uraian' => '',
            'kendala' => '',
            'foto' => [],
        ];
    }

    /**
     * Remove a stop from a specific date.
     */
    public function removeTitikKegiatan(int $dayIndex, int $spotIndex): void
    {
        if (count($this->harian[$dayIndex]['titik_kegiatan'] ?? []) <= 1) {
            Notification::make()
                ->title('Minimal harus ada 1 titik kegiatan')
                ->warning()
                ->send();
            return;
        }
        unset($this->harian[$dayIndex]['titik_kegiatan'][$spotIndex]);
        $this->harian[$dayIndex]['titik_kegiatan'] = array_values($this->harian[$dayIndex]['titik_kegiatan']);
    }

    /**
     * Quick action to load workdays only (Mon - Fri).
     */
    public function loadWorkdaysOnly(): void
    {
        if (!$this->minDate || !$this->maxDate) {
            return;
        }

        $start = Carbon::parse($this->minDate);
        $end = Carbon::parse($this->maxDate);

        $this->harian = [];

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            if (!$date->isWeekend()) {
                $this->addHarian($date->toDateString());
            }
        }

        if (empty($this->harian)) {
            $this->addHarian($this->minDate);
        }

        Notification::make()
            ->title('Berhasil memuat ' . count($this->harian) . ' hari kerja')
            ->success()
            ->send();
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
        $this->nomorSuratTugas = $penugasan->suratTugas?->nomor_surat_tugas ?? ($penugasan->suratTugas?->nomor ? "B-{$penugasan->suratTugas->nomor}" : '-');
        $this->nomorSpd = $penugasan->suratPerjadin?->nomor_surat_perjadin ?? ($penugasan->suratPerjadin?->nomor ? "{$penugasan->suratPerjadin->nomor}/SPD" : ($penugasan->jenis_surat_tugas === Constants::NON_SPPD ? 'Non-SPPD' : '-'));
        $this->modaTransportasi = $penugasan->jenis_transportasi ?? 'Kendaraan Pribadi';
        $this->daerahDikunjungi = $penugasan->tujuan_penugasan ?: 'Kecamatan Mempawah Timur';
        
        $this->minDate = Carbon::parse($penugasan->tgl_mulai_tugas)->toDateString();
        $this->maxDate = Carbon::parse($penugasan->tgl_akhir_tugas)->toDateString();
        $this->periodeStr = Carbon::parse($this->minDate)->translatedFormat('d F Y') . ' s.d ' . Carbon::parse($this->maxDate)->translatedFormat('d F Y');

        $this->presetSteps = PresetRutePerjadin::getStepsForKecamatan($this->daerahDikunjungi, $this->minDate);

        // If report already exists in database
        if ($penugasan->laporanPerjadin) {
            $laporan = $penugasan->laporanPerjadin;
            $this->tanggalLaporan = $laporan->tanggal_laporan->toDateString();
            
            $isiKegiatan = $laporan->isi_kegiatan ?? [];
            if (isset($isiKegiatan['mode_laporan']) && $isiKegiatan['mode_laporan'] === 'periodik') {
                $this->modeLaporan = 'periodik';
                $this->periodikData = $isiKegiatan['data'] ?? [
                    'cakupan_wilayah' => $this->daerahDikunjungi,
                    'uraian_draft' => '',
                    'kendala' => '',
                    'titik_kegiatan' => [
                        ['nama_titik' => 'Titik Utama / Posko', 'koordinat' => '', 'uraian' => '', 'foto' => []]
                    ],
                    'foto' => [],
                ];
            } else {
                $this->modeLaporan = 'harian';
                $rawItems = isset($isiKegiatan['items']) ? $isiKegiatan['items'] : (is_array($isiKegiatan) ? $isiKegiatan : []);
                
                // Ensure backward compatibility with titik_kegiatan
                $this->harian = array_map(function ($item) {
                    if (!isset($item['titik_kegiatan']) || !is_array($item['titik_kegiatan'])) {
                        $spots = [];
                        if (!empty($item['koordinat_list'])) {
                            foreach ($item['koordinat_list'] as $s) {
                                $spots[] = [
                                    'kategori' => 'lapangan',
                                    'nama_titik' => $s['label'] ?? 'Lokasi Sampel',
                                    'koordinat' => $s['coords'] ?? '',
                                    'uraian' => $item['uraian_draft'] ?? '',
                                    'kendala' => $item['kendala'] ?? '',
                                    'foto' => $item['foto'] ?? [],
                                ];
                            }
                        } else {
                            $presetModel = PresetRutePerjadin::getPresetForKecamatan($this->daerahDikunjungi);
                            $camatName = $presetModel?->kantor_camat_nama ?? 'Kantor Camat (Visum SPPD & Koordinasi)';
                            $camatCoord = $item['koordinat'] ?: ($presetModel?->kantor_camat_koordinat ?? '');

                            $spots[] = [
                                'kategori' => 'kantor_camat',
                                'nama_titik' => $camatName . ' (Visum SPPD)',
                                'koordinat' => $camatCoord,
                                'uraian' => "Perjalanan menuju {$camatName} untuk koordinasi pelaksanaan tugas dan penandatanganan/cap visum SPPD.",
                                'kendala' => '',
                                'foto' => [],
                            ];
                            $spots[] = [
                                'kategori' => 'lapangan',
                                'nama_titik' => 'Lokasi Lapangan / Sampel 1',
                                'koordinat' => '',
                                'uraian' => $item['uraian_draft'] ?? '',
                                'kendala' => $item['kendala'] ?? '',
                                'foto' => $item['foto'] ?? [],
                            ];
                        }
                        $item['titik_kegiatan'] = $spots;
                    }
                    return $item;
                }, $rawItems);
            }

            $this->reportData = $laporan->hasil_kegiatan ?? [
                'dasar_pelaksanaan' => $this->nomorSuratTugas,
                'moda_transportasi' => $this->modaTransportasi,
                'daerah_dikunjungi' => $this->daerahDikunjungi,
                'tabel_kegiatan' => [],
                'ringkasan' => '',
                'kesimpulan' => '',
                'tindak_lanjut' => '',
            ];

            if (!empty($this->reportData['tabel_kegiatan']) || !empty($this->reportData['uraian_kegiatan_polished'])) {
                $this->isGenerated = true;
                $this->hasSaved = true;
                $this->hasDraft = false;
            } else {
                $this->isGenerated = false;
                $this->hasDraft = true;
                $this->draftSavedAt = $laporan->updated_at ? $laporan->updated_at->translatedFormat('d F Y, H:i') . ' WIB' : null;
            }
            return;
        }

        // Initialize default days for Mode 1
        $this->modeLaporan = 'harian';
        $diffDays = Carbon::parse($this->minDate)->diffInDays(Carbon::parse($this->maxDate)) + 1;

        $this->harian = [];

        if ($diffDays <= 5) {
            $dates = Penugasan::generateDateRange(
                Carbon::parse($this->minDate),
                Carbon::parse($this->maxDate)
            );
            foreach ($dates as $date) {
                $this->addHarian($date);
            }
        } else {
            $this->addHarian($this->minDate);
        }
    }

    /**
     * Save draft directly while in the field without triggering AI.
     */
    public function saveDraftOnly(bool $silent = false): void
    {
        $penugasan = $this->getPenugasan();
        if (!$penugasan) {
            return;
        }

        $allPhotos = [];

        if ($this->modeLaporan === 'periodik') {
            $periodikSavedPhotos = $this->periodikData['foto'] ?? [];
            if (!empty($this->periodikPhotos)) {
                foreach ($this->periodikPhotos as $uploadedFile) {
                    $path = $uploadedFile->store('laporan-perjadin', 'public');
                    $periodikSavedPhotos[] = $path;
                    $allPhotos[] = $path;
                }
            }
            $this->periodikData['foto'] = $periodikSavedPhotos;
            $this->periodikPhotos = [];

            $savedIsiKegiatan = [
                'mode_laporan' => 'periodik',
                'data' => $this->periodikData,
            ];
        } else {
            $savedIsiKegiatan = [
                'mode_laporan' => 'harian',
                'items' => $this->harian,
            ];

            foreach ($this->harian as $day) {
                foreach ($day['titik_kegiatan'] ?? [] as $spot) {
                    if (!empty($spot['foto'])) {
                        $allPhotos = array_merge($allPhotos, $spot['foto']);
                    }
                }
            }
        }

        LaporanPerjadin::updateOrCreate(
            ['penugasan_id' => $penugasan->id],
            [
                'tanggal_laporan' => $this->tanggalLaporan,
                'isi_kegiatan' => $savedIsiKegiatan,
                'status_laporan' => 'draft',
                'foto_dokumentasi' => array_merge($penugasan->laporanPerjadin?->foto_dokumentasi ?? [], $allPhotos),
            ]
        );

        $this->hasDraft = true;
        $this->draftSavedAt = now()->translatedFormat('d F Y, H:i') . ' WIB';

        if (!$silent) {
            Notification::make()
                ->title('Draf kegiatan lapangan berhasil disimpan sementara')
                ->body('Data titik singgah, GPS, foto, dan catatan tersimpan di database.')
                ->success()
                ->send();
        }
    }

    /**
     * Save watermarked image data URL directly to public storage.
     */
    public function saveWatermarkedPhoto(int $dayIndex, int $spotIndex, string $dataUrl, bool $isPeriodic = false): void
    {
        if (preg_match('/^data:image\/(\w+);base64,/', $dataUrl, $type)) {
            $data = substr($dataUrl, strpos($dataUrl, ',') + 1);
            $type = strtolower($type[1]); // jpg, png, etc.

            $data = base64_decode($data);
            if ($data === false) {
                return;
            }

            $fileName = 'laporan-perjadin/' . Str::random(24) . '.' . ($type === 'jpeg' ? 'jpg' : $type);
            Storage::disk('public')->put($fileName, $data);

            if ($isPeriodic) {
                $this->periodikData['titik_kegiatan'][$spotIndex]['foto'][] = $fileName;
                $this->periodikData['foto'][] = $fileName;
            } else {
                $this->harian[$dayIndex]['titik_kegiatan'][$spotIndex]['foto'][] = $fileName;
            }

            $this->saveDraftOnly(silent: true);

            Notification::make()
                ->title('Foto berhasil di-watermark & disimpan')
                ->success()
                ->send();
        }
    }

    /**
     * Remove a photo from a specific activity spot.
     */
    public function removeSpotPhoto(int $dayIndex, int $spotIndex, int $photoIndex, bool $isPeriodic = false): void
    {
        if ($isPeriodic) {
            $photoPath = $this->periodikData['titik_kegiatan'][$spotIndex]['foto'][$photoIndex] ?? null;
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
                unset($this->periodikData['titik_kegiatan'][$spotIndex]['foto'][$photoIndex]);
                $this->periodikData['titik_kegiatan'][$spotIndex]['foto'] = array_values($this->periodikData['titik_kegiatan'][$spotIndex]['foto']);
            }
        } else {
            $photoPath = $this->harian[$dayIndex]['titik_kegiatan'][$spotIndex]['foto'][$photoIndex] ?? null;
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
                unset($this->harian[$dayIndex]['titik_kegiatan'][$spotIndex]['foto'][$photoIndex]);
                $this->harian[$dayIndex]['titik_kegiatan'][$spotIndex]['foto'] = array_values($this->harian[$dayIndex]['titik_kegiatan'][$spotIndex]['foto']);
            }
        }

        $this->saveDraftOnly(silent: true);
    }

    /**
     * Delete existing draft and reset the form.
     */
    public function deleteDraft(): void
    {
        $penugasan = $this->getPenugasan();
        if ($penugasan && $penugasan->laporanPerjadin) {
            $penugasan->laporanPerjadin->delete();
        }

        $this->hasDraft = false;
        $this->draftSavedAt = null;
        $this->isGenerated = false;
        $this->hasSaved = false;
        $this->isEditing = false;
        $this->harian = [];
        $this->photos = [];
        $this->periodikPhotos = [];
        $this->reportData = [
            'dasar_pelaksanaan' => '',
            'moda_transportasi' => '',
            'daerah_dikunjungi' => '',
            'tabel_kegiatan' => [],
            'ringkasan' => '',
            'kesimpulan' => '',
            'tindak_lanjut' => '',
        ];

        $this->addHarian($this->minDate ?? now()->toDateString());

        Notification::make()
            ->title('Draf sementara berhasil dihapus')
            ->body('Formulir telah direset ke kondisi awal.')
            ->warning()
            ->send();
    }

    /**
     * Trigger the AI text generator with Sequential Stops and Auto-Timing.
     */
    public function generateReport(AIService $aiService): void
    {
        $penugasan = $this->getPenugasan();
        if (!$penugasan) {
            return;
        }

        if ($this->modeLaporan === 'periodik') {
            $this->validate([
                'selectedSuratTugasId' => 'required',
                'selectedPelaksanaNip' => 'required',
                'tanggalLaporan' => 'required|date',
                'periodikData.uraian_draft' => 'required|string|min:10',
            ], [
                'periodikData.uraian_draft.required' => 'Draf uraian pelaksanaan & capaian wajib diisi.',
                'periodikData.uraian_draft.min' => 'Draf uraian minimal 10 karakter.',
            ]);

            $hasPeriodicPhotos = !empty($this->periodikData['foto']) || !empty($this->periodikPhotos);
            if (!$hasPeriodicPhotos) {
                Notification::make()
                    ->title('Foto Dokumentasi Wajib Dilampirkan')
                    ->body('Laporan perjalanan dinas wajib melampirkan minimal 1 foto dokumentasi kegiatan.')
                    ->danger()
                    ->send();
                return;
            }
        } else {
            $this->validate([
                'selectedSuratTugasId' => 'required',
                'selectedPelaksanaNip' => 'required',
                'tanggalLaporan' => 'required|date',
                'harian.*.tanggal' => 'required|date',
            ]);

            // VALIDATION: Setiap tanggal wajib minimal 1 foto dokumentasi dan minimal 1 uraian kegiatan
            foreach ($this->harian as $idx => $day) {
                $totalPhotos = 0;
                $hasUraian = false;

                foreach ($day['titik_kegiatan'] ?? [] as $spot) {
                    $totalPhotos += count($spot['foto'] ?? []);
                    if (!empty(trim($spot['uraian'] ?? ''))) {
                        $hasUraian = true;
                    }
                }

                $tglLabel = Carbon::parse($day['tanggal'])->translatedFormat('l, d F Y');

                if (!$hasUraian) {
                    Notification::make()
                        ->title("Catatan Kegiatan Belum Diisi ({$tglLabel})")
                        ->body("Harap isi uraian kegiatan pada salah satu titik lapangan tanggal {$tglLabel}.")
                        ->danger()
                        ->send();
                    return;
                }

                if ($totalPhotos === 0) {
                    Notification::make()
                        ->title("Foto Dokumentasi Kurang ({$tglLabel})")
                        ->body("Tanggal {$tglLabel} wajib melampirkan minimal 1 foto dokumentasi lapangan.")
                        ->danger()
                        ->send();
                    return;
                }
            }
        }

        $this->isGenerating = true;
        $this->errorMessage = null;

        $metaContext = [
            'kegiatan_nama' => $this->kegiatanNama,
            'nomor_surat_tugas' => $this->nomorSuratTugas,
            'moda_transportasi' => $this->modaTransportasi,
            'daerah_dikunjungi' => $this->daerahDikunjungi,
            'pelaksana_nama' => $penugasan->pegawai?->nama ?? $this->selectedPelaksanaNip,
            'periode_str' => $this->periodeStr,
            'preset_steps' => $this->presetSteps,
        ];

        try {
            if ($this->modeLaporan === 'periodik') {
                $this->reportData = $aiService->generatePeriodicStructuredReport(
                    $this->periodikData,
                    $metaContext
                );
            } else {
                // Map sequential stops for LLM Auto-Timing
                $rawInput = array_map(function ($day) {
                    return [
                        'tanggal' => $day['tanggal'],
                        'waktu_dinas' => "{$day['waktu_mulai']} - {$day['waktu_selesai']}",
                        'is_jumat' => Carbon::parse($day['tanggal'])->isFriday(),
                        'titik_kegiatan' => array_map(function ($spot) {
                            return [
                                'kategori' => $spot['kategori'] ?? 'lapangan',
                                'nama_titik' => $spot['nama_titik'] ?? '',
                                'koordinat' => $spot['koordinat'] ?? '',
                                'uraian' => $spot['uraian'] ?? '',
                                'kendala' => $spot['kendala'] ?? '',
                                'foto_count' => count($spot['foto'] ?? []),
                            ];
                        }, $day['titik_kegiatan'] ?? []),
                        'preset_steps' => PresetRutePerjadin::getStepsForKecamatan($this->daerahDikunjungi, $day['tanggal']),
                    ];
                }, $this->harian);

                $this->reportData = $aiService->generateStructuredReport($rawInput, $metaContext);
            }

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

        $allPhotos = [];

        if ($this->modeLaporan === 'periodik') {
            $savedIsiKegiatan = [
                'mode_laporan' => 'periodik',
                'data' => $this->periodikData,
            ];
            $allPhotos = $this->periodikData['foto'] ?? [];
        } else {
            $savedIsiKegiatan = [
                'mode_laporan' => 'harian',
                'items' => $this->harian,
            ];

            foreach ($this->harian as $day) {
                foreach ($day['titik_kegiatan'] ?? [] as $spot) {
                    if (!empty($spot['foto'])) {
                        $allPhotos = array_merge($allPhotos, $spot['foto']);
                    }
                }
            }
        }

        LaporanPerjadin::updateOrCreate(
            ['penugasan_id' => $penugasan->id],
            [
                'tanggal_laporan' => $this->tanggalLaporan,
                'isi_kegiatan' => $savedIsiKegiatan,
                'hasil_kegiatan' => $this->reportData,
                'status_laporan' => 'selesai',
                'foto_dokumentasi' => array_merge($penugasan->laporanPerjadin?->foto_dokumentasi ?? [], $allPhotos),
            ]
        );

        $this->hasSaved = true;
        $this->isEditing = false;
        $this->hasDraft = false;

        Notification::make()
            ->title('Laporan Perjalanan Dinas berhasil disimpan secara permanen')
            ->success()
            ->send();
    }

    public string $revisionInstruction = '';
    public bool $isRevising = false;

    /**
     * Request AI to revise the existing report based on specific instructions.
     */
    public function reviseReport(AIService $aiService): void
    {
        $penugasan = $this->getPenugasan();
        if (!$penugasan || empty(trim($this->revisionInstruction))) {
            return;
        }

        $this->isRevising = true;
        $this->errorMessage = null;

        $metaContext = [
            'kegiatan_nama' => $this->kegiatanNama,
            'nomor_surat_tugas' => $this->nomorSuratTugas,
            'moda_transportasi' => $this->modaTransportasi,
            'daerah_dikunjungi' => $this->daerahDikunjungi,
            'pelaksana_nama' => $penugasan->pegawai?->nama ?? $this->selectedPelaksanaNip,
            'periode_str' => $this->periodeStr,
            'preset_steps' => $this->presetSteps,
        ];

        try {
            $this->reportData = $aiService->reviseStructuredReport(
                $this->reportData,
                $this->revisionInstruction,
                $metaContext
            );

            $this->revisionInstruction = '';
            $this->hasSaved = false;

            Notification::make()
                ->title('Tabel berhasil disempurnakan oleh AI sesuai instruksi revisi')
                ->success()
                ->send();

        } catch (Exception $e) {
            $this->errorMessage = "Gagal memproses revisi AI: " . $e->getMessage();
            Notification::make()
                ->title('Gagal Memproses Revisi')
                ->body($e->getMessage())
                ->danger()
                ->send();
        } finally {
            $this->isRevising = false;
        }
    }

    /**
     * Add a blank row to the generated 5-column table.
     */
    public function addTableRow(?string $targetDate = null): void
    {
        $date = $targetDate ?? ($this->harian[0]['tanggal'] ?? $this->minDate ?? now()->toDateString());
        
        $this->reportData['tabel_kegiatan'][] = [
            'tanggal' => $date,
            'waktu' => '13.00 - 14.00',
            'uraian_kegiatan' => 'Melanjutkan pelaksanaan kegiatan lapangan di ...',
            'permasalahan_pemecahan' => '-',
            'keterangan' => '1. Pendataan berjalan lancar sesuai SOP.',
        ];

        $this->isEditing = true;
    }

    /**
     * Remove a specific row from the generated table.
     */
    public function removeTableRow(int $rowIndex): void
    {
        if (isset($this->reportData['tabel_kegiatan'][$rowIndex])) {
            unset($this->reportData['tabel_kegiatan'][$rowIndex]);
            $this->reportData['tabel_kegiatan'] = array_values($this->reportData['tabel_kegiatan']);
        }
    }

    public function toggleEditMode(): void
    {
        $this->isEditing = !$this->isEditing;
    }

    public function editDraft(): void
    {
        $this->isGenerated = false;
        $this->hasSaved = false;
        $this->isEditing = false;
    }
}
