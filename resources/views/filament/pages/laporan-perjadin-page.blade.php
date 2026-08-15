<x-filament-panels::page>
    <div class="space-y-6 laporan-perjadin-page" x-data="laporanPerjadinPage(@js($daerahDikunjungi ?: 'Kecamatan di Kab. Mempawah'), @js($isGenerated))">
        <!-- Print & Utility Styles -->
        <style>
            [x-cloak] {
                display: none !important;
            }
            /* F4 Paper Sheet Screen & Print Styling */
            .f4-sheet-preview {
                width: 215mm;
                min-height: 330mm;
                padding: 15mm 20mm;
                margin: 0 auto;
                background-color: #ffffff !important;
                color: #000000 !important;
                font-family: Arial, Helvetica, sans-serif !important;
                box-sizing: border-box;
                border-radius: 4px;
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2), 0 8px 10px -6px rgba(0, 0, 0, 0.15);
                border: 1px solid #d1d5db;
                position: relative;
            }

            @media print {
                @page {
                    size: 215mm 330mm; /* Standard F4 / Folio Indonesia (21.5 cm x 33.0 cm) */
                    margin: 15mm 20mm 15mm 20mm;
                }
                
                /* Reset ALL dark mode styles and backgrounds to pure white */
                html,
                html.dark,
                body,
                body.dark,
                #app,
                .fi-body,
                .fi-layout,
                .fi-main,
                .fi-main-ctn,
                .fi-page,
                .laporan-perjadin-page,
                .f4-preview-wrapper {
                    background: #ffffff !important;
                    background-color: #ffffff !important;
                    color: #000000 !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    width: 100% !important;
                    box-shadow: none !important;
                    border: none !important;
                }

                /* Hide non-printable UI elements */
                .no-print,
                .fi-sidebar,
                .fi-topbar,
                .fi-header,
                .fi-breadcrumbs,
                header,
                nav,
                aside {
                    display: none !important;
                    visibility: hidden !important;
                }

                /* Hide everything in the body by default */
                body * {
                    visibility: hidden !important;
                }

                /* Make the print container and its children visible */
                .print-container,
                .print-container * {
                    visibility: visible !important;
                }

                .print-container,
                .f4-sheet-preview {
                    position: static !important;
                    display: block !important;
                    width: 100% !important;
                    max-width: 100% !important;
                    min-height: auto !important;
                    padding: 0 !important;
                    margin: 0 !important;
                    border: none !important;
                    box-shadow: none !important;
                    background: #ffffff !important;
                    background-color: #ffffff !important;
                    color: #000000 !important;
                    font-family: Arial, Helvetica, sans-serif !important;
                    font-size: 9.5pt !important;
                    border-radius: 0 !important;
                }

                .f4-page-break {
                    page-break-before: always !important;
                    break-before: page !important;
                    margin-top: 0 !important;
                    padding-top: 0 !important;
                }

                table {
                    width: 100% !important;
                    border: 1px solid black !important;
                    border-collapse: collapse !important;
                    table-layout: fixed !important;
                }

                thead {
                    display: table-header-group !important;
                }

                tr {
                    page-break-inside: avoid !important;
                    break-inside: avoid !important;
                }

                th {
                    border: 1px solid black !important;
                    padding: 4px 6px !important;
                    color: black !important;
                    font-size: 8.5pt !important;
                    font-weight: bold !important;
                    background-color: #f3f4f6 !important;
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                    text-align: center !important;
                }

                td {
                    border: 1px solid black !important;
                    padding: 4px 6px !important;
                    color: black !important;
                    font-size: 8.5pt !important;
                    line-height: 1.35 !important;
                }

                .photo-item {
                    page-break-inside: avoid !important;
                    break-inside: avoid !important;
                    max-width: 280px !important;
                    margin: 0 auto !important;
                }

                .photo-item img {
                    max-height: 160px !important;
                    width: auto !important;
                    object-fit: contain !important;
                }

                .signature-block {
                    margin-top: 16px !important;
                    page-break-inside: avoid !important;
                    break-inside: avoid !important;
                    font-size: 8.5pt !important;
                }

                img {
                    max-width: 100% !important;
                    page-break-inside: avoid !important;
                }
            }
            #interactive-map-canvas {
                height: 380px;
                width: 100%;
                border-radius: 0.75rem;
                z-index: 10;
            }
            @keyframes pulseGlow {
                0%, 100% { opacity: 0.4; transform: scale(0.98); }
                50% { opacity: 0.9; transform: scale(1.02); }
            }
            .animate-pulse-glow {
                animation: pulseGlow 2.5s infinite ease-in-out;
            }
        </style>

        <!-- WIZARD STEP 1: FORM INPUT SMART SEQUENTIAL STOPS -->
        @if(!$isGenerated && !$isGenerating)
            <div class="no-print bg-white dark:bg-gray-900 shadow-xl rounded-2xl p-4 md:p-6 border border-gray-100 dark:border-gray-800 transition duration-300">
                <div class="flex items-center space-x-3 border-b border-gray-100 dark:border-gray-800 pb-4 mb-6">
                    <div class="p-2 bg-teal-50 dark:bg-teal-950/30 rounded-lg text-teal-600 dark:text-teal-400 shrink-0">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg md:text-xl font-bold text-gray-900 dark:text-white">Laporan Perjalanan Dinas</h2>
                        <p class="text-xs text-gray-500">Susun titik singgah kegiatan lapangan, AI akan mengalkulasi pembagian jam secara otomatis</p>
                    </div>
                </div>

                <!-- DRAFT NOTIFICATION BANNER -->
                @if($hasDraft)
                    <div class="mb-6 p-3.5 md:p-4 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900/50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 shadow-sm">
                        <div class="flex items-center space-x-3">
                            <div class="p-2 bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300 rounded-lg shrink-0">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-xs md:text-sm font-bold text-amber-900 dark:text-amber-200">Draf Kegiatan Lapangan Tersimpan</h4>
                                <p class="text-[11px] text-amber-700 dark:text-amber-400">Terakhir disimpan: <b>{{ $draftSavedAt }}</b> ({{ count($harian) }} hari log aktif)</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-end">
                            <button type="button" 
                                wire:click="deleteDraft" 
                                wire:confirm="Apakah Anda yakin ingin menghapus seluruh draf sementara ini dan mengulang dari awal?"
                                class="px-3 py-1.5 text-xs text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-950/40 rounded-lg border border-red-200 dark:border-red-800 transition font-medium flex items-center space-x-1">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                <span>Hapus Draf</span>
                            </button>
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 mb-6">
                    <div class="space-y-1.5">
                        <label class="block text-xs md:text-sm font-semibold text-gray-700 dark:text-gray-300">Pilih Surat Tugas</label>
                        <x-filament::input.wrapper>
                            <x-filament::input.select wire:model.live="selectedSuratTugasId" class="truncate pr-8 text-xs md:text-sm">
                                <option value="">-- Pilih Surat Tugas --</option>
                                @foreach($suratTugasOptions as $id => $label)
                                    <option value="{{ $id }}" title="{{ $label }}">{{ $label }}</option>
                                @endforeach
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs md:text-sm font-semibold text-gray-700 dark:text-gray-300">Pilih Pelaksana</label>
                        <x-filament::input.wrapper :disabled="!$selectedSuratTugasId">
                            <x-filament::input.select wire:model.live="selectedPelaksanaNip" :disabled="!$selectedSuratTugasId" class="truncate pr-8 text-xs md:text-sm">
                                <option value="">-- Pilih Pelaksana --</option>
                                @foreach($pelaksanaOptions as $nip => $name)
                                    <option value="{{ $nip }}" title="{{ $name }}">{{ $name }}</option>
                                @endforeach
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs md:text-sm font-semibold text-gray-700 dark:text-gray-300">Tanggal Melapor</label>
                        <x-filament::input.wrapper>
                            <x-filament::input type="date" wire:model="tanggalLaporan" />
                        </x-filament::input.wrapper>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs md:text-sm font-semibold text-gray-700 dark:text-gray-300">Detail Penugasan (Otomatis dari Sistem)</label>
                        <div class="bg-gray-100 dark:bg-gray-800/80 p-3 rounded-xl text-xs space-y-1 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700">
                            <div>📌 <b>Dasar Pelaksanaan:</b> {{ $nomorSuratTugas ?: '-' }}</div>
                            <div>🚗 <b>Moda Transportasi:</b> {{ $modaTransportasi ?: '-' }}</div>
                            <div>📍 <b>Daerah Tujuan:</b> {{ $daerahDikunjungi ?: '-' }}</div>
                        </div>
                    </div>
                </div>

                @if($this->getPenugasan())
                    <!-- MODE LAPORAN SWITCHER -->
                    <div class="mt-6 md:mt-8 pt-4 border-t border-gray-100 dark:border-gray-800">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500">Pilih Format / Mode Laporan:</label>
                                <p class="text-xs text-gray-400">Periode: <span class="font-semibold text-teal-600 dark:text-teal-400">{{ $periodeStr }}</span></p>
                            </div>
                            
                            <div class="inline-flex rounded-xl p-1 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 w-full sm:w-auto">
                                <button type="button" 
                                    wire:click="setModeLaporan('harian')" 
                                    class="flex-1 sm:flex-initial py-2 px-3 rounded-lg text-xs font-semibold transition flex items-center justify-center space-x-1.5 {{ $modeLaporan === 'harian' ? 'bg-white dark:bg-gray-900 text-teal-600 dark:text-teal-400 shadow-sm' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white' }}">
                                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span>Mode 1: Harian (Matriks 5 Kolom)</span>
                                    <span class="text-[9px] bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-300 px-1 py-0.2 rounded font-mono hidden sm:inline">Default</span>
                                </button>
                                
                                <button type="button" 
                                    wire:click="setModeLaporan('periodik')" 
                                    class="flex-1 sm:flex-initial py-2 px-3 rounded-lg text-xs font-semibold transition flex items-center justify-center space-x-1.5 {{ $modeLaporan === 'periodik' ? 'bg-white dark:bg-gray-900 text-teal-600 dark:text-teal-400 shadow-sm' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white' }}">
                                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span>Mode 2: Rekapitulasi Periode</span>
                                </button>
                            </div>
                        </div>

                        <!-- MODE 1: SMART SEQUENTIAL STOPS PER DATE -->
                        @if($modeLaporan === 'harian')
                            <div class="space-y-6">
                                <div class="flex flex-wrap items-center justify-between gap-3 bg-teal-50/60 dark:bg-teal-950/20 border border-teal-100 dark:border-teal-900/40 rounded-xl p-3.5">
                                    <div class="flex items-center space-x-2">
                                        <span class="h-2 w-2 rounded-full bg-teal-500 shrink-0"></span>
                                        <span class="text-xs text-teal-800 dark:text-teal-200">
                                            Rute <b>{{ $daerahDikunjungi }}</b> aktif. Tambahkan titik singgah berurutan, AI akan membagi jam kegiatan dan waktu sholat secara proporsional.
                                        </span>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <!-- Stempel / Watermark Toggle Option -->
                                        <label class="inline-flex items-center gap-1.5 cursor-pointer bg-white dark:bg-gray-800 px-2.5 py-1 rounded-lg border border-teal-200 dark:border-teal-800 text-xs font-semibold text-teal-900 dark:text-teal-200 shadow-sm select-none">
                                            <input type="checkbox" x-model="applyWatermark" class="rounded border-teal-400 text-teal-600 focus:ring-teal-500 h-3.5 w-3.5">
                                            <span>⚡ Stempel Foto (Waktu, GPS & Alamat)</span>
                                        </label>

                                        <button type="button" wire:click="loadWorkdaysOnly" class="text-xs px-2.5 py-1 rounded-lg border border-teal-300 dark:border-teal-700 bg-white dark:bg-gray-800 text-teal-700 dark:text-teal-300 hover:bg-teal-50 transition font-medium">
                                            Muat Hari Kerja
                                        </button>
                                        <button type="button" wire:click="addHarian" class="text-xs px-3 py-1 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-bold transition flex items-center space-x-1 shadow-sm">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                            <span>Tambah Hari</span>
                                        </button>
                                    </div>
                                </div>

                                <div class="relative border-l-2 border-teal-200 dark:border-teal-800 ml-2 md:ml-4 space-y-8">
                                    @foreach($harian as $index => $day)
                                        @php
                                            $totalDayPhotos = 0;
                                            foreach($day['titik_kegiatan'] ?? [] as $sp) {
                                                $totalDayPhotos += count($sp['foto'] ?? []);
                                            }
                                        @endphp
                                        <div class="relative pl-4 md:pl-6" wire:key="day-{{ $index }}">
                                            <span class="absolute -left-3 top-2 flex h-6 w-6 items-center justify-center rounded-full bg-teal-100 dark:bg-teal-900 text-teal-600 dark:text-teal-400 ring-4 ring-white dark:ring-gray-900 font-bold text-xs">
                                                {{ $index + 1 }}
                                            </span>

                                            <div class="bg-gray-50 dark:bg-gray-900/50 border {{ $totalDayPhotos > 0 ? 'border-gray-200/80 dark:border-gray-800' : 'border-amber-300 dark:border-amber-800/80 ring-1 ring-amber-200 dark:ring-amber-900/40' }} rounded-2xl p-4 md:p-5 shadow-sm space-y-4">
                                                
                                                <!-- Header Tanggal -->
                                                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 dark:border-gray-800 pb-3">
                                                    <div class="flex items-center space-x-2 md:space-x-3">
                                                        <div class="space-y-0.5">
                                                            <label class="block text-[10px] font-bold uppercase text-gray-500">Tanggal Pelaksanaan:</label>
                                                            <input type="date" 
                                                                wire:model.live="harian.{{ $index }}.tanggal" 
                                                                min="{{ $minDate }}" 
                                                                max="{{ $maxDate }}" 
                                                                class="py-1 px-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-xs font-semibold shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                                        </div>
                                                        <span class="text-xs font-bold text-teal-600 dark:text-teal-400 pt-3.5">
                                                            {{ \Illuminate\Support\Carbon::parse($day['tanggal'])->translatedFormat('l, d F Y') }}
                                                        </span>
                                                    </div>

                                                    <div class="flex items-center space-x-2 md:space-x-3">
                                                        <div class="flex items-center space-x-1 text-xs">
                                                            <span class="text-gray-500 text-[11px]">Jam Dinas:</span>
                                                            <input type="text" wire:model="harian.{{ $index }}.waktu_mulai" placeholder="08.00" class="w-14 text-center py-1 px-1 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs font-semibold">
                                                            <span class="text-gray-400">-</span>
                                                            <input type="text" wire:model="harian.{{ $index }}.waktu_selesai" placeholder="15.15" class="w-14 text-center py-1 px-1 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs font-semibold">
                                                        </div>

                                                        @if(count($harian) > 1)
                                                            <button type="button" wire:click="removeHarian({{ $index }})" class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-950/20 rounded-lg transition" title="Hapus tanggal ini">
                                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                </svg>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>

                                                <!-- REPEATER TITIK SINGGAH -->
                                                <div class="space-y-4">
                                                    <div class="flex items-center justify-between">
                                                        <span class="text-xs font-bold text-gray-700 dark:text-gray-300 flex items-center space-x-1.5">
                                                            <span>📍 Urutan Titik Singgah & Aktivitas Lapangan</span>
                                                            <span class="text-[10px] text-gray-400 font-normal">(Foto & Koordinat menempel pada titik masing-masing)</span>
                                                        </span>
                                                    </div>

                                                    @foreach($day['titik_kegiatan'] ?? [] as $sIdx => $spot)
                                                        <div class="bg-white dark:bg-gray-800/90 rounded-xl p-3.5 border border-gray-200 dark:border-gray-700 shadow-sm space-y-3" wire:key="spot-{{ $index }}-{{ $sIdx }}">
                                                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-gray-100 dark:border-gray-700/60 pb-2.5">
                                                                <div class="flex items-center space-x-2 flex-1">
                                                                    <span class="h-5 w-5 rounded-full bg-teal-50 dark:bg-teal-950 text-teal-600 dark:text-teal-400 font-bold text-[10px] flex items-center justify-center border border-teal-200 dark:border-teal-800 shrink-0">
                                                                        {{ $sIdx + 1 }}
                                                                    </span>
                                                                    <input type="text" 
                                                                        wire:model="harian.{{ $index }}.titik_kegiatan.{{ $sIdx }}.nama_titik" 
                                                                        placeholder="Contoh: Kantor Camat Mempawah Timur / Desa Pasir Panjang RT 02" 
                                                                        class="w-full text-xs font-bold rounded-lg border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900 text-gray-900 dark:text-white py-1 px-2">
                                                                </div>

                                                                @if(count($day['titik_kegiatan']) > 1)
                                                                    <div class="flex justify-end">
                                                                        <button type="button" wire:click="removeTitikKegiatan({{ $index }}, {{ $sIdx }})" class="text-xs text-red-500 hover:text-red-700 transition flex items-center space-x-1">
                                                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                            </svg>
                                                                            <span class="text-[11px]">Hapus Titik</span>
                                                                        </button>
                                                                    </div>
                                                                @endif
                                                            </div>

                                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                                <!-- Koordinat Spot -->
                                                                <div class="space-y-1">
                                                                    <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400">Titik Koordinat Lokasi Ini</label>
                                                                    <div class="flex space-x-1.5">
                                                                        <input type="text" 
                                                                            wire:model="harian.{{ $index }}.titik_kegiatan.{{ $sIdx }}.koordinat" 
                                                                            placeholder="Latitude, Longitude" 
                                                                            class="w-full text-xs font-mono rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white py-1 px-2">
                                                                        
                                                                        <button type="button" 
                                                                            @click="openMap({{ $index }}, {{ $sIdx }}, false)" 
                                                                            class="px-2.5 py-1 bg-teal-600 hover:bg-teal-700 text-white rounded-lg text-xs font-semibold transition flex items-center space-x-1 shrink-0 shadow-sm" 
                                                                            title="Geser titik di peta">
                                                                            <span>Peta</span>
                                                                        </button>

                                                                        <button type="button" 
                                                                            @click="navigator.geolocation.getCurrentPosition(pos => { $wire.set('harian.{{ $index }}.titik_kegiatan.{{ $sIdx }}.koordinat', pos.coords.latitude.toFixed(6) + ', ' + pos.coords.longitude.toFixed(6)) }, err => { alert('Gagal mendeteksi GPS.') }, { enableHighAccuracy: true })" 
                                                                            class="px-2 py-1 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg text-xs font-medium transition shrink-0"
                                                                            title="Ambil GPS perangkat">
                                                                            GPS
                                                                        </button>
                                                                    </div>
                                                                </div>

                                                                <!-- Upload Foto Spot -->
                                                                <div class="space-y-1">
                                                                    <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400">Foto Dokumentasi di Titik Ini</label>
                                                                    <div class="flex flex-wrap items-center gap-1.5">
                                                                        <label class="cursor-pointer px-2.5 py-1 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 text-white rounded-lg text-xs font-bold shadow-sm flex items-center space-x-1 transition active:scale-95">
                                                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                            </svg>
                                                                            <span>Kamera HP</span>
                                                                            <input type="file" accept="image/*" capture="environment" class="hidden" @change="processAndWatermarkPhoto($event, {{ $index }}, {{ $sIdx }}, false)">
                                                                        </label>

                                                                        <label class="cursor-pointer px-2.5 py-1 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-600 rounded-lg text-xs font-medium flex items-center space-x-1 transition">
                                                                            <span>Galeri</span>
                                                                            <input type="file" accept="image/*" multiple class="hidden" @change="processAndWatermarkPhoto($event, {{ $index }}, {{ $sIdx }}, false)">
                                                                        </label>

                                                                        @if(!empty($spot['foto']))
                                                                            <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold">
                                                                                ✓ {{ count($spot['foto']) }} foto
                                                                            </span>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-1">
                                                                <div class="space-y-1">
                                                                    <label class="block text-[11px] font-semibold text-gray-700 dark:text-gray-300">Catatan/Aktivitas di Titik Ini</label>
                                                                    <textarea wire:model="harian.{{ $index }}.titik_kegiatan.{{ $sIdx }}.uraian" rows="2" placeholder="Contoh: Mengawasi ubinan padi sawah Pak Ahmad, hasil ubinan 4.2 kg..." class="w-full text-xs rounded-xl border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white"></textarea>
                                                                </div>

                                                                <div class="space-y-1">
                                                                    <label class="block text-[11px] font-semibold text-gray-700 dark:text-gray-300">Kendala & Solusi (Jika ada)</label>
                                                                    <textarea wire:model="harian.{{ $index }}.titik_kegiatan.{{ $sIdx }}.kendala" rows="2" placeholder="Contoh: Responden tidak tahu luas baku / dilakukan pendekatan taksiran bibit..." class="w-full text-xs rounded-xl border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white"></textarea>
                                                                </div>
                                                            </div>

                                                            @if(!empty($spot['foto']))
                                                                <div class="flex flex-wrap gap-2.5 pt-1.5 border-t border-gray-100 dark:border-gray-700/40">
                                                                    @foreach($spot['foto'] as $pIdx => $photoPath)
                                                                        <div class="relative group">
                                                                            <img src="{{ asset('storage/' . $photoPath) }}" class="h-14 w-14 object-cover rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
                                                                            <button type="button" 
                                                                                wire:click="removeSpotPhoto({{ $index }}, {{ $sIdx }}, {{ $pIdx }})" 
                                                                                class="absolute -top-1.5 -right-1.5 bg-red-500 hover:bg-red-600 text-white rounded-full h-4 w-4 flex items-center justify-center text-[11px] shadow transition active:scale-95" 
                                                                                title="Hapus foto ini">
                                                                                &times;
                                                                            </button>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>

                                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pt-2 border-t border-gray-100 dark:border-gray-800">
                                                    <button type="button" wire:click="addTitikKegiatan({{ $index }})" class="text-xs px-3 py-1.5 rounded-lg border border-dashed border-teal-400 dark:border-teal-700 text-teal-700 dark:text-teal-300 hover:bg-teal-50 dark:hover:bg-teal-950/20 font-bold transition flex items-center space-x-1.5 w-fit">
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                        </svg>
                                                        <span>+ Tambah Titik Kunjungan / Sampel Berikutnya</span>
                                                    </button>

                                                    <span class="text-[11px] text-gray-400">
                                                        🕌 Jeda Sholat & Ishoma otomatis dikelola sistem
                                                    </span>
                                                </div>

                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="flex justify-start ml-2 md:ml-4 pt-2">
                                    <button type="button" wire:click="addHarian" class="px-4 py-2.5 border-2 border-dashed border-teal-300 dark:border-teal-700 hover:border-teal-500 text-teal-700 dark:text-teal-300 rounded-xl text-xs font-bold transition flex items-center space-x-2 bg-teal-50/30">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        <span>+ Tambah Tanggal Kegiatan Lainnya</span>
                                    </button>
                                </div>
                            </div>
                        @endif

                        <!-- MODE 2: REKAPITULASI PERIODE -->
                        @if($modeLaporan === 'periodik')
                            <div class="bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-800 rounded-2xl p-4 md:p-6 space-y-6">
                                <div class="bg-teal-50/70 dark:bg-teal-950/20 border border-teal-100 dark:border-teal-900/50 rounded-xl p-3 text-xs text-teal-800 dark:text-teal-200">
                                    💡 <b>Mode Rekapitulasi Periode:</b> Cocok untuk surat tugas rentang panjang (1-3 bulan). Anda cukup mengisi satu draf ringkasan capaian pelaksanaan tugas secara keseluruhan.
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="space-y-1">
                                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Cakupan Wilayah / Tempat Pelaksanaan</label>
                                        <input type="text" wire:model="periodikData.cakupan_wilayah" placeholder="Contoh: Kecamatan Mempawah Timur" class="w-full text-xs rounded-xl border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                    </div>

                                    <div class="space-y-1">
                                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Titik Koordinat Utama / Posko</label>
                                        <div class="flex space-x-2">
                                            <input type="text" wire:model="periodikData.titik_kegiatan.0.koordinat" placeholder="Contoh: -0.0264, 109.3425" class="w-full text-xs rounded-xl border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                            
                                            <button type="button" 
                                                @click="openMap(0, 0, true)" 
                                                class="px-2.5 py-1.5 bg-teal-600 hover:bg-teal-700 text-white rounded-lg text-xs font-semibold transition flex items-center space-x-1 shadow-sm shrink-0" 
                                                title="Buka peta dan geser pin lokasi">
                                                <span>Peta</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-1">
                                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Draf Uraian Pelaksanaan & Capaian Tugas (Bebas)</label>
                                    <textarea wire:model="periodikData.uraian_draft" rows="5" placeholder="Tuliskan draf kegiatan secara bebas..." class="w-full text-sm rounded-xl border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-teal-500 focus:ring-teal-500"></textarea>
                                    @error("periodikData.uraian_draft") <span class="text-xs text-red-500 font-semibold">{{ $message }}</span> @enderror
                                </div>

                                <div class="space-y-1">
                                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Kendala Lapangan yang Dihadapi (Jika ada)</label>
                                    <textarea wire:model="periodikData.kendala" rows="3" placeholder="Tuliskan kendala jika ada..." class="w-full text-sm rounded-xl border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-teal-500 focus:ring-teal-500"></textarea>
                                </div>

                                <div class="space-y-2">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Upload Foto Dokumentasi Lapangan (Wajib Min. 1 Foto)</label>
                                        <label class="inline-flex items-center gap-1.5 cursor-pointer bg-teal-50 dark:bg-teal-950/40 px-2.5 py-1 rounded-lg border border-teal-200 dark:border-teal-800 text-xs font-semibold text-teal-900 dark:text-teal-200 select-none">
                                            <input type="checkbox" x-model="applyWatermark" class="rounded border-teal-400 text-teal-600 focus:ring-teal-500 h-3.5 w-3.5">
                                            <span>⚡ Stempel Otomatis (Waktu, GPS & Alamat)</span>
                                        </label>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <label class="cursor-pointer px-3.5 py-2 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-teal-500/20 flex items-center space-x-1.5 transition">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                            </svg>
                                            <span>Buka Kamera HP</span>
                                            <input type="file" accept="image/*" capture="environment" class="hidden" @change="processAndWatermarkPhoto($event, 0, 0, true)">
                                        </label>

                                        <label class="cursor-pointer px-3.5 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-700 rounded-xl text-xs font-semibold flex items-center space-x-1.5 transition">
                                            <span>Pilih Galeri</span>
                                            <input type="file" accept="image/*" multiple class="hidden" @change="processAndWatermarkPhoto($event, 0, 0, true)">
                                        </label>
                                    </div>
                                    
                                    @if(!empty($periodikData['foto']))
                                        <div class="flex flex-wrap gap-2.5 mt-2">
                                            @foreach($periodikData['foto'] as $pIdx => $savedPhoto)
                                                <div class="relative group">
                                                    <img src="{{ asset('storage/' . $savedPhoto) }}" class="h-16 w-16 object-cover rounded-xl border border-gray-200 shadow-sm">
                                                    <button type="button" 
                                                        wire:click="removeSpotPhoto(0, 0, {{ $pIdx }}, true)" 
                                                        class="absolute -top-1.5 -right-1.5 bg-red-500 hover:bg-red-600 text-white rounded-full h-4 w-4 flex items-center justify-center text-[11px] shadow transition active:scale-95" 
                                                        title="Hapus foto ini">
                                                        &times;
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                    </div>

                    <!-- ACTION BUTTONS -->
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 pt-6 border-t border-gray-100 dark:border-gray-800 mt-8">
                        <div>
                            @if($hasDraft)
                                <button type="button" 
                                    wire:click="deleteDraft" 
                                    wire:confirm="Apakah Anda yakin ingin menghapus draf sementara ini dan mengulang formulir dari awal?"
                                    class="w-full sm:w-auto px-4 py-2.5 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/20 rounded-xl border border-red-200 dark:border-red-800 transition text-xs font-semibold flex items-center justify-center space-x-1.5">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    <span>Hapus Draf Sementara</span>
                                </button>
                            @endif
                        </div>

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 md:gap-3">
                            <button type="button" 
                                wire:click="saveDraftOnly" 
                                class="inline-flex flex-row items-center justify-center whitespace-nowrap min-h-[42px] px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-xl font-bold transition text-xs space-x-1.5 border border-gray-300 dark:border-gray-700 shadow-sm shrink-0">
                                <svg class="h-4 w-4 text-gray-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                                </svg>
                                <span>Simpan Draf Lapangan</span>
                            </button>

                            <!-- GENERATE BUTTON DENGAN INSTANT FEEDBACK & ANTI DOUBLE CLICK -->
                            <button type="button" 
                                wire:click="generateReport" 
                                wire:loading.attr="disabled"
                                wire:target="generateReport"
                                @click="startLoadingProgress()"
                                class="inline-flex items-center justify-center gap-2 whitespace-nowrap min-h-[42px] px-5 py-2 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white rounded-xl shadow-lg shadow-teal-500/20 font-bold transition text-xs disabled:opacity-75 disabled:cursor-not-allowed active:scale-95 shrink-0">
                                
                                <span wire:loading.remove wire:target="generateReport" class="inline-flex items-center gap-2">
                                    <span>Proses & Susun Tabel LPD Resmi</span>
                                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </span>

                                <span wire:loading.inline-flex wire:target="generateReport" class="items-center gap-2">
                                    <svg class="h-4 w-4 animate-spin text-white shrink-0" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                    <span class="animate-pulse whitespace-nowrap">AI Sedang Menyusun Laporan...</span>
                                </span>
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <!-- MODAL INTERAKTIF MAP PICKER -->
        <div x-show="showMapModal" 
            x-cloak
            style="display: none;"
            class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-3 md:p-4"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-95">
            
            <div class="bg-white dark:bg-gray-900 rounded-2xl max-w-2xl w-full p-4 md:p-6 shadow-2xl border border-gray-200 dark:border-gray-800 space-y-4" @click.outside="showMapModal = false">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-3">
                    <div class="flex items-center space-x-2">
                        <span class="p-1.5 bg-teal-100 dark:bg-teal-900 text-teal-600 rounded-lg shrink-0">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </span>
                        <div>
                            <h3 class="text-sm md:text-base font-bold text-gray-900 dark:text-white">Pilih Titik Lokasi Kegiatan di Peta</h3>
                            <p class="text-xs text-gray-500">Geser pin merah atau klik pada peta untuk menentukan posisi presisi.</p>
                        </div>
                    </div>
                    <button type="button" @click="showMapModal = false" class="text-gray-400 hover:text-gray-600 text-lg font-bold">&times;</button>
                </div>

                <div class="relative rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700">
                    <div id="interactive-map-canvas"></div>
                    
                    <div class="absolute top-3 right-3 z-[400]">
                        <button type="button" 
                            @click="useDeviceLocation()" 
                            class="bg-white/90 dark:bg-gray-900/90 hover:bg-white dark:hover:bg-gray-900 text-teal-700 dark:text-teal-300 px-3 py-1.5 rounded-lg text-xs font-semibold shadow-md border border-gray-200 dark:border-gray-700 flex items-center space-x-1.5 backdrop-blur-sm transition">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span x-text="isLocating ? 'Mendeteksi...' : 'Pusatkan ke GPS Saya'"></span>
                        </button>
                    </div>
                </div>

                <div class="bg-gray-50 dark:bg-gray-800/60 p-3 rounded-xl border border-gray-200 dark:border-gray-700 flex flex-wrap items-center justify-between gap-2 text-xs">
                    <div class="text-gray-600 dark:text-gray-400">
                        Titik Terpilih: <span class="font-mono font-bold text-teal-600 dark:text-teal-400" x-text="mapLat.toFixed(6) + ', ' + mapLng.toFixed(6)"></span>
                    </div>
                    <div class="text-[11px] text-gray-500">
                        📌 Mode geser pin aktif
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" @click="showMapModal = false" class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-xl text-xs font-medium hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                        Batal
                    </button>
                    <button type="button" @click="confirmLocation()" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-bold shadow-md shadow-teal-500/20 transition flex items-center space-x-1.5">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Gunakan Titik Ini</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- WIZARD STEP 2: 2026 TREND AI GENERATIVE LOADING SCREEN -->
        @if($isGenerating || $isRevising)
            <div class="no-print bg-white/90 dark:bg-gray-900/90 backdrop-blur-xl shadow-2xl rounded-3xl p-8 md:p-14 border border-teal-100 dark:border-teal-900/50 flex flex-col items-center justify-center space-y-8 transition-all">
                
                <!-- Glowing Liquid Glass Orb -->
                <div class="relative flex items-center justify-center">
                    <div class="absolute -inset-4 bg-gradient-to-r from-teal-500 via-emerald-500 to-cyan-500 rounded-full blur-xl opacity-40 animate-pulse-glow"></div>
                    <div class="relative p-7 bg-gradient-to-tr from-teal-600 via-teal-500 to-emerald-500 rounded-full text-white shadow-2xl shadow-teal-500/50 border border-white/20">
                        <svg class="h-12 w-12 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H18.235" />
                        </svg>
                    </div>
                </div>

                <!-- Dynamic Processing Status -->
                <div class="text-center space-y-3 max-w-lg">
                    <h3 class="text-xl md:text-2xl font-black text-gray-900 dark:text-white tracking-tight">
                        {{ $isRevising ? 'AI Sedang Menyempurnakan Laporan' : 'AI Sedang Mengalkulasi & Menyusun Tabel BPS' }}
                    </h3>
                    
                    <div class="space-y-2 text-xs md:text-sm text-gray-600 dark:text-gray-300">
                        <div class="flex items-center justify-center space-x-2 text-teal-700 dark:text-teal-300 font-semibold" x-show="loadingStep === 1">
                            <span class="animate-bounce">📍</span>
                            <span>Menganalisis rute, koordinat GPS & urutan titik singgah...</span>
                        </div>
                        <div class="flex items-center justify-center space-x-2 text-teal-700 dark:text-teal-300 font-semibold" x-show="loadingStep === 2">
                            <span class="animate-bounce">🕌</span>
                            <span>Membagi time block sesi lapangan & menyelaraskan jadwal ibadah sholat...</span>
                        </div>
                        <div class="flex items-center justify-center space-x-2 text-teal-700 dark:text-teal-300 font-semibold" x-show="loadingStep >= 3">
                            <span class="animate-bounce">✍️</span>
                            <span>Memformulasikan narasi matriks 5 kolom resmi BPS...</span>
                        </div>
                    </div>
                </div>

                <!-- Shimmer Skeleton Bar -->
                <div class="w-full max-w-md bg-gray-100 dark:bg-gray-800 rounded-full h-2 overflow-hidden shadow-inner">
                    <div class="bg-gradient-to-r from-teal-500 via-emerald-400 to-cyan-500 h-full w-full animate-pulse"></div>
                </div>
            </div>
        @endif

        <!-- WIZARD STEP 3: PREVIEW, EDIT & PRINT MODE DENGAN STREAMING TYPEWRITER REVEAL -->
        @if($isGenerated && !$isGenerating && !$isRevising && $this->getPenugasan())
            
            <!-- 1. AI REVISION PROMPT BAR -->
            <div class="no-print bg-gradient-to-r from-teal-50 to-emerald-50 dark:from-teal-950/40 dark:to-emerald-950/40 border border-teal-200 dark:border-teal-800/80 rounded-2xl p-4 shadow-sm space-y-2">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center space-x-2">
                        <span class="p-1 bg-teal-600 text-white rounded-md">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </span>
                        <span class="text-xs font-bold text-teal-900 dark:text-teal-200">
                            ✨ Minta AI Merevisi Tabel Ini
                        </span>
                    </div>
                    <span class="text-[10px] text-teal-700 dark:text-teal-400">
                        Contoh: <i>"Perpendek uraian di tanggal 2"</i>, <i>"Ubah jam pulang ke 16.00"</i>, <i>"Tambahkan kendala sinyal"</i>
                    </span>
                </div>

                <div class="flex flex-col sm:flex-row gap-2">
                    <input type="text" 
                        wire:model="revisionInstruction" 
                        wire:keydown.enter="reviseReport"
                        placeholder="Ketik instruksi perbaikan yang Anda inginkan..." 
                        class="flex-1 text-xs rounded-xl border-teal-300 dark:border-teal-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm focus:border-teal-500 focus:ring-teal-500 py-2.5 px-3">
                    
                    <button type="button" 
                        wire:click="reviseReport" 
                        wire:loading.attr="disabled"
                        wire:target="reviseReport"
                        class="px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-bold shadow-md shadow-teal-500/20 transition inline-flex items-center justify-center gap-1.5 shrink-0 disabled:opacity-75">
                        
                        <span wire:loading.remove wire:target="reviseReport" class="inline-flex items-center gap-1.5">
                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            <span>Revisi dengan AI</span>
                        </span>

                        <span wire:loading.inline-flex wire:target="reviseReport" class="items-center gap-1.5">
                            <svg class="h-4 w-4 animate-spin text-white shrink-0" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span class="animate-pulse">Merevisi...</span>
                        </span>
                    </button>
                </div>
            </div>

            <!-- 2. CONTROLS BAR -->
            <div class="no-print flex flex-wrap items-center justify-between gap-4 bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                <div class="flex items-center space-x-2">
                    <span class="flex h-3 w-3 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-400"></span>
                    </span>
                    <span class="text-sm font-bold text-gray-900 dark:text-white">Tabel Laporan Perjalanan Dinas Siap Dicetak</span>
                    <span class="text-[10px] bg-teal-100 dark:bg-teal-900/60 text-teal-700 dark:text-teal-300 px-2 py-0.5 rounded-full font-bold">✨ AI Generated</span>
                </div>
                
                <div class="flex flex-wrap items-center gap-2 md:gap-3">
                    <button type="button" wire:click="editDraft" class="px-3.5 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition text-xs font-medium">
                        ← Edit Draf Asli
                    </button>
                    
                    <button type="button" wire:click="toggleEditMode" class="px-3.5 py-2 border {{ $isEditing ? 'border-teal-600 bg-teal-50 text-teal-700 font-bold' : 'border-teal-200 dark:border-teal-800 text-teal-600 dark:text-teal-400' }} rounded-xl hover:bg-teal-50 dark:hover:bg-teal-950/20 transition text-xs font-medium flex items-center space-x-1">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        <span>{{ $isEditing ? 'Selesai Edit Manual' : 'Edit Tabel Manual' }}</span>
                    </button>

                    @if($isEditing || !$hasSaved)
                        <button type="button" wire:click="saveReport" class="px-3.5 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl transition text-xs font-bold shadow-lg shadow-teal-500/10">
                            Simpan Permanen
                        </button>
                    @endif

                    <button type="button" @click="window.print()" class="px-4 py-2 !bg-teal-600 hover:!bg-teal-700 !text-white rounded-xl shadow-md shadow-teal-600/30 font-bold transition text-xs flex items-center space-x-1.5 shrink-0 active:scale-95">
                        <svg class="h-4 w-4 !text-white shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-3a2 2 0 00-2-2H9a2 2 0 00-2 2v3a2 2 0 002 2zm5-17V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <span class="!text-white font-bold">Cetak PDF LPD</span>
                    </button>
                </div>
            </div>

            <!-- PREVIEW SHEET WRAPPER DENGAN TAMPILAN KERTAS F4 RESMI (21.5 cm x 33.0 cm) -->
            @php
                $allPhotos = [];
                if(!empty($harian)) {
                    foreach($harian as $h) {
                        $tglLabel = \Illuminate\Support\Carbon::parse($h['tanggal'] ?? now())->translatedFormat('d M Y');
                        foreach($h['titik_kegiatan'] ?? [] as $sp) {
                            foreach($sp['foto'] ?? [] as $f) {
                                $allPhotos[] = [
                                    'path' => $f,
                                    'label' => ($sp['nama_titik'] ?: 'Dokumentasi Lapangan') . ' (' . $tglLabel . ')',
                                ];
                            }
                        }
                    }
                }
                if(!empty($periodikData['foto'])) {
                    foreach($periodikData['foto'] as $f) {
                        $allPhotos[] = [
                            'path' => $f,
                            'label' => 'Dokumentasi Pelaksanaan Tugas Periode',
                        ];
                    }
                }
            @endphp

            <div class="f4-preview-wrapper flex flex-col items-center gap-6 py-4">
                <!-- Page Info / Indicator Bar (No Print) -->
                <div class="no-print w-full max-w-[215mm] flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 px-2">
                    <div class="flex items-center gap-1.5 font-semibold">
                        <svg class="h-4 w-4 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Pratinjau Format Kertas F4 / Folio (21.5 cm × 33.0 cm)</span>
                    </div>
                    <span class="text-[11px] bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-300 border border-teal-200 dark:border-teal-800 px-2.5 py-0.5 rounded font-bold">
                        {{ !empty($allPhotos) ? '📄 Total: 2 Halaman (Laporan + Lampiran)' : '📄 Total: 1 Halaman (Laporan Utama)' }}
                    </span>
                </div>

                <!-- ================= LEMBAR 1: LAPORAN KEGIATAN UTAMA (F4) ================= -->
                <div class="f4-sheet-preview print-container bg-white text-black leading-normal relative flex flex-col justify-between">
                    <div>
                        <!-- Page Number Badge for Preview Only -->
                        <div class="no-print absolute top-3 right-4 text-[10px] font-bold text-gray-400 bg-gray-100 dark:bg-gray-200 px-2 py-0.5 rounded shadow-sm">
                            Halaman 1
                        </div>

                        <!-- 1. Document Title -->
                        <div class="text-center mb-5">
                            <h1 class="text-sm md:text-base font-bold tracking-wide uppercase">LAPORAN PERJALANAN DINAS</h1>
                        </div>

                        <!-- 2. Header Metadata (2-Column Grid) -->
                        <div class="text-[11px] mb-3 space-y-1">
                            <div class="grid grid-cols-12 gap-1">
                                <div class="col-span-4 sm:col-span-3 font-semibold">Nama Pelaksana</div>
                                <div class="col-span-8 sm:col-span-9">: {{ $this->getPenugasan()->pegawai?->nama ?? $selectedPelaksanaNip }}</div>
                            </div>
                            <div class="grid grid-cols-12 gap-1">
                                <div class="col-span-4 sm:col-span-3 font-semibold">NIP</div>
                                <div class="col-span-8 sm:col-span-9">: {{ $selectedPelaksanaNip }}</div>
                            </div>
                            <div class="grid grid-cols-12 gap-1">
                                <div class="col-span-4 sm:col-span-3 font-semibold">Wilayah Tugas</div>
                                <div class="col-span-8 sm:col-span-9">: {{ $daerahDikunjungi }}</div>
                            </div>
                            <div class="grid grid-cols-12 gap-1">
                                <div class="col-span-4 sm:col-span-3 font-semibold">Kegiatan</div>
                                <div class="col-span-8 sm:col-span-9">: {{ $kegiatanNama }}</div>
                            </div>
                            <div class="grid grid-cols-12 gap-1">
                                <div class="col-span-4 sm:col-span-3 font-semibold">Tanggal</div>
                                <div class="col-span-8 sm:col-span-9">: {{ $periodeStr }}</div>
                            </div>
                        </div>

                        <div class="border-t-2 border-black mb-3"></div>

                        <!-- 3. Laporan Kegiatan Metadata -->
                        <div class="text-[11px] mb-3 space-y-1">
                            <div class="font-bold mb-1.5">Laporan Kegiatan</div>
                            
                            <div class="grid grid-cols-12 gap-1 pl-4">
                                <div class="col-span-5 sm:col-span-4 font-semibold">I. Dasar Pelaksanaan</div>
                                <div class="col-span-7 sm:col-span-8">: {{ $nomorSuratTugas }}</div>
                            </div>
                            <div class="grid grid-cols-12 gap-1 pl-4">
                                <div class="col-span-5 sm:col-span-4 font-semibold">II. Moda Transportasi</div>
                                <div class="col-span-7 sm:col-span-8">: {{ $modaTransportasi }}</div>
                            </div>
                            <div class="grid grid-cols-12 gap-1 pl-4">
                                <div class="col-span-5 sm:col-span-4 font-semibold">III. Daerah yang dikunjungi</div>
                                <div class="col-span-7 sm:col-span-8">: {{ $daerahDikunjungi }}</div>
                            </div>
                        </div>

                        <!-- 4. TABEL MATRIKS KEGIATAN (5 KOLOM STANDAR BPS DENGAN STREAMING EFFECT) -->
                        <div class="mt-3 overflow-x-auto">
                            <!-- AI Live Generation Gimmick Status Banner (No Print) -->
                            <div x-show="isStreaming" x-cloak class="no-print mb-2.5 p-2 bg-gradient-to-r from-teal-500/10 via-emerald-500/10 to-cyan-500/10 dark:from-teal-900/30 dark:via-emerald-900/30 dark:to-cyan-900/30 border border-teal-300/80 dark:border-teal-700 rounded-xl flex items-center justify-between shadow-sm animate-pulse">
                                <div class="flex items-center gap-2">
                                    <span class="relative flex h-2 w-2">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-teal-500"></span>
                                    </span>
                                    <span class="text-[11px] font-bold text-teal-900 dark:text-teal-200">
                                        ✨ AI Generative Engine: Menyusun butir kegiatan, jam perjalanan & pemecahan masalah secara live...
                                    </span>
                                </div>
                                <button type="button" @click="skipStreaming()" class="text-[10px] text-teal-700 dark:text-teal-300 hover:text-teal-900 dark:hover:text-white font-bold px-2 py-0.5 rounded bg-white/80 dark:bg-gray-800 border border-teal-200 dark:border-teal-700 transition">
                                    Lewati Animasi ➔
                                </button>
                            </div>

                            <table class="w-full text-[11px] border border-black border-collapse table-fixed" style="font-family: Arial, Helvetica, sans-serif;">
                                <thead>
                                    <tr class="bg-gray-100 text-center font-bold">
                                        <th class="border border-black px-2 py-1.5 text-center" style="width: 13%;">Tanggal</th>
                                        <th class="border border-black px-2 py-1.5 text-center" style="width: 14%;">Waktu (WIB)</th>
                                        <th class="border border-black px-2 py-1.5 text-center" style="width: 37%;">Uraian Kegiatan</th>
                                        <th class="border border-black px-2 py-1.5 text-center" style="width: 18%;">Permasalahan/ Pemecahan</th>
                                        <th class="border border-black px-2 py-1.5 text-center" style="width: 18%;">Keterangan</th>
                                        @if($isEditing)
                                            <th class="border border-black px-1 py-1 w-10 no-print text-center">Aksi</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(!empty($reportData['tabel_kegiatan']))
                                        @php
                                            $grouped = collect($reportData['tabel_kegiatan'])->groupBy('tanggal');
                                            $globalRowCounter = 0;
                                        @endphp
                                        @foreach($grouped as $tgl => $rows)
                                            @foreach($rows as $rIdx => $row)
                                                @php
                                                    $rawRowIndex = array_search($row, $reportData['tabel_kegiatan']);
                                                    $globalRowCounter++;
                                                @endphp
                                                <tr class="align-top transition-all duration-300"
                                                    :class="{ 'opacity-100': !isStreaming || revealedRows >= {{ $globalRowCounter }}, 'opacity-0': isStreaming && revealedRows < {{ $globalRowCounter }} }">
                                                    @if($rIdx === 0)
                                                        <td rowspan="{{ count($rows) }}" class="border border-black px-2 py-1.5 text-center font-semibold bg-gray-50/50">
                                                            {{ \Illuminate\Support\Carbon::parse($tgl)->translatedFormat('d F Y') }}
                                                            @if($isEditing)
                                                                <div class="no-print pt-1">
                                                                    <button type="button" wire:click="addTableRow('{{ $tgl }}')" class="text-[10px] text-teal-600 hover:underline block font-normal">
                                                                        + Tambah Baris
                                                                    </button>
                                                                </div>
                                                            @endif
                                                        </td>
                                                    @endif
                                                    
                                                    <!-- Waktu -->
                                                    <td class="border border-black px-2 py-1.5 text-center whitespace-nowrap font-mono text-[10px]">
                                                        @if($isEditing)
                                                            <input type="text" wire:model="reportData.tabel_kegiatan.{{ $rawRowIndex }}.waktu" class="w-full text-center text-xs p-1 border rounded">
                                                        @else
                                                            {{ $row['waktu'] ?? '-' }}
                                                        @endif
                                                    </td>

                                                    <!-- Uraian Kegiatan -->
                                                    <td class="border border-black px-2.5 py-1.5 text-justify leading-snug text-[10.5px]">
                                                        @if($isEditing)
                                                            <textarea wire:model="reportData.tabel_kegiatan.{{ $rawRowIndex }}.uraian_kegiatan" rows="2" class="w-full text-xs p-1 border rounded"></textarea>
                                                        @else
                                                            <span>{{ $row['uraian_kegiatan'] ?? '-' }}</span>
                                                            <span x-show="isStreaming && revealedRows === {{ $globalRowCounter }}" class="inline-block w-1.5 h-3 bg-teal-500 animate-ping ml-0.5 align-middle no-print"></span>
                                                        @endif
                                                    </td>

                                                    <!-- Permasalahan / Pemecahan -->
                                                    <td class="border border-black px-2.5 py-1.5 text-justify leading-snug text-[10.5px]">
                                                        @if($isEditing)
                                                            <textarea wire:model="reportData.tabel_kegiatan.{{ $rawRowIndex }}.permasalahan_pemecahan" rows="2" class="w-full text-xs p-1 border rounded"></textarea>
                                                        @else
                                                            {{ $row['permasalahan_pemecahan'] ?? '-' }}
                                                        @endif
                                                    </td>

                                                    <!-- Keterangan -->
                                                    <td class="border border-black px-2.5 py-1.5 leading-snug text-[10.5px]">
                                                        @if($isEditing)
                                                            <textarea wire:model="reportData.tabel_kegiatan.{{ $rawRowIndex }}.keterangan" rows="2" class="w-full text-xs p-1 border rounded"></textarea>
                                                        @else
                                                            {!! nl2br(e($row['keterangan'] ?? '-')) !!}
                                                        @endif
                                                    </td>

                                                    @if($isEditing)
                                                        <td class="border border-black p-1 text-center no-print">
                                                            <button type="button" wire:click="removeTableRow({{ $rawRowIndex }})" class="text-red-500 hover:text-red-700 p-1" title="Hapus baris ini">
                                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                </svg>
                                                            </button>
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    @else
                                        <tr class="align-top">
                                            <td class="border border-black px-2 py-1.5 text-center font-semibold">
                                                {{ $periodeStr }}
                                            </td>
                                            <td class="border border-black px-2 py-1.5 text-center font-mono text-[10px]">
                                                08.00 - 15.00
                                            </td>
                                            <td class="border border-black px-2.5 py-1.5 text-justify leading-snug text-[10.5px]">
                                                {{ $reportData['uraian_kegiatan_polished'] ?? ($reportData['ringkasan'] ?? '-') }}
                                            </td>
                                            <td class="border border-black px-2.5 py-1.5 text-justify leading-snug text-[10.5px]">
                                                {{ $reportData['kendala_polished'] ?? '-' }}
                                            </td>
                                            <td class="border border-black px-2.5 py-1.5 leading-snug text-[10.5px]">
                                                {{ $reportData['solusi_polished'] ?? '1. Pelaksanaan berjalan lancar sesuai SOP.' }}
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- 5. Signature Block Tepat Setelah Tabel di Halaman 1 -->
                    <div class="signature-block flex justify-end mt-5 text-[11px]">
                        <div class="text-center w-60 space-y-10">
                            <div>
                                Mempawah, {{ \Illuminate\Support\Carbon::parse($tanggalLaporan)->translatedFormat('d F Y') }}<br>
                                yang melaporkan,
                            </div>
                            
                            <div>
                                <div class="font-bold underline uppercase">
                                    {{ $this->getPenugasan()->pegawai?->nama ?? $selectedPelaksanaNip }}
                                </div>
                                <div>NIP. {{ $selectedPelaksanaNip }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                @if(!empty($allPhotos))
                    <!-- ================= PEMBATAS HALAMAN / PAGE BREAK DIVIDER (PREVIEW ONLY) ================= -->
                    <div class="no-print w-full max-w-[215mm] flex items-center justify-center my-1">
                        <div class="relative flex items-center justify-center w-full">
                            <div class="border-t-2 border-dashed border-teal-400/80 dark:border-teal-600/80 w-full"></div>
                            <span class="absolute bg-white dark:bg-gray-900 text-teal-800 dark:text-teal-300 text-xs px-4 py-1.5 rounded-full font-bold border border-teal-300 dark:border-teal-700 shadow-md flex items-center gap-1.5">
                                ✂️ Batas Halaman (Page Break) ➔ Halaman 2: Lampiran Foto Dokumentasi
                            </span>
                        </div>
                    </div>

                    <!-- ================= LEMBAR 2: LAMPIRAN DOKUMENTASI FOTO (F4) ================= -->
                    <div class="f4-sheet-preview print-container f4-page-break bg-white text-black leading-normal relative flex flex-col justify-start">
                        <div>
                            <!-- Page Number Badge for Preview Only -->
                            <div class="no-print absolute top-3 right-4 text-[10px] font-bold text-gray-400 bg-gray-100 dark:bg-gray-200 px-2 py-0.5 rounded shadow-sm">
                                Halaman 2
                            </div>

                            <!-- 1. Header Lampiran -->
                            <div class="text-center mb-6 pb-3 border-b-2 border-black">
                                <h2 class="text-sm font-bold tracking-wide uppercase">LAMPIRAN DOKUMENTASI FOTO KEGIATAN</h2>
                                <p class="text-[10.5px] text-gray-700 mt-1 font-medium">
                                    Laporan Perjalanan Dinas — Surat Tugas No: {{ $nomorSuratTugas }}
                                </p>
                            </div>

                            <!-- 2. Grid Foto Dokumentasi -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 md:gap-6 max-w-2xl mx-auto">
                                @foreach($allPhotos as $item)
                                    <div class="photo-item border border-black rounded-lg overflow-hidden bg-white shadow-sm flex flex-col">
                                        <div class="w-full bg-gray-50 flex items-center justify-center p-2">
                                            <img src="{{ asset('storage/' . $item['path']) }}" class="w-full h-auto max-h-56 object-contain rounded">
                                        </div>
                                        <div class="bg-gray-100 px-3 py-1.5 border-t border-black text-[10px] font-semibold text-center text-gray-800">
                                            {{ $item['label'] }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            function laporanPerjadinPage(initialDistrictName, isGeneratedInitial) {
                return {
                    isPrinting: false,
                    showMapModal: false,
                    currentDayIndex: null,
                    currentSpotIndex: null,
                    isPeriodic: false,
                    mapLat: -0.0264,
                    mapLng: 109.3425,
                    leafletMap: null,
                    leafletMarker: null,
                    isLocating: false,
                    districtName: initialDistrictName || 'Kecamatan di Kab. Mempawah',

                    revealedRows: 0,
                    isStreaming: true,
                    loadingStep: 1,
                    stepInterval: null,

                    init() {
                        if (isGeneratedInitial) {
                            this.startStreaming();
                        }
                    },

                    startStreaming() {
                        this.revealedRows = 0;
                        this.isStreaming = true;
                        let total = 20;
                        let timer = setInterval(() => {
                            this.revealedRows++;
                            if (this.revealedRows >= total) {
                                this.isStreaming = false;
                                clearInterval(timer);
                            }
                        }, 180);
                    },

                    skipStreaming() {
                        this.revealedRows = 999;
                        this.isStreaming = false;
                    },

                    startLoadingProgress() {
                        this.loadingStep = 1;
                        if (this.stepInterval) clearInterval(this.stepInterval);
                        this.stepInterval = setInterval(() => {
                            if (this.loadingStep < 3) {
                                this.loadingStep++;
                            }
                        }, 1800);
                    },

                    openMap(dayIndex, spotIndex = 0, isPeriodic = false) {
                        this.currentDayIndex = dayIndex;
                        this.currentSpotIndex = spotIndex;
                        this.isPeriodic = isPeriodic;
                        
                        let coordStr = '';
                        let wire = this.$wire || window.Livewire?.find(this.$el.closest('[wire\\:id]')?.getAttribute('wire:id'));
                        if (isPeriodic) {
                            let list = wire ? wire.get('periodikData.titik_kegiatan') : [];
                            coordStr = list && list[spotIndex] ? list[spotIndex].koordinat : '';
                        } else {
                            let list = wire ? wire.get('harian.' + dayIndex + '.titik_kegiatan') : [];
                            coordStr = list && list[spotIndex] ? list[spotIndex].koordinat : '';
                        }
                        
                        if (coordStr && coordStr.includes(',')) {
                            let parts = coordStr.split(',');
                            this.mapLat = parseFloat(parts[0].trim()) || -0.0264;
                            this.mapLng = parseFloat(parts[1].trim()) || 109.3425;
                            this.showMapModal = true;
                            this.$nextTick(() => {
                                setTimeout(() => this.initOrUpdateMap(), 250);
                            });
                        } else if (navigator.geolocation) {
                            this.isLocating = true;
                            navigator.geolocation.getCurrentPosition(
                                (pos) => {
                                    this.mapLat = pos.coords.latitude;
                                    this.mapLng = pos.coords.longitude;
                                    this.isLocating = false;
                                    this.showMapModal = true;
                                    this.$nextTick(() => {
                                        setTimeout(() => this.initOrUpdateMap(), 250);
                                    });
                                },
                                (err) => {
                                    this.isLocating = false;
                                    this.mapLat = -0.0264;
                                    this.mapLng = 109.3425;
                                    this.showMapModal = true;
                                    this.$nextTick(() => {
                                        setTimeout(() => this.initOrUpdateMap(), 250);
                                    });
                                },
                                { enableHighAccuracy: true, timeout: 6000 }
                            );
                        } else {
                            this.mapLat = -0.0264;
                            this.mapLng = 109.3425;
                            this.showMapModal = true;
                            this.$nextTick(() => {
                                setTimeout(() => this.initOrUpdateMap(), 250);
                            });
                        }
                    },

                    loadLeaflet() {
                        if (window.L) return Promise.resolve();
                        return new Promise((resolve) => {
                            if (!document.getElementById('leaflet-css')) {
                                let link = document.createElement('link');
                                link.id = 'leaflet-css';
                                link.rel = 'stylesheet';
                                link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                                document.head.appendChild(link);
                            }
                            if (!document.getElementById('leaflet-js')) {
                                let script = document.createElement('script');
                                script.id = 'leaflet-js';
                                script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                                script.onload = () => resolve();
                                document.head.appendChild(script);
                            } else {
                                resolve();
                            }
                        });
                    },

                    async initOrUpdateMap() {
                        await this.loadLeaflet();
                        if (!window.L) return;
                        let container = document.getElementById('interactive-map-canvas');
                        if (!container) return;

                        if (!this.leafletMap) {
                            this.leafletMap = L.map(container).setView([this.mapLat, this.mapLng], 15);
                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                maxZoom: 19,
                                attribution: '© OpenStreetMap contributors'
                            }).addTo(this.leafletMap);

                            this.leafletMarker = L.marker([this.mapLat, this.mapLng], {
                                draggable: true,
                                autoPan: true
                            }).addTo(this.leafletMap);

                            this.leafletMarker.on('dragend', (e) => {
                                let pos = e.target.getLatLng();
                                this.mapLat = pos.lat;
                                this.mapLng = pos.lng;
                            });

                            this.leafletMap.on('click', (e) => {
                                this.mapLat = e.latlng.lat;
                                this.mapLng = e.latlng.lng;
                                this.leafletMarker.setLatLng(e.latlng);
                            });
                        } else {
                            this.leafletMap.invalidateSize();
                            this.leafletMap.setView([this.mapLat, this.mapLng], 15);
                            this.leafletMarker.setLatLng([this.mapLat, this.mapLng]);
                        }
                    },

                    useDeviceLocation() {
                        if (navigator.geolocation) {
                            this.isLocating = true;
                            navigator.geolocation.getCurrentPosition(
                                (pos) => {
                                    this.mapLat = pos.coords.latitude;
                                    this.mapLng = pos.coords.longitude;
                                    this.isLocating = false;
                                    this.initOrUpdateMap();
                                },
                                (err) => {
                                    this.isLocating = false;
                                    alert('Gagal mendeteksi lokasi GPS device.');
                                },
                                { enableHighAccuracy: true }
                            );
                        }
                    },

                    confirmLocation() {
                        let coordFormatted = this.mapLat.toFixed(6) + ', ' + this.mapLng.toFixed(6);
                        let wire = this.$wire || window.Livewire?.find(this.$el.closest('[wire\\:id]')?.getAttribute('wire:id'));
                        if (wire) {
                            if (this.isPeriodic) {
                                wire.set('periodikData.titik_kegiatan.' + this.currentSpotIndex + '.koordinat', coordFormatted);
                            } else {
                                wire.set('harian.' + this.currentDayIndex + '.titik_kegiatan.' + this.currentSpotIndex + '.koordinat', coordFormatted);
                            }
                        }
                        this.showMapModal = false;
                    },

                    applyWatermark: true,

                    async reverseGeocode(lat, lng) {
                        if (!lat || !lng) return null;
                        try {
                            let controller = new AbortController();
                            let timeoutId = setTimeout(() => controller.abort(), 2500);
                            let res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`, {
                                signal: controller.signal,
                                headers: { 'Accept-Language': 'id-ID,id;q=0.9' }
                            });
                            clearTimeout(timeoutId);
                            if (res.ok) {
                                let data = await res.json();
                                if (data && data.address) {
                                    let a = data.address;
                                    let road = a.road || a.pedestrian || a.street || '';
                                    let village = a.village || a.suburb || a.neighbourhood || a.quarter || '';
                                    let subdistrict = a.municipality || a.subdistrict || a.city_district || '';
                                    let regency = a.county || a.city || 'Kab. Mempawah';
                                    
                                    let parts = [];
                                    if (road) parts.push(road);
                                    if (village) parts.push('Ds. ' + village);
                                    if (subdistrict) parts.push('Kec. ' + subdistrict);
                                    if (regency) parts.push(regency);
                                    
                                    if (parts.length > 0) {
                                        return parts.join(', ');
                                    }
                                }
                            }
                        } catch (e) {
                            console.warn('Reverse geocoding OSM fallback:', e);
                        }

                        try {
                            let res = await fetch(`https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=${lat}&longitude=${lng}&localityLanguage=id`);
                            if (res.ok) {
                                let data = await res.json();
                                let parts = [];
                                if (data.locality) parts.push('Kec. ' + data.locality);
                                if (data.city) parts.push(data.city);
                                if (parts.length > 0) return parts.join(', ');
                            }
                        } catch (e) {}

                        return null;
                    },

                    async processAndWatermarkPhoto(event, dayIndex, spotIndex = 0, isPeriodic = false) {
                        let files = event.target.files;
                        if (!files || files.length === 0) return;

                        let spotName = '';
                        let coordText = '';
                        let targetDate = '';
                        let wire = this.$wire || window.Livewire?.find(this.$el.closest('[wire\\:id]')?.getAttribute('wire:id'));

                        if (isPeriodic) {
                            let list = wire ? wire.get('periodikData.titik_kegiatan') : [];
                            coordText = list && list[spotIndex] ? list[spotIndex].koordinat : '';
                            spotName = list && list[spotIndex] ? list[spotIndex].nama_titik : '';
                        } else {
                            let dayData = wire ? wire.get('harian.' + dayIndex) : {};
                            let list = dayData ? dayData.titik_kegiatan : [];
                            coordText = list && list[spotIndex] ? list[spotIndex].koordinat : '';
                            spotName = list && list[spotIndex] ? list[spotIndex].nama_titik : '';
                            targetDate = dayData ? dayData.tanggal : '';
                        }

                        // Extract lat & lng from coordText or fallback
                        let latNum = this.mapLat;
                        let lngNum = this.mapLng;
                        if (coordText && coordText.includes(',')) {
                            let cParts = coordText.split(',');
                            let pLat = parseFloat(cParts[0].trim());
                            let pLng = parseFloat(cParts[1].trim());
                            if (!isNaN(pLat) && !isNaN(pLng)) {
                                latNum = pLat;
                                lngNum = pLng;
                            }
                        }

                        let geocodedAddress = null;
                        if (this.applyWatermark) {
                            geocodedAddress = await this.reverseGeocode(latNum, lngNum);
                        }

                        for (let file of Array.from(files)) {
                            await new Promise((resolve) => {
                                let reader = new FileReader();
                                reader.onload = (e) => {
                                    let img = new Image();
                                    img.onload = () => {
                                        let canvas = document.createElement('canvas');
                                        let maxDim = 1600;
                                        let width = img.width;
                                        let height = img.height;

                                        if (width > maxDim || height > maxDim) {
                                            if (width > height) {
                                                height = Math.round((height * maxDim) / width);
                                                width = maxDim;
                                            } else {
                                                width = Math.round((width * maxDim) / height);
                                                height = maxDim;
                                            }
                                        }

                                        canvas.width = width;
                                        canvas.height = height;
                                        let ctx = canvas.getContext('2d');
                                        ctx.drawImage(img, 0, 0, width, height);

                                        if (this.applyWatermark) {
                                            let now = new Date();
                                            let timeOnly = now.toLocaleTimeString('id-ID', {
                                                timeZone: 'Asia/Jakarta',
                                                hour: '2-digit',
                                                minute: '2-digit',
                                                second: '2-digit'
                                            }).replace(/\./g, ':');

                                            let dateFormatted = '';
                                            if (targetDate) {
                                                let parts = targetDate.split('-');
                                                if (parts.length === 3) {
                                                    dateFormatted = parts[2] + '/' + parts[1] + '/' + parts[0];
                                                }
                                            }
                                            if (!dateFormatted) {
                                                dateFormatted = now.toLocaleDateString('id-ID', {
                                                    timeZone: 'Asia/Jakarta',
                                                    day: '2-digit',
                                                    month: '2-digit',
                                                    year: 'numeric'
                                                });
                                            }

                                            let timeStampStr = dateFormatted + ' ' + timeOnly + ' WIB (UTC+7)';
                                            let fontSize = Math.max(18, Math.round(width * 0.024));
                                            let lineHeight = fontSize * 1.35;
                                            let paddingX = Math.round(width * 0.02);
                                            let paddingY = Math.round(fontSize * 0.7);

                                            let displayWilayah = geocodedAddress || (this.districtName ? this.districtName : 'Kabupaten Mempawah');
                                            if (!displayWilayah.toLowerCase().includes('mempawah')) {
                                                displayWilayah += ', Kab. Mempawah';
                                            }

                                            let boxHeight = (lineHeight * 3) + (paddingY * 2);

                                            ctx.fillStyle = 'rgba(15, 23, 42, 0.88)';
                                            ctx.fillRect(0, height - boxHeight, width, boxHeight);

                                            ctx.fillStyle = '#06B6D4';
                                            ctx.fillRect(0, height - boxHeight, width, Math.max(3, Math.round(fontSize * 0.15)));

                                            let startY = height - boxHeight + paddingY + (fontSize * 0.9);

                                            ctx.fillStyle = '#FFFFFF';
                                            ctx.font = 'bold ' + fontSize + 'px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
                                            ctx.shadowColor = 'rgba(0,0,0,0.8)';
                                            ctx.shadowBlur = 4;
                                            ctx.fillText('🕒 ' + timeStampStr, paddingX, startY);

                                            let displayCoord = coordText || (latNum.toFixed(6) + ', ' + lngNum.toFixed(6));
                                            let displaySpot = spotName ? ' (' + spotName + ')' : '';
                                            ctx.fillStyle = '#38BDF8';
                                            ctx.font = 'bold ' + (fontSize * 0.92) + 'px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
                                            ctx.fillText('📍 GPS: ' + displayCoord + displaySpot, paddingX, startY + lineHeight);

                                            ctx.fillStyle = '#6EE7B7';
                                            ctx.font = 'bold ' + (fontSize * 0.92) + 'px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
                                            ctx.fillText('🏛️ Alamat: ' + displayWilayah, paddingX, startY + (lineHeight * 2));

                                            ctx.shadowColor = 'transparent';
                                            ctx.shadowBlur = 0;
                                        }

                                        let dataUrl = canvas.toDataURL('image/jpeg', 0.88);
                                        if (wire) {
                                            wire.saveWatermarkedPhoto(dayIndex, spotIndex, dataUrl, isPeriodic);
                                        }
                                        resolve();
                                    };
                                    img.src = e.target.result;
                                };
                                reader.readAsDataURL(file);
                            });
                        }

                        event.target.value = '';
                    }
                };
            }
        </script>
    @endpush
</x-filament-panels::page>
