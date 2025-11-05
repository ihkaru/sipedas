<x-filament-panels::page>


    <div
        class="fi-section mt-4 rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="fi-section-header-ctn border-b border-gray-200 px-4 py-3 dark:border-white/10">
            <h2 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                Filter Data
            </h2>
        </div>
        <div class="fi-section-content-ctn p-6">
            <form wire:submit.prevent="fetchTableData">
                {{ $this->form }}
            </form>
        </div>
    </div>
    <div class="border-b border-gray-200 dark:border-gray-700">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button wire:click="setFilterToRealisasi" @class([
                'whitespace-nowrap border-b-2 px-1 pb-4 text-sm font-medium',
                'border-primary-500 text-primary-600' => $activeMatrix === 'realisasi',
                'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:border-gray-600 dark:hover:text-gray-200' =>
                    $activeMatrix !== 'realisasi',
            ])>
                Matriks Realisasi
            </button>
            <button wire:click="setFilterToPengajuan" @class([
                'whitespace-nowrap border-b-2 px-1 pb-4 text-sm font-medium',
                'border-primary-500 text-primary-600' => $activeMatrix === 'pengajuan',
                'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:border-gray-600 dark:hover:text-gray-200' =>
                    $activeMatrix !== 'pengajuan',
            ])>
                Matriks Pengajuan
            </button>
        </nav>
    </div>

    @if (!empty($selectedKegiatans))
        <div
            class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="fi-section-header-ctn border-b border-gray-200 px-4 py-3 dark:border-white/10">
                <h2 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                    Matriks Perjadin
                </h2>
            </div>
            <div class="fi-section-content-ctn p-0">
                <div class="overflow-x-auto">
                    <table
                        class="fi-table w-full min-w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
                        <thead class="bg-gray-50 dark:bg-white/5">
                            <tr>
                                <th
                                    class="fi-table-header-cell px-3 py-3.5 sm:px-6 text-sm font-semibold text-gray-950 dark:text-white text-left">
                                    Nama Pegawai
                                </th>
                                <th
                                    class="fi-table-header-cell px-3 py-3.5 sm:px-6 text-sm font-semibold text-gray-950 dark:text-white text-center">
                                    Total Perjadin (Hari)
                                </th>
                                @foreach ($dateColumns as $date)
                                    <th
                                        class="fi-table-header-cell px-3 py-3.5 sm:px-6 text-sm font-semibold text-gray-950 dark:text-white text-center">
                                        {{ \Carbon\Carbon::parse($date)->format('d M Y') }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 whitespace-nowrap dark:divide-white/5">
                            @forelse ($tableData as $pegawaiData)
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                    <td class="fi-table-cell px-3 py-4 sm:px-6 text-sm text-gray-950 dark:text-white">
                                        {{ $pegawaiData['nama'] }}
                                    </td>
                                    <td
                                        class="fi-table-cell px-3 py-4 sm:px-6 text-sm text-gray-950 dark:text-white text-center">
                                        {{ $pegawaiData['total_perjadin'] }}
                                    </td>
                                    @foreach ($dateColumns as $date)
                                        <td
                                            class="fi-table-cell px-3 py-4 sm:px-6 text-sm text-gray-500 dark:text-gray-400 text-center">
                                            <span class="rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset">
                                                {{ $pegawaiData['dates'][$date] ?? '' }}
                                            </span>
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($dateColumns) + 2 }}" class="fi-table-cell px-3 py-4 sm:px-6 text-center text-gray-500 dark:text-gray-400">
                                        Tidak ada data untuk ditampilkan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-gray-100 dark:bg-gray-800 font-bold">
                            <tr>
                                <td class="fi-table-cell px-3 py-3.5 sm:px-6 text-sm text-gray-950 dark:text-white">
                                    Total
                                </td>
                                <td class="fi-table-cell px-3 py-3.5 sm:px-6 text-sm text-gray-950 dark:text-white text-center">
                                    {{ $tableTotals['total_perjadin'] ?? 0 }}
                                </td>
                                @foreach ($dateColumns as $date)
                                    <td class="fi-table-cell px-3 py-3.5 sm:px-6 text-sm text-gray-950 dark:text-white text-center">
                                        {{ $tableTotals['dates'][$date] ?? 0 }}
                                    </td>
                                @endforeach
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @if (!empty($selectedKegiatans))
        <div
            class="fi-section mt-6 rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="fi-section-header-ctn border-b border-gray-200 px-4 py-3 dark:border-white/10">
                <h2 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                    Ringkasan Berdasarkan Tujuan
                </h2>
            </div>
            <div class="fi-section-content-ctn p-0">
                <div class="overflow-x-auto">
                    <table
                        class="fi-table w-full min-w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
                        <thead class="bg-gray-50 dark:bg-white/5">
                            <tr>
                                <th
                                    class="fi-table-header-cell px-3 py-3.5 sm:px-6 text-sm font-semibold text-gray-950 dark:text-white text-left">
                                    Tujuan Penugasan
                                </th>
                                <th
                                    class="fi-table-header-cell px-3 py-3.5 sm:px-6 text-sm font-semibold text-gray-950 dark:text-white text-center">
                                    Total Perjadin (Hari)
                                </th>
                                @foreach ($kegiatanColumns as $kegiatanName)
                                    <th
                                        class="fi-table-header-cell px-3 py-3.5 sm:px-6 text-sm font-semibold text-gray-950 dark:text-white text-center">
                                        {{ $kegiatanName }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 whitespace-nowrap dark:divide-white/5">
                            @forelse ($summaryTableData as $summaryRow)
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                    <td class="fi-table-cell px-3 py-4 sm:px-6 text-sm text-gray-950 dark:text-white">
                                        {{ $summaryRow['tujuan'] }}
                                    </td>
                                    <td
                                        class="fi-table-cell px-3 py-4 sm:px-6 text-sm text-gray-950 dark:text-white text-center">
                                        {{ $summaryRow['total_perjadin'] }}
                                    </td>
                                    @foreach ($kegiatanColumns as $kegiatanId => $kegiatanName)
                                        <td
                                            class="fi-table-cell px-3 py-4 sm:px-6 text-sm text-gray-500 dark:text-gray-400 text-center">
                                            {{ $summaryRow['kegiatan_counts'][$kegiatanId] ?? 0 }}
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($kegiatanColumns) + 2 }}"
                                        class="fi-table-cell px-3 py-4 sm:px-6 text-center text-gray-500 dark:text-gray-400">
                                        Tidak ada data untuk ditampilkan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-gray-100 dark:bg-gray-800 font-bold">
                            <tr>
                                <td class="fi-table-cell px-3 py-3.5 sm:px-6 text-sm text-gray-950 dark:text-white">
                                    Total
                                </td>
                                <td class="fi-table-cell px-3 py-3.5 sm:px-6 text-sm text-gray-950 dark:text-white text-center">
                                    {{ $summaryTotals['total_perjadin'] ?? 0 }}
                                </td>
                                @foreach ($kegiatanColumns as $kegiatanId => $kegiatanName)
                                    <td class="fi-table-cell px-3 py-3.5 sm:px-6 text-sm text-gray-950 dark:text-white text-center">
                                        {{ $summaryTotals['kegiatan_counts'][$kegiatanId] ?? 0 }}
                                    </td>
                                @endforeach
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
