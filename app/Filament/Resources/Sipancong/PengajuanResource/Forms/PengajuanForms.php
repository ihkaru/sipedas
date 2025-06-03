<?php

namespace App\Filament\Resources\Sipancong\PengajuanResource\Forms;

use App\Models\Pegawai;
use App\Models\Sipancong\JenisDokumen;
use App\Models\Sipancong\Pengajuan;
use App\Models\Sipancong\PosisiDokumen;
use App\Models\Sipancong\StatusPembayaran;
use App\Models\Sipancong\StatusPengajuan;
use App\Models\Sipancong\Subfungsi;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Illuminate\Support\HtmlString;

class PengajuanForms
{
    public static function pengajuanPembayaran()
    {
        return [
            Select::make("nip_penanggung_jawab")
                ->label("Penanggung Jawab")
                ->searchable()
                ->options(Pegawai::pluck("nama", "nip"))
                ->required(),
            DatePicker::make("tanggal_pengajuan")
                ->label("Tanggal Pengajuan")
                ->helperText("Tanggal Pengajuan Harus Sama atau Lebih Akhir daripada Tanggal Penyelesaian Kegiatan di Dokumen"),
            Select::make("sub_fungsi_id")
                ->options(Subfungsi::pluck('nama', 'id'))
                ->preload()
                ->label("Sub Fungsi")
                ->required(),
            Textarea::make("uraian_pengajuan")
                ->label("Uraian Pengajuan")
                ->required(),
            TextInput::make("link_folder_dokumen")
                ->helperText(new HtmlString("Pastikan akses sudah folder sudah terbuka untuk edit!"))
                ->label("Link Folder Dokumen")
                ->helperText("Pastikan akses sudah folder sudah terbuka untuk edit!")
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

            // Select::make("posisi_dokumen_id")
            //     ->label("Posisi Dokumen Fisik")
            //     ->options(PosisiDokumen::whereIn('nama', ["Di PPK"])->pluck("nama", "id"))
            //     ->helperText("Jika setelah pengajuan ini Anda memberikan dokumen fisik ke PPK, maka isikan PPK")
            //     ->required(),
        ];
    }
    public static function tanggapanPengaju()
    {
        return [
            Select::make("nip_penanggung_jawab")
                ->label("Penanggung Jawab")
                ->options(Pegawai::pluck("nama", "nip"))
                ->disabled(),
            Textarea::make('uraian_pengajuan')
                ->readOnly(),
            TextInput::make("nominal_pengajuan")
                ->label("Nominal Pengajuan")
                ->readOnly(),
            Select::make("posisi_dokumen_id")
                ->label("Posisi Dokumen Fisik")
                ->options(PosisiDokumen::pluck("nama", "id"))
                ->disabled(),
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
            Textarea::make("catatan_ppk")
                ->label("Catatan PPK")
                ->readOnly(),
            Textarea::make("tanggapan_pengaju_ke_ppk")
                ->label("Tanggapan dari Pengaju ke PPK"),
            Textarea::make("catatan_bendahara")
                ->label("Catatan Bendahara")
                ->readOnly(),
            Textarea::make("tanggapan_pengaju_ke_bendahara")
                ->label("Tanggapan dari Pengaju ke Bendahara"),
            Textarea::make("catatan_ppspm")
                ->label("Catatan PPSPM")
                ->readOnly(),
            Textarea::make("tanggapan_pengaju_ke_ppspm")
                ->label("Tanggapan dari Pengaju ke PPSPM")
        ];
    }
    public static function pemeriksaanPpk()
    {
        return [
            Select::make("nip_penanggung_jawab")
                ->label("Penanggung Jawab")
                ->options(Pegawai::pluck("nama", "nip"))
                ->disabled(),
            Textarea::make("uraian_pengajuan")
                ->label("Uraian Pengajuan")
                ->readOnly(),
            TextInput::make("nominal_pengajuan")
                ->label("Nominal Pengajuan")
                ->readOnly(),
            TextInput::make("link_folder_dokumen")
                ->hintAction(Action::make("bukaFolder")
                    ->hidden(fn(Pengajuan $record): bool => !($record->link_folder_dokumen))
                    ->icon("heroicon-m-link")
                    ->url(fn(Pengajuan $record): string => $record->link_folder_dokumen)
                    ->openUrlInNewTab())
                ->label("Link Folder Dokumen")
                ->readOnly(),
            Select::make("status_pengajuan_ppk_id")
                ->options(StatusPengajuan::whereNot('nama', 'Diajukan')->pluck("nama", "id"))
                ->label("Hasil Pemeriksaan PPK")
                ->required(),
            Textarea::make("catatan_ppk")
                ->label("Catatan"),
            Textarea::make("tanggapan_pengaju_ke_ppk")
                ->label("Tanggapan dari Pengaju ke PPK")
                ->readOnly(),
            // Select::make("posisi_dokumen_id")
            //     ->label("Posisi Dokumen Fisik")
            //     ->options(PosisiDokumen::whereIn("nama", ["Di PPK", "Di Bendahara"])->pluck("nama", "id"))
            //     ->helperText("Jika setelah pengajuan ini Anda memberikan dokumen fisik ke Bendahara, maka isikan Bendahara")
            //     ->required(),
        ];
    }
    public static function pemeriksaanBendahara()
    {
        return [
            Select::make("nip_penanggung_jawab")
                ->label("Penanggung Jawab")
                ->options(Pegawai::pluck("nama", "nip"))
                ->disabled(),
            Textarea::make("uraian_pengajuan")
                ->label("Uraian Pengajuan")
                ->readOnly(),
            TextInput::make("nominal_pengajuan")
                ->label("Nominal Pengajuan")
                ->readOnly(),
            TextInput::make("link_folder_dokumen")
                ->hintAction(
                    Action::make("bukaFolder")
                        ->hidden(fn(Pengajuan $record): bool => !($record->link_folder_dokumen))
                        ->icon("heroicon-m-link")
                        ->url(fn(Pengajuan $record): string => $record->link_folder_dokumen)
                        ->openUrlInNewTab()
                )
                ->label("Link Folder Dokumen")
                ->readOnly()
                ->helperText("Jangan lupa menambahkan bukti dukung yang diperlukan di folder ini ya!"),
            Select::make("status_pengajuan_ppk_id")
                ->options(StatusPengajuan::whereNot('nama', 'Diajukan')->pluck("nama", "id"))
                ->disabled()
                ->label("Hasil Pemeriksaan PPK"),
            Select::make("status_pengajuan_bendahara_id")
                ->options(StatusPengajuan::whereNot('nama', 'Diajukan')->pluck("nama", "id"))
                ->label("Hasil Pemeriksaan Bendahara")
                ->required(),
            Textarea::make("catatan_ppk")
                ->label("Catatan PPK")
                ->readOnly(),
            Textarea::make("tanggapan_pengaju_ke_ppk")
                ->label("Tanggapan dari Pengaju ke PPK")
                ->readOnly(),
            Textarea::make("catatan_bendahara")
                ->label("Catatan Bendahara"),
            Textarea::make("tanggapan_pengaju_ke_bendahara")
                ->label("Tanggapan dari Pengaju ke Bendahara")
                ->readOnly(),
            // Select::make("posisi_dokumen_id")
            //     ->label("Posisi Dokumen Fisik")
            //     ->options(PosisiDokumen::whereIn("nama", ["Di PPK", "Di Bendahara", "Di PPSPM"])->pluck("nama", "id"))
            //     ->helperText("Jika setelah pengajuan ini Anda memberikan dokumen fisik ke PPSPM, maka isikan PPSPM. Pilih 'Di PPK' untuk mengembalikan ke PPK")
            //     ->required(),
        ];
    }
    public static function pemeriksaanPpspm()
    {
        return [
            Select::make("nip_penanggung_jawab")
                ->label("Penanggung Jawab")
                ->options(Pegawai::pluck("nama", "nip"))
                ->disabled(),
            Textarea::make("uraian_pengajuan")
                ->label("Uraian Pengajuan")
                ->readOnly(),
            TextInput::make("nominal_pengajuan")
                ->label("Nominal Pengajuan")
                ->readOnly(),
            TextInput::make("link_folder_dokumen")
                ->hintAction(
                    Action::make("bukaFolder")
                        ->hidden(fn(Pengajuan $record): bool => !($record->link_folder_dokumen))
                        ->icon("heroicon-m-link")
                        ->url(fn(Pengajuan $record): string => $record->link_folder_dokumen)
                        ->openUrlInNewTab()
                )
                ->label("Link Folder Dokumen")
                ->readOnly()
                ->helperText("Anda dapat menimpa (replace) file yang sudah ditandatangani di folder ini"),
            Select::make("status_pengajuan_ppk_id")
                ->options(StatusPengajuan::whereNot('nama', 'Diajukan')->pluck("nama", "id"))
                ->disabled()
                ->label("Hasil Pemeriksaan PPK"),
            Select::make("status_pengajuan_bendahara_id")
                ->options(StatusPengajuan::whereNot('nama', 'Diajukan')->pluck("nama", "id"))
                ->label("Hasil Pemeriksaan Bendahara")
                ->disabled(),
            Select::make("status_pengajuan_ppspm_id")
                ->options(StatusPengajuan::whereNot('nama', 'Diajukan')->pluck("nama", "id"))
                ->helperText(new HtmlString("Pastikan Kepala Satker <strong>telah menandatangani</strong> dokumen fisik atau softfile sebelum menyetujui!"))
                ->label("Hasil Pemeriksaan PPSPM")
                ->required(),
            Textarea::make("catatan_ppk")
                ->label("Catatan PPK")
                ->readOnly(),
            Textarea::make("tanggapan_pengaju_ke_ppk")
                ->label("Tanggapan dari Pengaju ke PPK")
                ->readOnly(),
            Textarea::make("catatan_bendahara")
                ->label("Catatan Bendahara")
                ->readOnly(),
            Textarea::make("tanggapan_pengaju_ke_bendahara")
                ->label("Tanggapan dari Pengaju ke Bendahara")
                ->readOnly(),
            Textarea::make("catatan_ppspm")
                ->label("Catatan PPSPM"),
            Textarea::make("tanggapan_pengaju_ke_ppspm")
                ->label("Tanggapan dari Pengaju ke PPSPM")
                ->readOnly(),
            // Select::make("posisi_dokumen_id")
            //     ->label("Posisi Dokumen Fisik")
            //     ->options(PosisiDokumen::whereIn("nama", ["Di Bendahara", "Di PPSPM"])->pluck("nama", "id"))
            //     ->helperText("Jika setelah pengajuan ini Anda memberikan dokumen fisik ke Bendahara, maka isikan Bendahara")
            //     ->required(),
        ];
    }
    public static function pemrosesanBendahara()
    {
        return [
            Select::make("nip_penanggung_jawab")
                ->label("Penanggung Jawab")
                ->options(Pegawai::pluck("nama", "nip"))
                ->disabled(),
            Textarea::make("uraian_pengajuan")
                ->label("Uraian Pengajuan")
                ->readOnly(),
            TextInput::make("nominal_pengajuan")
                ->label("Nominal Pengajuan")
                ->readOnly(),
            TextInput::make("link_folder_dokumen")
                ->suffixAction(
                    Action::make("bukaFolder")
                        ->hidden(fn(Pengajuan $record): bool => !($record->link_folder_dokumen))
                        ->icon("heroicon-m-link")
                        ->url(fn(Pengajuan $record): string => $record->link_folder_dokumen)
                        ->openUrlInNewTab()
                )
                ->label("Link Folder Dokumen")
                ->readOnly()
                ->helperText("Anda dapat menimpa (replace) file yang sudah ditandatangani di folder ini"),
            Select::make("status_pengajuan_ppk_id")
                ->options(StatusPengajuan::whereNot('nama', 'Diajukan')->pluck("nama", "id"))
                ->disabled()
                ->label("Hasil Pemeriksaan PPK"),
            Select::make("status_pengajuan_bendahara_id")
                ->options(StatusPengajuan::whereNot('nama', 'Diajukan')->pluck("nama", "id"))
                ->label("Hasil Pemeriksaan Bendahara")
                ->disabled(),
            Select::make("status_pengajuan_ppspm_id")
                ->options(StatusPengajuan::whereNot('nama', 'Diajukan')->pluck("nama", "id"))
                ->label("Hasil Pemeriksaan PPSPM")
                ->disabled(),
            Select::make("status_pembayaran_id")
                ->options(StatusPembayaran::pluck("nama", "id"))
                ->live()
                ->label("Hasil Pemrosesan")
                ->required(),
            TextInput::make("nominal_dibayarkan")
                ->label("Nominal yang Dibayarkan")
                ->hidden(function (Get $get) {
                    return !collect([1, 2, 4, 5, 6, 7])->contains($get('status_pembayaran_id'));
                })
                ->required(function (Get $get) {
                    return collect([1, 2, 4, 5, 6, 7])->contains($get('status_pembayaran_id'));
                })
                ->numeric(),
            TextInput::make("nominal_dikembalikan")
                ->label("Nominal yang Dikembalikan")
                ->numeric()
                ->hidden(function (Get $get) {
                    return !collect([1, 2, 4, 5, 6, 7])->contains($get('status_pembayaran_id'));
                })
                ->required(function (Get $get) {
                    return collect([1, 2, 4, 5, 6, 7])->contains($get('status_pembayaran_id'));
                })
                ->required(),
            DatePicker::make('tanggal_pembayaran')
                ->label("Tanggal Pembayaran")
                ->hidden(function (Get $get) {
                    return !collect([1, 2, 5, 7])->contains($get('status_pembayaran_id'));
                })
                ->required(function (Get $get) {
                    return collect([1, 2, 5, 7])->contains($get('status_pembayaran_id'));
                }),
            Select::make("jenis_dokumen_id")
                ->label("Jenis Dokumen (SPM/SPBY)")
                ->options(JenisDokumen::pluck('nama', 'id'))
                ->hidden(function (Get $get) {
                    return !collect([1, 2, 4, 5, 6])->contains($get('status_pembayaran_id'));
                })
                ->required(function (Get $get) {
                    return collect([1, 2, 4, 5, 6])->contains($get('status_pembayaran_id'));
                }),
            TextInput::make("nomor_dokumen")
                ->label("Nomor Dokumen (SPM/SPBY)")
                ->hidden(function (Get $get) {
                    return !collect([1, 2, 4, 5, 6])->contains($get('status_pembayaran_id'));
                })
                ->required(function (Get $get) {
                    return collect([1, 2, 4, 5, 6])->contains($get('status_pembayaran_id'));
                }),

        ];
    }
}
