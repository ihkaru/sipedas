<?php

namespace App\Filament\Resources\MitraResource\Pages;

use App\Filament\Resources\MitraResource;
use App\Imports\MitraImport;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;
use Exception;
use Filament\Resources\Components\Tab;
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

            Actions\ActionGroup::make([
                Actions\Action::make('importMitra')
                    ->label('Impor Mitra')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->form([
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
                        FileUpload::make('attachment')
                            ->label('File Excel (.xlsx)')
                            ->required()->disk('local')
                            ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']),
                    ])
                    ->action(function (array $data) {
                        try {
                            // --- PERUBAHAN UTAMA DI SINI ---

                            // 1. Dapatkan path relatif file dari disk yang ditentukan
                            $filePath = $data['attachment'];

                            // 2. Buat instance Importer dengan melemparkan data tahun dan status
                            $importer = new MitraImport($data['tahun'], $data['status']);

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
        $currentYear = date('Y');

        return [
            // Tab untuk menampilkan semua mitra tanpa filter
            'all' => Tab::make('Semua Mitra'),

            // Tab untuk tahun berjalan (dinamis)
            (string)$currentYear => Tab::make("Aktif {$currentYear}")
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->whereHas(
                        'kemitraans',
                        fn(Builder $q) =>
                        $q->where('tahun', $currentYear)->where('status', 'AKTIF')
                    )
                )
                ->badge(
                    \App\Models\Mitra::query()
                        ->whereHas('kemitraans', fn(Builder $q) => $q->where('tahun', $currentYear)->where('status', 'AKTIF'))
                        ->count()
                )
                ->icon('heroicon-o-calendar-days'),

            // Tab untuk tahun sebelumnya (dinamis)
            (string)($currentYear - 1) => Tab::make("Aktif " . ($currentYear - 1))
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->whereHas(
                        'kemitraans',
                        fn(Builder $q) =>
                        $q->where('tahun', ($currentYear - 1))->where('status', 'AKTIF')
                    )
                )
                ->badge(
                    \App\Models\Mitra::query()
                        ->whereHas('kemitraans', fn(Builder $q) => $q->where('tahun', ($currentYear - 1))->where('status', 'AKTIF'))
                        ->count()
                )
                ->icon('heroicon-o-calendar'),

            // Tab khusus untuk melihat semua yang di-blacklist
            'blacklisted' => Tab::make('Blacklisted')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->whereHas(
                        'kemitraans',
                        fn(Builder $q) =>
                        $q->where('status', 'BLACKLISTED')
                    )
                )
                ->badge(
                    \App\Models\Mitra::query()
                        ->whereHas('kemitraans', fn(Builder $q) => $q->where('status', 'BLACKLISTED'))
                        ->count()
                )
                ->icon('heroicon-o-no-symbol'),
        ];
    }
}
