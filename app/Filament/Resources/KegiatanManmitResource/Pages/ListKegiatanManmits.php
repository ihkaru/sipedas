<?php

namespace App\Filament\Resources\KegiatanManmitResource\Pages;

use App\Filament\Resources\KegiatanManmitResource;
use App\Imports\KegiatanManmitFromUploadImport; // Menggunakan nama kelas yang sudah diperbaiki
use App\Services\KegiatanManmitService;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage; // <-- TAMBAHKAN USE STATEMENT INI

use Exception;

class ListKegiatanManmits extends ListRecords
{
    protected static string $resource = KegiatanManmitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // 1. Tombol standar untuk membuat record baru
            Actions\CreateAction::make()
                ->label("Tambah Kegiatan Manajemen Mitra"),

            // 2. Tombol untuk impor dari JSON (tetap ada sesuai permintaan)
            Actions\Action::make('importFromJson')
                ->hidden(function () {
                    return !auth()->user()->hasRole("super_admin");
                })
                ->label('Impor dari JSON')
                ->color('warning')
                ->icon('heroicon-o-code-bracket') // Ikon yang lebih sesuai untuk JSON
                ->form([
                    Textarea::make('json_data')
                        ->label('Data JSON')
                        ->placeholder('Salin dan tempel teks JSON di sini...')
                        ->required()
                        ->rows(15),
                ])
                ->action(function (array $data) {
                    try {
                        $importedCount = KegiatanManmitService::importFromJsonString($data['json_data']);
                        Notification::make()
                            ->title('Impor JSON Berhasil')
                            ->body("Berhasil mengimpor atau memperbarui {$importedCount} kegiatan.")
                            ->success()
                            ->send();
                    } catch (Exception $e) {
                        Notification::make()
                            ->title('Impor JSON Gagal')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            // 3. Tombol untuk mengelompokkan aksi Excel
            Actions\ActionGroup::make([
                // 3a. Tombol untuk mengimpor dari file Excel
                Actions\Action::make('importFromExcel')
                    ->label('Impor dari Excel')
                    ->icon('heroicon-o-document-arrow-up')
                    ->form([
                        FileUpload::make('attachment')
                            ->label('File Excel')
                            ->required()
                            ->acceptedFileTypes(['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                            ->disk('local')
                    ])
                    ->action(function (array $data) {
                        try {
                            // --- PERUBAHAN UTAMA DI SINI ---

                            // 1. $data['attachment'] sekarang adalah string path, contoh: 'livewire-tmp/xyz.xlsx'
                            $filePath = $data['attachment'];

                            // 2. Gunakan Storage facade untuk mendapatkan path absolut file tersebut di server.
                            $absolutePath = Storage::disk('local')->path($filePath);

                            // 3. Berikan path absolut tersebut ke Maatwebsite Excel.
                            Excel::import(new KegiatanManmitFromUploadImport, $absolutePath);

                            Notification::make()
                                ->title('Impor Excel Berhasil')
                                ->body('Data kegiatan berhasil diimpor atau diperbarui.')
                                ->success()
                                ->send();
                        } catch (Exception $e) {
                            Notification::make()
                                ->title('Impor Excel Gagal')
                                ->body('Terjadi kesalahan saat mengimpor file: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                // 3b. Tombol untuk mengunduh template Excel
                Actions\Action::make('downloadTemplate')
                    ->label('Unduh Template Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(route('download.template.kegiatan-manmit'))
                    ->openUrlInNewTab(),
            ])
                ->hidden(function () {
                    return !auth()->user()->hasRole("super_admin");
                })
                ->label('Aksi Excel')
                ->icon('heroicon-m-table-cells')
                ->color('success')
                ->button(),

        ];
    }
}
