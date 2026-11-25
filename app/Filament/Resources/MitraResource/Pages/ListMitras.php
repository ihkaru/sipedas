<?php

namespace App\Filament\Resources\MitraResource\Pages;

use App\Filament\Resources\MitraResource;
use App\Imports\MitraImport;
use App\Models\Kemitraan;
use App\Models\Mitra;
use App\Services\KemitraanCarryOverService;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\HtmlString;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;
use Exception;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Support\Facades\DB;

class ListMitras extends ListRecords
{
    protected static string $resource = MitraResource::class;

    /**
     * Terapkan Eager Loading untuk meningkatkan performa.
     * Ini akan mengambil semua data 'kemitraans' dalam satu query tambahan,
     * bukan satu query per baris mitra.
     */
    protected function getEloquentQuery(): Builder
    {
        // HANYA LAKUKAN EAGER LOADING. HAPUS SEMUA JOIN DAN SELECT.
        return parent::getEloquentQuery()->with(['kemitraans']);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            Actions\Action::make('rekonsiliasiPraRekrutmen')
                ->label('Rekonsiliasi Pra-Rekrutmen')
                ->icon('heroicon-o-shield-check')
                ->color('warning')
                ->visible(fn (): bool => Kemitraan::query()->where('status_rekrutmen', Kemitraan::REKRUTMEN_BELUM)->exists())
                ->form([
                    TextInput::make('tahun')
                        ->label('Tahun Kemitraan yang Direkonsiliasi')
                        ->numeric()
                        ->required()
                        ->default((int) date('Y') + 1)
                        ->minValue(2020),
                    TextInput::make('alasan')
                        ->label('Catatan Penonaktifan')
                        ->default('Dinonaktifkan pasca-rekrutmen resmi (tidak tercatat lulus pada KEPKA).'),
                ])
                ->requiresConfirmation()
                ->modalHeading('Nonaktifkan Sisa Mitra Pra-Rekrutmen?')
                ->modalDescription('Aksi ini akan mengubah status semua mitra yang masih bertanda "Belum Lulus Rekrutmen" pada tahun terpilih menjadi TIDAK_AKTIF (TIDAK_LULUS). Histori SPK pelatihan yang sudah terbit tetap aman.')
                ->action(function (array $data) {
                    $affected = app(KemitraanCarryOverService::class)->reconcileNonVerified(
                        (int) $data['tahun'],
                        $data['alasan'] ?? null
                    );

                    Notification::make()
                        ->title('Rekonsiliasi Selesai')
                        ->body("{$affected} mitra pra-rekrutmen tahun {$data['tahun']} telah ditandai TIDAK_AKTIF (Tidak Lulus).")
                        ->success()
                        ->send();
                }),

            Actions\ActionGroup::make([
                Actions\Action::make('importMitra')
                    ->label('Impor Mitra')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->form([
                        Placeholder::make('panduan_import')
                            ->label('Panduan Pengambilan Data')
                            ->content(new HtmlString('
                                <div class="text-sm text-gray-600 dark:text-gray-400 space-y-2">
                                    <p>Sistem ini sekarang mendukung impor berkas langsung hasil ekspor dari aplikasi pusat <strong>Manajemen Mitra BPS (KEPKA)</strong>.</p>
                                    <ol class="list-decimal pl-4 space-y-1">
                                        <li>Buka halaman: <a href="https://manajemen-mitra.bps.go.id/mitra/kepka" target="_blank" class="text-primary-600 dark:text-primary-400 underline font-semibold">https://manajemen-mitra.bps.go.id/mitra/kepka</a></li>
                                        <li>Filter <strong>Tahun</strong>, <strong>Provinsi</strong>, dan <strong>Kab/Kota</strong> sesuai kebutuhan.</li>
                                        <li>Klik tombol <strong>Download</strong> di atas tabel (di sebelah kiri tombol Atur Kolom) untuk mengunduh data mitra.</li>
                                        <li>Unggah langsung berkas hasil unduhan tersebut pada field <strong>File Excel</strong> di bawah ini tanpa perlu memindahkan datanya ke template manual lagi.</li>
                                    </ol>
                                </div>
                            ')),
                        // Tambahkan form input untuk Tahun dan Status
                        TextInput::make('tahun')
                            ->label('Tahun Kemitraan')
                            ->required()
                            ->numeric()
                            ->default(date('Y'))
                            ->minValue(2020),
                        Select::make('status')
                            ->label('Status Kemitraan')
                            ->options([
                                'AKTIF' => 'Aktif',
                                'TIDAK_AKTIF' => 'Tidak Aktif',
                                'BLACKLISTED' => 'Blacklisted',
                            ])
                            ->required()
                            ->default('AKTIF'),
                        Select::make('status_rekrutmen')
                            ->label('Status Rekrutmen')
                            ->options(Kemitraan::statusRekrutmenOptions())
                            ->required()
                            ->default(Kemitraan::REKRUTMEN_TERVERIFIKASI)
                            ->helperText('Impor KEPKA resmi akan otomatis memverifikasi mitra pra-rekrutmen yang lulus.'),
                        FileUpload::make('attachment')
                            ->label('File Excel (.xlsx)')
                            ->required()
                            ->disk('local')
                            ->rules([
                                fn (): \Closure => function (string $attribute, mixed $value, \Closure $fail) {
                                    // If value is a string, it represents the already uploaded temporary file path on disk (e.g. livewire-tmp/*.tmp).
                                    // We skip extension validation here because it was already validated as an UploadedFile/TemporaryUploadedFile object during upload.
                                    if (is_string($value)) {
                                        return;
                                    }

                                    $extension = null;
                                    $clientOriginalName = null;
                                    
                                    if (is_object($value)) {
                                        if (method_exists($value, 'getClientOriginalExtension')) {
                                            $extension = $value->getClientOriginalExtension();
                                        }
                                        if (method_exists($value, 'getClientOriginalName')) {
                                            $clientOriginalName = $value->getClientOriginalName();
                                        }
                                    }

                                    // Fallback to parsing the client original name if extension is empty
                                    if (empty($extension) && !empty($clientOriginalName)) {
                                        $extension = pathinfo($clientOriginalName, PATHINFO_EXTENSION);
                                    }

                                    if ($extension) {
                                        $ext = strtolower($extension);
                                        if (!in_array($ext, ['xlsx', 'xls'])) {
                                            $fail('Jenis berkas tidak valid. Mengharapkan .xlsx atau .xls');
                                        }
                                    } else {
                                        $fail('Gagal memverifikasi format berkas. Pastikan Anda mengunggah berkas Excel (.xlsx atau .xls).');
                                    }
                                }
                            ]),
                    ])
                    ->action(function (array $data) {
                        try {
                            // --- PERUBAHAN UTAMA DI SINI ---

                            // 1. Dapatkan path relatif file dari disk yang ditentukan
                            $filePath = $data['attachment'];

                            // 2. Buat instance Importer dengan melemparkan data tahun, status, dan status rekrutmen
                            $importer = new MitraImport(
                                (int) $data['tahun'],
                                (string) $data['status'],
                                (string) ($data['status_rekrutmen'] ?? Kemitraan::REKRUTMEN_TERVERIFIKASI)
                            );

                            // 3. Panggil Excel::import dengan 3 argumen
                            Excel::import($importer, $filePath, 'local');

                            Notification::make()
                                ->title('Impor Mitra Berhasil')
                                ->body('Data Mitra dan status kemitraan mereka telah berhasil diimpor/diperbarui.')
                                ->success()
                                ->send();
                        } catch (Exception $e) {
                            Notification::make()
                                ->title('Impor Gagal')
                                ->body('Terjadi kesalahan: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Actions\Action::make('downloadTemplate')
                    ->label('Unduh Template Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(route('download.template.mitra'))
                    ->openUrlInNewTab(),
            ])
                ->label('Aksi Excel')
                ->icon('heroicon-m-table-cells')
                ->color('primary')
                ->button(),
        ];
    }
    /**
     * Mendefinisikan Tabs untuk memfilter daftar Mitra.
     *
     * @return array
     */
    public function getTabs(): array
    {
        $currentYear = (int) date('Y');
        $nextYear = $currentYear + 1;

        $tabs = [
            // Tab untuk menampilkan semua mitra tanpa filter
            'all' => Tab::make('Semua Mitra'),
        ];

        $nextYearCount = Mitra::query()
            ->whereHas('kemitraans', fn (Builder $q) => $q->where('tahun', $nextYear)->where('status', 'AKTIF'))
            ->count();

        if ($nextYearCount > 0) {
            $tabs[(string) $nextYear] = Tab::make("Aktif {$nextYear}")
                ->modifyQueryUsing(
                    fn (Builder $query) => $query->whereHas(
                        'kemitraans',
                        fn (Builder $q) => $q->where('tahun', $nextYear)->where('status', 'AKTIF')
                    )
                )
                ->badge($nextYearCount)
                ->icon('heroicon-o-sparkles');
        }

        $tabs[(string) $currentYear] = Tab::make("Aktif {$currentYear}")
            ->modifyQueryUsing(
                fn (Builder $query) => $query->whereHas(
                    'kemitraans',
                    fn (Builder $q) => $q->where('tahun', $currentYear)->where('status', 'AKTIF')
                )
            )
            ->badge(
                Mitra::query()
                    ->whereHas('kemitraans', fn (Builder $q) => $q->where('tahun', $currentYear)->where('status', 'AKTIF'))
                    ->count()
            )
            ->icon('heroicon-o-calendar-days');

        $tabs[(string) ($currentYear - 1)] = Tab::make('Aktif ' . ($currentYear - 1))
            ->modifyQueryUsing(
                fn (Builder $query) => $query->whereHas(
                    'kemitraans',
                    fn (Builder $q) => $q->where('tahun', ($currentYear - 1))->where('status', 'AKTIF')
                )
            )
            ->badge(
                Mitra::query()
                    ->whereHas('kemitraans', fn (Builder $q) => $q->where('tahun', ($currentYear - 1))->where('status', 'AKTIF'))
                    ->count()
            )
            ->icon('heroicon-o-calendar');

        $belumRekrutmenCount = Mitra::query()
            ->whereHas('kemitraans', fn (Builder $q) => $q->where('status_rekrutmen', Kemitraan::REKRUTMEN_BELUM))
            ->count();

        if ($belumRekrutmenCount > 0) {
            $tabs['belum_rekrutmen'] = Tab::make('Belum Rekrutmen')
                ->modifyQueryUsing(
                    fn (Builder $query) => $query->whereHas(
                        'kemitraans',
                        fn (Builder $q) => $q->where('status_rekrutmen', Kemitraan::REKRUTMEN_BELUM)
                    )
                )
                ->badge($belumRekrutmenCount)
                ->badgeColor('warning')
                ->icon('heroicon-o-exclamation-triangle');
        }

        $tabs['blacklisted'] = Tab::make('Blacklisted')
            ->modifyQueryUsing(
                fn (Builder $query) => $query->whereHas(
                    'kemitraans',
                    fn (Builder $q) => $q->where('status', 'BLACKLISTED')
                )
            )
            ->badge(
                Mitra::query()
                    ->whereHas('kemitraans', fn (Builder $q) => $q->where('status', 'BLACKLISTED'))
                    ->count()
            )
            ->icon('heroicon-o-no-symbol');

        return $tabs;
    }
}
