<?php

namespace App\Filament\Resources\Sipancong\PengajuanResource\Pages;

use App\Filament\Resources\Sipancong\PengajuanResource;
use App\Models\Sipancong\Pengajuan;
use App\Models\Sipancong\PosisiDokumen;
use App\Models\Sipancong\Subfungsi;
use App\Services\Sipancong\PengajuanServices;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
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
                    Select::make("sub_fungsi")
                        ->options(Subfungsi::pluck('nama', 'id'))
                        ->preload()
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
                    Select::make("posisi_dokumen_id")
                        ->label("Posisi Dokumen Fisik")
                        ->options(PosisiDokumen::pluck("nama", "id"))
                        ->helperText("Jika setelah pengajuan ini Anda memberikan dokumen fisik ke PPK, maka isikan PPK")
                        ->required(),
                ])
                ->action(function (array $data) {
                    PengajuanServices::ajukan($data);
                }),
        ];
    }
}
