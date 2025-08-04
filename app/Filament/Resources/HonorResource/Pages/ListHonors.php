<?php

namespace App\Filament\Resources\HonorResource\Pages;

use App\Filament\Resources\HonorResource;
use App\Imports\HonorImport;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Exception;

class ListHonors extends ListRecords
{
    protected static string $resource = HonorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Tombol standar untuk membuat honor baru
            Actions\CreateAction::make(),

            // Grup tombol untuk semua aksi yang berhubungan dengan Excel
            Actions\ActionGroup::make([
                // Aksi untuk mengimpor dari file Excel
                Actions\Action::make('importFromExcel')
                    ->label('Impor dari Excel')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->form([
                        FileUpload::make('attachment')
                            ->label('File Excel (.xlsx)')
                            ->required()
                            ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                            ->disk('local') // Simpan sementara di disk lokal
                    ])
                    ->action(function (array $data) {
                        try {
                            // Dapatkan path relatif dari file di disk 'local'
                            // Contoh: 'livewire-tmp/xyz.xlsx'
                            $filePath = $data['attachment'];

                            // Panggil Excel::import dengan 3 argumen:
                            // 1. Objek importer Anda
                            // 2. Path relatif file
                            // 3. Nama disk tempat file disimpan
                            Excel::import(new HonorImport, $filePath, 'local');

                            Notification::make()
                                ->title('Impor Excel Berhasil')
                                ->body('Data honor berhasil diimpor atau diperbarui.')
                                ->success()
                                ->send();
                        } catch (Exception $e) {
                            Notification::make()
                                ->title('Impor Excel Gagal')
                                ->body('Terjadi kesalahan: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                // Aksi untuk mengunduh template Excel
                Actions\Action::make('downloadTemplate')
                    ->label('Unduh Template Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    // Arahkan ke route yang sudah kita buat
                    ->url(route('download.template.honor'))
                    ->openUrlInNewTab(),
            ])
                ->label('Aksi Excel')
                ->icon('heroicon-m-table-cells')
                ->color('success')
                ->button(),
        ];
    }
}
