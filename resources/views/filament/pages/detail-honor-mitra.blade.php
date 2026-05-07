<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <x-filament::section>
             <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Honor Sensus</div>
             <div class="text-2xl font-bold text-primary-600 dark:text-primary-400">
                Rp {{ number_format($this->getSensusTotal(), 0, ',', '.') }}
             </div>
        </x-filament::section>
        
        <x-filament::section>
             <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Honor Survei</div>
             <div class="text-2xl font-bold text-primary-600 dark:text-primary-400">
                Rp {{ number_format($this->getSurveiTotal(), 0, ',', '.') }}
             </div>
        </x-filament::section>
    </div>

    {{ $this->table }}
</x-filament-panels::page>
