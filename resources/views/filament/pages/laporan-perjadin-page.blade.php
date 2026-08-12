<x-filament-panels::page>
    <div class="space-y-6" x-data="{ isPrinting: false }">
        <!-- Print Styles -->
        <style>
            @media print {
                body {
                    background: white !important;
                    color: black !important;
                    font-family: 'Times New Roman', Times, serif !important;
                    font-size: 12pt !important;
                }
                .fi-sidebar, .fi-topbar, .fi-breadcrumbs, .fi-actions, .no-print {
                    display: none !important;
                }
                .print-only {
                    display: block !important;
                }
                .print-container {
                    padding: 0 !important;
                    margin: 0 !important;
                    border: none !important;
                    box-shadow: none !important;
                    background: transparent !important;
                    width: 100% !important;
                }
                .page-break {
                    page-break-before: always;
                }
            }
        </style>

        <!-- WIZARD STEP 1: FORM INPUT DRAFT LOGS -->
        @if(!$isGenerated && !$isGenerating)
            <div class="no-print bg-white dark:bg-gray-900 shadow-xl rounded-2xl p-6 border border-gray-100 dark:border-gray-800 transition duration-300">
                <div class="flex items-center space-x-3 border-b border-gray-100 dark:border-gray-800 pb-4 mb-6">
                    <div class="p-2 bg-teal-50 dark:bg-teal-950/30 rounded-lg text-teal-600 dark:text-teal-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Pengajuan Laporan Perjalanan Dinas</h2>
                        <p class="text-xs text-gray-500">Pilih Surat Tugas & susun draf kegiatan harian Anda</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Dropdown Surat Tugas -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Pilih Surat Tugas</label>
                        <select wire:model.live="selectedSuratTugasId" class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-teal-500 focus:ring-teal-500">
                            <option value="">-- Pilih Surat Tugas --</option>
                            @foreach($suratTugasOptions as $id => $label)
                                <option value="{{ $id }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Dropdown Pelaksana -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Pilih Pelaksana</label>
                        <select wire:model.live="selectedPelaksanaNip" class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-teal-500 focus:ring-teal-500" {{ !$selectedSuratTugasId ? 'disabled' : '' }}>
                            <option value="">-- Pilih Pelaksana --</option>
                            @foreach($pelaksanaOptions as $nip => $name)
                                <option value="{{ $nip }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Tanggal Melapor -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Tanggal Melapor</label>
                        <input type="date" wire:model="tanggalLaporan" class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-teal-500 focus:ring-teal-500">
                    </div>

                    <!-- Nama Kegiatan (Disabled Auto-fill) -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-400 dark:text-gray-500">Nama Kegiatan</label>
                        <input type="text" wire:model="kegiatanNama" disabled class="w-full rounded-lg border-gray-200 dark:border-gray-800 bg-gray-100 dark:bg-gray-800 text-gray-500 shadow-none cursor-not-allowed">
                    </div>
                </div>

                @if($this->getPenugasan())
                    <!-- DYNAMIC DAILY LOG TIMELINE -->
                    <div class="mt-8 space-y-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-800 pb-2">Log Harian Kegiatan Per Tanggal</h3>
                        
                        <div class="relative border-l-2 border-teal-200 dark:border-teal-800 ml-4 space-y-8">
                            @foreach($harian as $index => $day)
                                <div class="relative pl-6">
                                    <!-- Timeline dot -->
                                    <span class="absolute -left-3 top-1 flex h-6 w-6 items-center justify-center rounded-full bg-teal-100 dark:bg-teal-900 text-teal-600 dark:text-teal-400 ring-4 ring-white dark:ring-gray-900">
                                        {{ $index + 1 }}
                                    </span>

                                    <div class="bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800/80 rounded-xl p-5 shadow-sm space-y-4">
                                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 dark:border-gray-800 pb-3">
                                            <div class="text-sm font-bold text-teal-600 dark:text-teal-400">
                                                {{ \Illuminate\Support\Carbon::parse($day['tanggal'])->translatedFormat('l, d F Y') }}
                                            </div>
                                            <div class="flex items-center space-x-4">
                                                <!-- Waktu -->
                                                <div class="flex items-center space-x-2 text-xs">
                                                    <span class="text-gray-500">Jam:</span>
                                                    <input type="text" wire:model="harian.{{ $index }}.waktu_mulai" placeholder="08:00" class="w-16 text-center py-1 px-2 rounded border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs">
                                                    <span class="text-gray-400">s.d</span>
                                                    <input type="text" wire:model="harian.{{ $index }}.waktu_selesai" placeholder="12:00" class="w-16 text-center py-1 px-2 rounded border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <!-- Uraian Kegiatan (Draft) -->
                                            <div class="space-y-1">
                                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Uraian Laporan Kegiatan (Draf/Bebas)</label>
                                                <textarea wire:model="harian.{{ $index }}.uraian_draft" rows="3" placeholder="Tulis uraian kegiatan bebas, misalnya: pergi ke dinas pertanian ketemu pak eko bahas data panen..." class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-teal-500 focus:ring-teal-500"></textarea>
                                                @error("harian.{$index}.uraian_draft") <span class="text-xs text-red-500 font-semibold">{{ $message }}</span> @enderror
                                            </div>

                                            <!-- Kendala -->
                                            <div class="space-y-1">
                                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Kendala (Jika ada)</label>
                                                <textarea wire:model="harian.{{ $index }}.kendala" rows="3" placeholder="Tulis kendala jika ada, kosongkan jika tidak ada..." class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-teal-500 focus:ring-teal-500"></textarea>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                                            <!-- Titik Koordinat -->
                                            <div class="space-y-1">
                                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Titik Koordinat (Latitude, Longitude)</label>
                                                <div class="flex space-x-2">
                                                    <input type="text" wire:model="harian.{{ $index }}.koordinat" placeholder="Contoh: -0.0264, 109.3425" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                                    <button type="button" @click="navigator.geolocation.getCurrentPosition(pos => { $wire.set('harian.{{ $index }}.koordinat', pos.coords.latitude.toFixed(6) + ', ' + pos.coords.longitude.toFixed(6)) })" class="px-3 bg-teal-50 dark:bg-teal-950/30 text-teal-600 dark:text-teal-400 border border-teal-200 dark:border-teal-800 rounded-lg text-xs hover:bg-teal-100 transition">
                                                        GPS
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Upload Foto & Timestamp Toggle -->
                                            <div class="space-y-1">
                                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Upload Foto Dokumentasi</label>
                                                <div class="flex items-center space-x-4">
                                                    <input type="file" wire:model="photos.{{ $index }}" multiple class="text-xs text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
                                                    <label class="inline-flex items-center space-x-1 cursor-pointer">
                                                        <input type="checkbox" wire:model="harian.{{ $index }}.gunakan_timestamp" class="rounded text-teal-600 focus:ring-teal-500">
                                                        <span class="text-xs text-gray-500">Gunakan Timestamp</span>
                                                    </label>
                                                </div>
                                                
                                                <!-- Saved Photos Previews -->
                                                @if(!empty($day['foto']))
                                                    <div class="flex flex-wrap gap-2 mt-2">
                                                        @foreach($day['foto'] as $savedPhoto)
                                                            <div class="relative group">
                                                                <img src="{{ asset('storage/' . $savedPhoto) }}" class="h-12 w-12 object-cover rounded border border-gray-200">
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif

                                                <!-- Temporary Upload Previews -->
                                                @if(!empty($photos[$index]))
                                                    <div class="flex flex-wrap gap-2 mt-2">
                                                        @foreach($photos[$index] as $tempFile)
                                                            <div class="relative">
                                                                <img src="{{ $tempFile->temporaryUrl() }}" class="h-12 w-12 object-cover rounded border border-teal-300 animate-pulse">
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- ACTION BUTTONS -->
                    <div class="flex justify-end space-x-3 pt-6 border-t border-gray-100 dark:border-gray-800 mt-8">
                        <button type="button" wire:click="generateReport" class="px-5 py-2.5 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white rounded-lg shadow-lg shadow-teal-500/20 font-bold transition flex items-center space-x-2">
                            <span>Proses & Poles dengan AI</span>
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </button>
                    </div>
                @endif
            </div>
        @endif

        <!-- WIZARD STEP 2: LOADING SCREEN -->
        @if($isGenerating)
            <div class="no-print bg-white dark:bg-gray-900 shadow-xl rounded-2xl p-12 border border-gray-100 dark:border-gray-800 flex flex-col items-center justify-center space-y-6">
                <div class="relative flex items-center justify-center">
                    <span class="animate-ping absolute inline-flex h-20 w-20 rounded-full bg-teal-400 opacity-20"></span>
                    <div class="relative p-6 bg-gradient-to-r from-teal-500 to-emerald-500 rounded-full text-white shadow-xl shadow-teal-500/30">
                        <svg class="h-10 w-10 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H18.235" />
                        </svg>
                    </div>
                </div>
                <div class="text-center space-y-2">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">AI Sedang Memoles Laporan Anda</h3>
                    <p class="text-sm text-gray-500 max-w-sm">Menerjemahkan draf harian ke dalam format laporan resmi, merapikan kalimat, serta merekonstruksi solusi kendala secara profesional...</p>
                </div>
            </div>
        @endif

        <!-- WIZARD STEP 3: PREVIEW, EDIT & PRINT MODE -->
        @if($isGenerated && !$isGenerating && $this->getPenugasan())
            <!-- Controls (no-print) -->
            <div class="no-print flex flex-wrap items-center justify-between gap-4 bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                <div class="flex items-center space-x-2">
                    <span class="flex h-3 w-3 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-400"></span>
                    </span>
                    <span class="text-sm font-bold text-gray-900 dark:text-white">Laporan Berhasil Dipoles AI</span>
                </div>
                
                <div class="flex items-center space-x-3">
                    <button type="button" wire:click="editDraft" class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition text-sm font-medium">
                        Edit Draf Asli
                    </button>
                    
                    <button type="button" wire:click="toggleEditMode" class="px-4 py-2 border border-teal-200 dark:border-teal-800 text-teal-600 dark:text-teal-400 rounded-lg hover:bg-teal-50 dark:hover:bg-teal-950/20 transition text-sm font-medium">
                        {{ $isEditing ? 'Selesai Edit' : 'Edit Narasi AI' }}
                    </button>

                    @if($isEditing || !$hasSaved)
                        <button type="button" wire:click="saveReport" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-lg transition text-sm font-bold shadow-lg shadow-teal-500/10">
                            Simpan Laporan
                        </button>
                    @endif

                    <button type="button" @click="window.print()" class="px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white rounded-lg shadow-lg shadow-emerald-500/20 font-bold transition text-sm flex items-center space-x-1">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-3a2 2 0 00-2-2H9a2 2 0 00-2 2v3a2 2 0 002 2zm5-17V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <span>Cetak PDF</span>
                    </button>
                </div>
            </div>

            <!-- PREVIEW SHEET (Styled like official document) -->
            <div class="print-container bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-2xl rounded-2xl p-8 max-w-4xl mx-auto font-sans leading-relaxed text-gray-900 dark:text-gray-100 transition duration-300">
                
                <!-- Document Header BPS -->
                <div class="flex items-center justify-between border-b-2 border-double border-gray-900 dark:border-gray-100 pb-4 mb-6">
                    <div class="text-left">
                        <h1 class="text-lg font-extrabold tracking-wider uppercase">Badan Pusat Statistik</h1>
                        <p class="text-xs uppercase font-semibold">Laporan Hasil Perjalanan Dinas</p>
                    </div>
                    <div class="text-right text-xs">
                        <p class="font-bold">Lampiran Laporan</p>
                        <p>Format Resmi Pemerintah</p>
                    </div>
                </div>

                <div class="space-y-6 text-sm">
                    <!-- General Details Grid -->
                    <div class="grid grid-cols-3 gap-2 border-b border-gray-200 dark:border-gray-800 pb-4">
                        <div class="font-bold">1. Nama Kegiatan</div>
                        <div class="col-span-2">: {{ $kegiatanNama }}</div>

                        <div class="font-bold">2. Pelaksana</div>
                        <div class="col-span-2">: 
                            <span class="font-bold">{{ $this->getPenugasan()->pegawai?->nama ?? $selectedPelaksanaNip }}</span> 
                            (NIP. {{ $selectedPelaksanaNip }})
                        </div>

                        <div class="font-bold">3. Tanggal Pelaksanaan</div>
                        <div class="col-span-2">: 
                            {{ \Illuminate\Support\Carbon::parse($this->getPenugasan()->tgl_mulai_tugas)->translatedFormat('d F Y') }}
                            s.d 
                            {{ \Illuminate\Support\Carbon::parse($this->getPenugasan()->tgl_akhir_tugas)->translatedFormat('d F Y') }}
                        </div>

                        <div class="font-bold">4. Tanggal Laporan</div>
                        <div class="col-span-2">: {{ \Illuminate\Support\Carbon::parse($tanggalLaporan)->translatedFormat('d F Y') }}</div>
                    </div>

                    <!-- Ringkasan Eksekutif -->
                    <div class="space-y-2">
                        <h4 class="text-sm font-bold uppercase border-l-4 border-teal-600 pl-2">I. Ringkasan Eksekutif</h4>
                        @if($isEditing)
                            <textarea wire:model="reportData.ringkasan" rows="4" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800"></textarea>
                        @else
                            <p class="text-gray-700 dark:text-gray-300 text-justify indent-8 leading-relaxed">
                                {{ $reportData['ringkasan'] }}
                            </p>
                        @endif
                    </div>

                    <!-- Rincian Kegiatan Harian -->
                    <div class="space-y-4">
                        <h4 class="text-sm font-bold uppercase border-l-4 border-teal-600 pl-2">II. Rincian Kegiatan Harian</h4>
                        
                        <div class="space-y-6">
                            @foreach($reportData['kegiatan_harian'] ?? [] as $index => $item)
                                <div class="border border-gray-200 dark:border-gray-800 rounded-xl p-4 bg-gray-50/50 dark:bg-gray-800/10 space-y-3">
                                    <div class="flex flex-wrap items-center justify-between border-b border-gray-200 dark:border-gray-800 pb-2">
                                        <span class="font-bold text-teal-600 dark:text-teal-400">
                                            {{ \Illuminate\Support\Carbon::parse($item['tanggal'])->translatedFormat('l, d F Y') }}
                                        </span>
                                        <span class="text-xs text-gray-500">
                                            Waktu: {{ $item['waktu'] }} | Koordinat: {{ $item['koordinat'] ?: '-' }}
                                        </span>
                                    </div>

                                    <!-- Polished Narration Edit -->
                                    <div class="space-y-1">
                                        <span class="block text-xs font-semibold text-gray-500 uppercase">Uraian Kegiatan:</span>
                                        @if($isEditing)
                                            <textarea wire:model="reportData.kegiatan_harian.{{ $index }}.uraian_polished" rows="3" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800"></textarea>
                                        @else
                                            <p class="text-gray-700 dark:text-gray-300 text-justify text-sm">
                                                {{ $item['uraian_polished'] }}
                                            </p>
                                        @endif
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
                                        <!-- Kendala Polished -->
                                        <div class="space-y-1">
                                            <span class="block text-xs font-semibold text-gray-500 uppercase">Kendala:</span>
                                            @if($isEditing)
                                                <textarea wire:model="reportData.kegiatan_harian.{{ $index }}.kendala_polished" rows="2" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800"></textarea>
                                            @else
                                                <p class="text-xs text-gray-600 dark:text-gray-400">
                                                    {{ $item['kendala_polished'] }}
                                                </p>
                                            @endif
                                        </div>

                                        <!-- Solusi Polished -->
                                        <div class="space-y-1">
                                            <span class="block text-xs font-semibold text-gray-500 uppercase">Solusi:</span>
                                            @if($isEditing)
                                                <textarea wire:model="reportData.kegiatan_harian.{{ $index }}.solusi_polished" rows="2" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800"></textarea>
                                            @else
                                                <p class="text-xs text-gray-600 dark:text-gray-400">
                                                    {{ $item['solusi_polished'] }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Render photos if any -->
                                    @if(!empty($harian[$index]['foto']))
                                        <div class="pt-2">
                                            <span class="block text-xs font-semibold text-gray-500 uppercase mb-2">Dokumentasi Foto Lapangan:</span>
                                            <div class="grid grid-cols-3 gap-2">
                                                @foreach($harian[$index]['foto'] as $photo)
                                                    <div class="relative aspect-video overflow-hidden rounded-lg border border-gray-200 shadow-sm">
                                                        <img src="{{ asset('storage/' . $photo) }}" class="w-full h-full object-cover">
                                                        @if(!empty($harian[$index]['gunakan_timestamp']))
                                                            <div class="absolute bottom-1 right-1 bg-black/60 text-white text-[8px] px-1.5 py-0.5 rounded font-mono">
                                                                {{ \Illuminate\Support\Carbon::parse($item['tanggal'])->format('d-m-Y') }} | GPS: {{ $item['koordinat'] }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Kesimpulan -->
                    <div class="space-y-2 pt-2">
                        <h4 class="text-sm font-bold uppercase border-l-4 border-teal-600 pl-2">III. Kesimpulan</h4>
                        @if($isEditing)
                            <textarea wire:model="reportData.kesimpulan" rows="3" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800"></textarea>
                        @else
                            <p class="text-gray-700 dark:text-gray-300 text-justify text-sm indent-8">
                                {{ $reportData['kesimpulan'] }}
                            </p>
                        @endif
                    </div>

                    <!-- Tindak Lanjut -->
                    <div class="space-y-2 pt-2">
                        <h4 class="text-sm font-bold uppercase border-l-4 border-teal-600 pl-2">IV. Rekomendasi / Tindak Lanjut</h4>
                        @if($isEditing)
                            <textarea wire:model="reportData.tindak_lanjut" rows="3" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800"></textarea>
                        @else
                            <p class="text-gray-700 dark:text-gray-300 text-justify text-sm indent-8">
                                {{ $reportData['tindak_lanjut'] }}
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Signatures Layout -->
                <div class="grid grid-cols-2 gap-8 pt-12 mt-12 border-t border-gray-100 dark:border-gray-800 text-sm">
                    <div class="text-center space-y-12">
                        <div>Mengetahui,<br>Kepala Satuan Kerja / Pejabat Penilai</div>
                        <div class="font-bold underline">
                            {{ $this->getPenugasan()->plhSesuai()?->nama ?? '.........................................' }}
                        </div>
                        <div class="text-xs text-gray-500">NIP. {{ $this->getPenugasan()->plh_id ?? '.........................' }}</div>
                    </div>
                    
                    <div class="text-center space-y-12">
                        <div>Melaporkan,<br>Pegawai yang Melaksanakan Tugas</div>
                        <div class="font-bold underline">
                            {{ $this->getPenugasan()->pegawai?->nama ?? '.........................................' }}
                        </div>
                        <div class="text-xs text-gray-500">NIP. {{ $selectedPelaksanaNip }}</div>
                    </div>
                </div>

            </div>
        @endif
    </div>
</x-filament-panels::page>
