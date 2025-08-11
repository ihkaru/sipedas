<?php

namespace App\Filament\Resources\AlokasiHonorResource\Pages;

use App\Filament\Resources\AlokasiHonorResource;
use App\Imports\AlokasiHonorImport;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class ListAlokasiHonors extends ListRecords
{
    protected static string $resource = AlokasiHonorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ActionGroup::make([
                Actions\Action::make('importAlokasi')
                    ->label('Impor Alokasi dari Excel')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->form([
                        FileUpload::make('attachment')
                            ->label('File Excel (.xlsx)')
                            ->required()
                            ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                            ->disk('local')
                    ])
                    ->action(function (array $data) {
                        // --- PERUBAHAN LOGIKA DIMULAI DI SINI ---
                        $import = new AlokasiHonorImport;

                        try {
                            Excel::import($import, $data['attachment'], 'local');

                            // Cek apakah ada kegagalan validasi yang terkumpul
                            $failures = $import->getFailures();

                            if (!empty($failures)) {
                                $errorMessages = [];
                                foreach ($failures as $failure) {
                                    $errors = implode(', ', $failure->errors());
                                    $errorMessages[] = "Baris {$failure->row()}: {$errors}";
                                }
                                throw new \Exception(implode('<br>', $errorMessages));
                            }

                            // Hanya tampilkan sukses jika tidak ada kegagalan
                            Notification::make()
                                ->title('Impor Berhasil')
                                ->body('Semua data alokasi honor berhasil diimpor.')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            // Tangkap exception yang kita lempar secara manual dan yang lainnya
                            Notification::make()
                                ->title('Impor Gagal: Terdapat Error')
                                ->body($e->getMessage())
                                ->danger()
                                ->persistent()
                                ->send();
                        }
                    }),

                Actions\Action::make('downloadTemplate')
                    ->label('Unduh Template Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(route('download.template.alokasi-honor'))
                    ->openUrlInNewTab(),
            ])
                ->label('Aksi Excel')
                ->icon('heroicon-m-table-cells')
                ->color('success')
                ->button(),
        ];
    }
}
