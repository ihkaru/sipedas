<?php

namespace App\Filament\Resources\Sipancong\PengajuanResource\Forms;

use App\Models\Sipancong\Pengajuan;
use App\Models\Sipancong\PosisiDokumen;
use App\Models\Sipancong\StatusPengajuan;
use App\Models\Sipancong\Subfungsi;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class PengajuanForms
{

    public static function pengajuanPembayaran()
    {
        return [
            Textarea::make("uraian_pengajuan")
                ->label("Uraian Pengajuan")
                ->required(),
            Select::make("sub_fungsi_id")
                ->options(Subfungsi::pluck('nama', 'id'))
                ->preload()
                ->label("Sub Fungsi")
                ->required(),
            TextInput::make("nomor_form_pembayaran")
                ->label("Nomor Form Pembayaran (FP)")
                ->helperText("Bisa lebih dari satu nomor. Gunakan koma sebagai pemisah nomor. Contoh: 12,13,140")
                ->required(),
            TextInput::make("nomor_detail_fa")
                ->label("Nomor Detail FA")
                ->helperText("Bisa lebih dari satu nomor. Gunakan koma sebagai pemisah nomor. Contoh: 12,13,140")
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
        ];
    }
    public static function tanggapanPengaju()
    {
        return [
            Textarea::make('uraian_pengajuan')
                ->readOnly(),
            Select::make('status_pengajuan_ppk_id')
                ->label("Status Pengajuan di PPK")
                ->options(StatusPengajuan::pluck('nama', 'id'))
                ->disabled(),
            Select::make('status_pengajuan_ppspm_id')
                ->label("Status Pengajuan di PPSPM")
                ->options(StatusPengajuan::pluck('nama', 'id'))
                ->disabled(),
            Select::make('status_pengajuan_bendahara_id')
                ->label("Status Pengajuan di Bendahara")
                ->options(StatusPengajuan::pluck('nama', 'id'))
                ->disabled(),
            Textarea::make('tanggapan_pengaju_ke_ppk')
                ->label("Tanggapan Pengaju ke PPK")
                ->columnSpanFull(),
            Textarea::make('tanggapan_pengaju_ke_ppspm')
                ->label("Tanggapan Pengaju ke PPSPM")
                ->columnSpanFull(),
            Textarea::make('tanggapan_pengaju_ke_bendahara')
                ->label("Tanggapan Pengaju ke Bendahara")
                ->columnSpanFull(),
        ];
    }
    public static function pemeriksaanPpk()
    {
        return [
            Textarea::make("uraian_pengajuan")
                ->label("Uraian Pengajuan")
                ->required(),
            TextInput::make("link_folder_dokumen")
                ->hintAction(
                    Action::make("bukaFolder")
                        ->icon("heroicon-m-link")
                        ->url(fn(Pengajuan $record): string => $record->link_folder_dokumen)
                        ->openUrlInNewTab()
                )
                ->label("Link Folder Dokumen")
                ->readOnly()
                ->helperText("Anda dapat menimpa (replace) file yang sudah ditandatangani di folder ini")
                ->required(),
            Select::make("status_pengajuan_ppk_id")
                ->options(StatusPengajuan::whereNot('nama', 'Diajukan')->pluck("nama", "id"))
                ->label("Hasil Pemeriksaan PPK")
                ->required(),
            Textarea::make("catatan_ppk")
                ->label("Catatan"),
            Textarea::make("tanggapan_pengaju_ke_ppk")
                ->label("Tanggapan dari Pengaju ke PPK")
                ->readOnly()
        ];
    }
}
