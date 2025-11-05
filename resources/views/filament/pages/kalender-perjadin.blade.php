<x-filament-panels::page>
    <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="fi-section-content-ctn p-6">
            <form wire:submit.prevent="fetchCalendarData" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{ $this->form }}
            </form>
        </div>
    </div>

    <div class="flex items-center justify-between mt-4">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">{{ $monthName }} {{ $year }}</h2>
        <div class="flex items-center">
            <span class="relative z-0 inline-flex shadow-sm rounded-md">
                <button wire:click="previousMonth" type="button"
                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-primary-600 bg-primary-600 text-white text-sm font-medium hover:bg-primary-700 focus:z-10 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
                    <span class="sr-only">Previous month</span>
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                        aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
                <button wire:click="nextMonth" type="button"
                    class="-ml-px relative inline-flex items-center px-2 py-2 rounded-r-md border border-primary-600 bg-primary-600 text-white text-sm font-medium hover:bg-primary-700 focus:z-10 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
                    <span class="sr-only">Next month</span>
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                        aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
            </span>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-4 mt-4">
        <div class="grid grid-cols-7 gap-2">
            @php
                $daysOfWeek = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
            @endphp
            @foreach ($daysOfWeek as $day)
                <div class="font-bold text-center text-gray-500 dark:text-gray-400">{{ $day }}</div>
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
                <div @class([
                    'p-2 border rounded-lg flex flex-col h-40',
                    'bg-primary-100 dark:bg-primary-800' => $isToday,
                    'bg-red-50 dark:bg-red-900' => $isWeekend && !$isToday,
                    'bg-white dark:bg-gray-800' => !$isToday && !$isWeekend,
                ])>
                    <div @class([
                        'font-bold text-lg',
                        'text-primary-600 dark:text-primary-400' => $isToday,
                        'text-red-600 dark:text-red-400' => $isWeekend && !$isToday,
                        'text-gray-800 dark:text-gray-200' => !$isToday && !$isWeekend,
                    ])>
                        {{ $day }}
                    </div>
                    <ul class="mt-2 text-xs space-y-1 overflow-y-auto">
                        @if (isset($calendarData[$day]))
                            @foreach ($calendarData[$day] as $nama)
                                <li class="mb-1">
                                    <span
                                        class="inline-block w-full truncate px-2 py-1 rounded-full text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-300"
                                        title="{{ $nama }}">
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
