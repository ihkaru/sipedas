<?php

namespace App\Filament\Resources\Sipancong\PengajuanResource\Pages;

use App\Filament\Resources\Sipancong\PengajuanResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;

class ListPengajuans extends ListRecords
{
    protected static string $resource = PengajuanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
            Action::make("ajukan")
                ->label("Ajukan Pembayaran")
                ->icon("heroicon-o-paper-airplane")
                ->modalHeading("Pengajuan Pembayaran")
                ->form([
                    Textarea::make("uraian_pengajuan")
                        ->label("Uraian Pengajuan")
                        ->required(),
                    Textarea::make("sub_fungsi")
                        ->label("Sub Fungsi")
                        ->required(),
                    TextInput::make("nomor_form_pembayaran")
                        ->label("Nomor Form Pembayaran (FP)")
                        ->required(),
                    TextInput::make("nomor_detail_fa")
                        ->label("Nomor Detail FA")
                        ->required(),
                    TextInput::make("nominal_pengajuan")
                        ->label("Nominal Pengajuan")
                        ->required()
                        ->numeric(),
                    TextInput::make("link_folder_dokumen")
                        ->label("Link Folder Dokumen")
                        ->helperText("Pastikan akses sudah folder sudah terbuka untuk edit!")
                        ->required(),
                ]),
        ];
    }
}
