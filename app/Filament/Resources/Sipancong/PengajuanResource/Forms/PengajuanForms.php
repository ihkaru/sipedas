<?php

namespace App\Filament\Resources\Sipancong\PengajuanResource\Forms;

use App\Models\Pegawai;
use App\Models\Sipancong\JenisDokumen;
use App\Models\Sipancong\StatusPembayaran;
use App\Models\Sipancong\StatusPengajuan;
use App\Models\Sipancong\Subfungsi;
use App\Supports\SipancongConstants as Constants;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Illuminate\Support\HtmlString;

class PengajuanForms
{
    /**
     * Form untuk Aksi "Buat Pengajuan" atau "Perbaiki Pengajuan".
     * Semua field bisa diedit.
     */
    public static function pengajuanPembayaran(): array
    {
        return [
            Fieldset::make('Informasi Utama Pengajuan')
                ->schema([
                    Grid::make(2)->schema([
                        Select::make("nip_penanggung_jawab")
                            ->label("Penanggung Jawab")
                            // ->relationship('penanggungJawab', 'nama')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make("sub_fungsi_id")
                            ->label("Sub Fungsi")
                            ->options(Subfungsi::pluck('nama', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),
                    Textarea::make("uraian_pengajuan")
                        ->label("Uraian Pengajuan")
                        ->required(),
                    Grid::make(2)->schema([
                        TextInput::make("nomor_form_pembayaran")
                            ->label("Nomor Form Pembayaran (FP)")
                            ->helperText("Pisahkan dengan koma jika lebih dari satu.")
                            ->required(),
                        TextInput::make("nomor_detail_fa")
                            ->label("Nomor Detail FA")
                            ->helperText("Pisahkan dengan koma jika lebih dari satu.")
                            ->required(),
                    ]),
                    Grid::make(2)->schema([
                        TextInput::make("nominal_pengajuan")
                            ->label("Nominal Pengajuan")
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),
                        DatePicker::make("tanggal_pengajuan")
                            ->label("Tanggal Pengajuan")
                            ->helperText("Sesuaikan dengan tanggal di dokumen.")
                            ->required(),
                    ]),
                    TextInput::make("link_folder_dokumen")
                        ->label("Link Folder Bukti Dukung")
                        ->url()
                        ->helperText(new HtmlString("Pastikan akses folder sudah <strong>terbuka untuk edit</strong>!"))
                        ->required(),
                ])
        ];
    }

    /**
     * Form untuk Aksi "Tanggapan Pengaju" setelah dokumen ditolak.
     * Hanya field tanggapan yang bisa diisi.
     */
    public static function tanggapanPengaju(): array
    {
        return [
            Fieldset::make('Detail Pengajuan (Read-only)')
                ->schema([
                    Textarea::make('uraian_pengajuan')->readOnly(),
                    TextInput::make("nominal_pengajuan")->numeric()->prefix('Rp')->readOnly(),
                ]),

            // Menampilkan catatan dan kolom tanggapan hanya jika ada catatan dari pemeriksa tersebut.
            Fieldset::make('Tanggapan untuk PPK')
                // ->relationship('statusPengajuanPpk') // <-- HAPUS BARIS INI
                ->hidden(fn(Get $get) => $get('status_pengajuan_ppk_id') != Constants::STATUS_DITOLAK)
                ->schema([
                    Textarea::make("catatan_ppk")->label("Catatan dari PPK")->readOnly(),
                    Textarea::make("tanggapan_pengaju_ke_ppk")->label("Tanggapan Anda untuk PPK")->required(),
                ]),

            Fieldset::make('Tanggapan untuk PPSPM')
                // ->relationship('statusPengajuanPpspm') // <-- HAPUS BARIS INI
                ->hidden(fn(Get $get) => $get('status_pengajuan_ppspm_id') != Constants::STATUS_DITOLAK)
                ->schema([
                    Textarea::make("catatan_ppspm")->label("Catatan dari PPSPM")->readOnly(),
                    Textarea::make("tanggapan_pengaju_ke_ppspm")->label("Tanggapan Anda untuk PPSPM")->required(),
                ]),

            Fieldset::make('Tanggapan untuk Bendahara')
                // ->relationship('statusPengajuanBendahara') // <-- HAPUS BARIS INI
                ->hidden(fn(Get $get) => $get('status_pengajuan_bendahara_id') != Constants::STATUS_DITOLAK)
                ->schema([
                    Textarea::make("catatan_bendahara")->label("Catatan dari Bendahara")->readOnly(),
                    Textarea::make("tanggapan_pengaju_ke_bendahara")->label("Tanggapan Anda untuk Bendahara")->required(),
                ]),
        ];
    }

    /**
     * Form untuk Aksi "Pemeriksaan PPK".
     */
    public static function pemeriksaanPpk(): array
    {
        return [
            Fieldset::make('Detail Pengajuan (Read-only)')
                ->schema(self::getReadOnlyInfoFields()),

            Fieldset::make('Tanggapan dari Pengaju (jika ada)')
                ->hidden(fn(Get $get) => is_null($get('tanggapan_pengaju_ke_ppk')))
                ->schema([
                    Textarea::make("tanggapan_pengaju_ke_ppk")->label("Tanggapan Pengaju")->readOnly(),
                ]),

            Fieldset::make('Hasil Pemeriksaan PPK')
                ->schema([
                    Select::make("status_pengajuan_ppk_id")
                        ->label("Status Pemeriksaan")
                        ->options(StatusPengajuan::where('id', '!=', Constants::STATUS_DIAJUKAN)->pluck("nama", "id"))
                        ->live()
                        ->required(),
                    Textarea::make("catatan_ppk")
                        ->label("Catatan")
                        ->helperText("Wajib diisi jika status Ditolak atau Disetujui dengan Catatan.")
                        ->required(fn(Get $get) => in_array($get('status_pengajuan_ppk_id'), [Constants::STATUS_DITOLAK, Constants::STATUS_DISETUJUI_DENGAN_CATATAN])),
                ]),
        ];
    }

    /**
     * Form untuk Aksi "Pemeriksaan PPSPM".
     */
    public static function pemeriksaanPpspm(): array
    {
        return [
            Fieldset::make('Detail Pengajuan & Hasil Pemeriksaan Sebelumnya (Read-only)')
                ->schema(array_merge(self::getReadOnlyInfoFields(), [
                    Textarea::make('catatan_ppk')->label('Catatan PPK')->readOnly(),
                ])),

            Fieldset::make('Tanggapan dari Pengaju (jika ada)')
                ->hidden(fn(Get $get) => is_null($get('tanggapan_pengaju_ke_ppspm')))
                ->schema([
                    Textarea::make("tanggapan_pengaju_ke_ppspm")->label("Tanggapan Pengaju")->readOnly(),
                ]),

            Fieldset::make('Hasil Pemeriksaan PPSPM')
                ->schema([
                    Select::make("status_pengajuan_ppspm_id")
                        ->label("Status Pemeriksaan")
                        ->options(StatusPengajuan::where('id', '!=', Constants::STATUS_DIAJUKAN)->pluck("nama", "id"))
                        ->live()
                        ->required(),
                    Textarea::make("catatan_ppspm")
                        ->label("Catatan")
                        ->helperText("Wajib diisi jika status Ditolak atau Disetujui dengan Catatan.")
                        ->required(fn(Get $get) => in_array($get('status_pengajuan_ppspm_id'), [Constants::STATUS_DITOLAK, Constants::STATUS_DISETUJUI_DENGAN_CATATAN])),
                ]),
        ];
    }

    /**
     * Form untuk Aksi "Verifikasi Bendahara".
     */
    public static function pemeriksaanBendahara(): array
    {
        return [
            Fieldset::make('Detail Pengajuan & Hasil Pemeriksaan Sebelumnya (Read-only)')
                ->schema(array_merge(self::getReadOnlyInfoFields(), [
                    Textarea::make('catatan_ppk')->label('Catatan PPK')->readOnly(),
                    Textarea::make('catatan_ppspm')->label('Catatan PPSPM')->readOnly(),
                ])),

            Fieldset::make('Tanggapan dari Pengaju (jika ada)')
                ->hidden(fn(Get $get) => is_null($get('tanggapan_pengaju_ke_bendahara')))
                ->schema([
                    Textarea::make("tanggapan_pengaju_ke_bendahara")->label("Tanggapan Pengaju")->readOnly(),
                ]),

            Fieldset::make('Hasil Verifikasi Bendahara')
                ->schema([
                    Select::make("status_pengajuan_bendahara_id")
                        ->label("Status Verifikasi")
                        ->options(StatusPengajuan::where('id', '!=', Constants::STATUS_DIAJUKAN)->pluck("nama", "id"))
                        ->live()
                        ->required(),
                    Textarea::make("catatan_bendahara")
                        ->label("Catatan")
                        ->helperText("Wajib diisi jika status Ditolak atau Disetujui dengan Catatan.")
                        ->required(fn(Get $get) => in_array($get('status_pengajuan_bendahara_id'), [Constants::STATUS_DITOLAK, Constants::STATUS_DISETUJUI_DENGAN_CATATAN])),
                ]),
        ];
    }

    /**
     * Form untuk Aksi "Proses Pembayaran" oleh Bendahara.
     */
    public static function pemrosesanBendahara(): array
    {
        return [
            Fieldset::make('Detail Pengajuan (Read-only)')
                ->schema(self::getReadOnlyInfoFields()),

            Fieldset::make('Proses Pembayaran')
                ->schema([
                    Select::make("status_pembayaran_id")
                        ->label("Status Pembayaran")
                        ->options(StatusPembayaran::pluck("nama", "id"))
                        ->live()
                        ->searchable()
                        ->required(),
                    Grid::make(2)->schema([
                        TextInput::make("nominal_dibayarkan")
                            ->label("Nominal Dibayarkan")
                            ->numeric()->prefix('Rp')
                            ->required(fn(Get $get) => Constants::isSelesaiDibayar($get('status_pembayaran_id')))
                            ->visible(fn(Get $get) => !is_null($get('status_pembayaran_id')) && $get('status_pembayaran_id') != Constants::PEMBAYARAN_BELUM_DOK_FISIK),
                        TextInput::make("nominal_dikembalikan")
                            ->label("Nominal Dikembalikan")
                            ->numeric()->prefix('Rp')
                            ->required(fn(Get $get) => Constants::isSelesaiDibayar($get('status_pembayaran_id')))
                            ->visible(fn(Get $get) => !is_null($get('status_pembayaran_id')) && $get('status_pembayaran_id') != Constants::PEMBAYARAN_BELUM_DOK_FISIK),
                    ]),
                    Grid::make(2)->schema([
                        Select::make("jenis_dokumen_id")
                            ->label("Jenis Dokumen Dasar")
                            ->options(JenisDokumen::pluck('nama', 'id'))
                            ->required(fn(Get $get) => in_array($get('status_pembayaran_id'), [Constants::PEMBAYARAN_PROSES_CATAT_LS, Constants::PEMBAYARAN_PROSES_CATAT_SPBY]))
                            ->visible(fn(Get $get) => in_array($get('status_pembayaran_id'), [Constants::PEMBAYARAN_PROSES_CATAT_LS, Constants::PEMBAYARAN_PROSES_CATAT_SPBY])),
                        TextInput::make("nomor_dokumen")
                            ->label("Nomor SPM / SPBY")
                            ->required(fn(Get $get) => in_array($get('status_pembayaran_id'), [Constants::PEMBAYARAN_PROSES_CATAT_LS, Constants::PEMBAYARAN_PROSES_CATAT_SPBY]))
                            ->visible(fn(Get $get) => in_array($get('status_pembayaran_id'), [Constants::PEMBAYARAN_PROSES_CATAT_LS, Constants::PEMBAYARAN_PROSES_CATAT_SPBY])),
                    ]),
                    DatePicker::make('tanggal_pembayaran')
                        ->label("Tanggal Pembayaran/Cair")
                        ->required(fn(Get $get) => Constants::isSelesaiDibayar($get('status_pembayaran_id')))
                        ->visible(fn(Get $get) => Constants::isSelesaiDibayar($get('status_pembayaran_id'))),
                ]),
        ];
    }

    /**
     * Helper untuk field info yang selalu read-only di form pemeriksaan.
     */
    private static function getReadOnlyInfoFields(): array
    {
        return [
            Textarea::make('uraian_pengajuan')->label('Uraian')->readOnly(),
            TextInput::make("nominal_pengajuan")->label('Nominal')->numeric()->prefix('Rp')->readOnly(),
            TextInput::make("link_folder_dokumen")
                ->label("Link Folder Dokumen")
                ->readOnly()
                ->suffixAction(
                    Action::make("bukaFolder")
                        ->icon("heroicon-m-link")
                        ->url(fn(?Pengajuan $record): string => $record?->link_folder_dokumen ?? '#')
                        ->openUrlInNewTab()
                ),
        ];
    }

    /**
     * Form lengkap untuk halaman Edit Admin.
     */
    public static function fullForm(): array
    {
        return [
            // Gabungkan semua field dari form lain di sini jika perlu.
            // Untuk saat ini kita biarkan kosong agar memakai form default dari Resource.
            // Atau bisa diisi dengan skema yang ada di PengajuanResource::form()
        ];
    }
}
