<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <x-filament::section>
             <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Honor Sensus</div>
             <div class="text-2xl font-bold text-primary-600 dark:text-primary-400">
                Rp {{ number_format($this->getSensusTotal(), 0, ',', '.') }}
             </div>
             <div class="mt-1 text-xs text-gray-500">
                Sisa: Rp {{ number_format($this->getSensusRemaining(), 0, ',', '.') }}
             </div>
        </x-filament::section>
        
        <x-filament::section>
             <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Honor Survei</div>
             <div class="text-2xl font-bold text-primary-600 dark:text-primary-400">
                Rp {{ number_format($this->getSurveiTotal(), 0, ',', '.') }}
             </div>
             <div class="mt-1 text-xs text-gray-500">
                Sisa: Rp {{ number_format($this->getSurveiRemaining(), 0, ',', '.') }}
             </div>
        </x-filament::section>
    </div>

    <style>
        .spk-info-banner {
            background-color: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 1rem;
            border-top-right-radius: 0.5rem;
            border-bottom-right-radius: 0.5rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
        .dark .spk-info-banner {
            background-color: #1e293b !important;
            border-left: 4px solid #60a5fa !important;
        }
        .spk-info-title {
            color: #1e3a8a;
            font-weight: 600;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .dark .spk-info-title {
            color: #93c5fd !important;
        }
        .spk-info-list {
            color: #334155;
            font-size: 0.75rem;
            margin-top: 0.5rem;
            margin-left: 1.75rem;
            list-style-type: disc;
        }
        .dark .spk-info-list {
            color: #f1f5f9 !important;
        }
        .spk-info-strong {
            color: #0f172a;
            font-weight: 700;
        }
        .dark .spk-info-strong {
            color: #ffffff !important;
        }
        .spk-badge-survei {
            color: #047857;
            font-weight: 700;
        }
        .dark .spk-badge-survei {
            color: #34d399 !important;
        }
        .spk-badge-sensus {
            color: #b45309;
            font-weight: 700;
        }
        .dark .spk-badge-sensus {
            color: #fbbf24 !important;
        }
    </style>

    <div class="spk-info-banner">
        <div class="spk-info-title">
            <x-heroicon-o-information-circle class="w-5 h-5 text-blue-600 dark:text-blue-400 shrink-0" />
            <span>Aturan Cetak Dokumen Administratif:</span>
        </div>
        <ul class="spk-info-list space-y-1.5">
            <li>
                <strong class="spk-info-strong">SPK Kontrak:</strong>
                Seluruh kegiatan berjenis <span class="spk-badge-survei">SURVEI</span> pada bulan ini akan otomatis digabungkan ke dalam <strong class="spk-info-strong">1 dokumen SPK gabungan</strong>. Kegiatan <span class="spk-badge-sensus">SENSUS</span> dicetak terpisah per kegiatan.
            </li>
            <li>
                <strong class="spk-info-strong">BAST:</strong>
                Berita Acara Serah Terima (BAST) selalu dicetak terpisah <strong class="spk-info-strong">per masing-masing kegiatan</strong>.
            </li>
        </ul>
    </div>

    {{ $this->table }}
</x-filament-panels::page>
