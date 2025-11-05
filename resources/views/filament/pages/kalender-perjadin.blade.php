<x-filament-panels::page>
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold">{{ $monthName }} {{ $year }}</h2>
        <div class="flex items-center space-x-2">
            <div>
                <select wire:model.live="selectedPegawai"
                    class="block w-full transition duration-75 rounded-lg shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 disabled:opacity-70 dark:bg-gray-700 dark:text-white dark:focus:border-primary-500 border-gray-300 dark:border-gray-600">
                    <option value="">Semua Pegawai</option>
                    @foreach ($pegawaiOptions as $nip => $nama)
                        <option value="{{ $nip }}">{{ $nama }}</option>
                    @endforeach
                </select>
            </div>
            <button wire:click="previousMonth"
                class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">Bulan
                Sebelumnya</button>
            <button wire:click="nextMonth"
                class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">Bulan
                Berikutnya</button>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-4 mt-8">
        <div class="grid grid-cols-7 gap-2">
            @php
                $daysOfWeek = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
            @endphp
            @foreach ($daysOfWeek as $day)
                <div class="font-bold text-center text-gray-500">{{ $day }}</div>
            @endforeach

            @php
                $firstDayOfMonth = Illuminate\Support\Carbon::create($year, $month, 1)->dayOfWeekIso;
                $daysInMonth = Illuminate\Support\Carbon::create($year, $month, 1)->daysInMonth;
            @endphp

            @for ($i = 1; $i < $firstDayOfMonth; $i++)
                <div></div>
            @endfor

            @for ($day = 1; $day <= $daysInMonth; $day++)
                @php
                    $date = Illuminate\Support\Carbon::create($year, $month, $day);
                    $isToday = now()->isSameDay($date);
                    $isWeekend = $date->isSaturday() || $date->isSunday();
                @endphp
                <div
                    class="p-2 border rounded-lg flex flex-col h-32 {{ $isToday ? 'bg-primary-100' : ($isWeekend ? 'bg-red-50' : '') }}">
                    <div
                        class="font-bold text-lg {{ $isToday ? 'text-primary-600' : ($isWeekend ? 'text-red-600' : '') }}">
                        {{ $day }}</div>
                    <ul class="mt-2 text-xs space-y-1 overflow-y-auto">
                        @if (isset($calendarData[$day]))
                            @foreach ($calendarData[$day] as $nama)
                                <li class="mb-1">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ $nama }}
                                    </span>
                                </li>
                            @endforeach
                        @endif
                    </ul>
                </div>
            @endfor
        </div>
    </div>
</x-filament-panels::page>
