<?php

namespace App\Filament\Resources\Sipancong\PengajuanResource\Forms;

use App\Models\Pegawai;
use App\Models\Sipancong\JenisDokumen;
use App\Models\Sipancong\Pengajuan;
use App\Models\Sipancong\StatusPembayaran;
use App\Models\Sipancong\StatusPengajuan;
use App\Models\Sipancong\Subfungsi;
use App\Supports\SipancongConstants as Constants;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
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
                            ->relationship('penanggungJawab', 'nama')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make("sub_fungsi_id")
                            ->label("Sub Fungsi")
                            ->preload()
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
                            ->maxLength(50)
                            ->rules([
                                // regex Laravel untuk membatasi agar hanya [0-9], koma, dan spasi
                                'regex:/^[0-9, ]+$/',
                            ])
                            ->extraInputAttributes([
                                // pattern HTML5 agar browser menolak input lain saat submit
                                'pattern' => '^[0-9, ]+$',
                                // tooltip saat user hover memberikan petunjuk input
                                'title'   => 'Hanya boleh angka, koma, dan spasi',
                                // inputmode numeric mendorong keyboard angka di mobile app
                                'inputmode' => 'numeric',
                            ])
                            ->label("Nomor Form Pembayaran (FP)")
                            ->helperText("Pisahkan dengan koma jika lebih dari satu.")
                            ->required(),
                        TextInput::make("nomor_detail_fa")
                            ->maxLength(50)
                            ->rules([
                                // regex Laravel untuk membatasi agar hanya [0-9], koma, dan spasi
                                'regex:/^[0-9, ]+$/',
                            ])
                            ->extraInputAttributes([
                                // pattern HTML5 agar browser menolak input lain saat submit
                                'pattern' => '^[0-9, ]+$',
                                // tooltip saat user hover memberikan petunjuk input
                                'title'   => 'Hanya boleh angka, koma, dan spasi',
                                // inputmode numeric mendorong keyboard angka di mobile app
                                'inputmode' => 'numeric',
                            ])
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
                    ...self::checklistPemeriksaanFields(),
                    Select::make("status_pengajuan_ppk_id")
                        ->label("Status Pemeriksaan")
                        ->preload()
                        ->options(StatusPengajuan::where('id', '!=', Constants::STATUS_DIAJUKAN)->pluck("nama", "id"))
                        ->live()
                        ->required(),
                    Textarea::make("catatan_ppk")
                        ->label("Catatan Tambahan / Kesimpulan PPK")
                        ->helperText("Otomatis digabung dengan rincian perbaikan checklist. Wajib diisi jika status Ditolak/Disetujui dengan Catatan tanpa rincian checklist.")
                        ->required(fn(Get $get) => self::isSummaryNoteRequired($get, 'status_pengajuan_ppk_id')),
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
                    ...self::checklistPemeriksaanFields(),
                    Select::make("status_pengajuan_ppspm_id")
                        ->label("Status Pemeriksaan")
                        ->preload()
                        ->options(StatusPengajuan::where('id', '!=', Constants::STATUS_DIAJUKAN)->pluck("nama", "id"))
                        ->live()
                        ->required(),
                    Textarea::make("catatan_ppspm")
                        ->label("Catatan Tambahan / Kesimpulan PPSPM")
                        ->helperText("Otomatis digabung dengan rincian perbaikan checklist. Wajib diisi jika status Ditolak/Disetujui dengan Catatan tanpa rincian checklist.")
                        ->required(fn(Get $get) => self::isSummaryNoteRequired($get, 'status_pengajuan_ppspm_id')),
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
                    ...self::checklistPemeriksaanFields(),
                    Select::make("status_pengajuan_bendahara_id")
                        ->label("Status Verifikasi")
                        ->preload()
                        ->options(StatusPengajuan::where('id', '!=', Constants::STATUS_DIAJUKAN)->pluck("nama", "id"))
                        ->live()
                        ->required(),
                    Textarea::make("catatan_bendahara")
                        ->label("Catatan Tambahan / Kesimpulan Bendahara")
                        ->helperText("Otomatis digabung dengan rincian perbaikan checklist. Wajib diisi jika status Ditolak/Disetujui dengan Catatan tanpa rincian checklist.")
                        ->required(fn(Get $get) => self::isSummaryNoteRequired($get, 'status_pengajuan_bendahara_id')),
                ]),
        ];
    }

    /**
     * Daftar definisi item checklist pemeriksaan dokumen pembayaran.
     *
     * @return array<string, array{label: string, options: array<string, string>, default: string, note_triggers: list<string>}>
     */
    public static function checklistItemDefinitions(): array
    {
        $standardOptions = [
            'SESUAI' => 'Sesuai',
            'PERLU_PERBAIKAN' => 'Perlu Perbaikan',
        ];

        return [
            'kak' => [
                'label' => 'KAK',
                'options' => $standardOptions,
                'default' => 'SESUAI',
                'note_triggers' => ['PERLU_PERBAIKAN'],
            ],
            'nominatif' => [
                'label' => 'Nominatif',
                'options' => $standardOptions,
                'default' => 'SESUAI',
                'note_triggers' => ['PERLU_PERBAIKAN'],
            ],
            'fp' => [
                'label' => 'FP',
                'options' => $standardOptions,
                'default' => 'SESUAI',
                'note_triggers' => ['PERLU_PERBAIKAN'],
            ],
            'bukti_pengeluaran' => [
                'label' => 'Bukti Pengeluaran',
                'options' => $standardOptions,
                'default' => 'SESUAI',
                'note_triggers' => ['PERLU_PERBAIKAN'],
            ],
            'sk' => [
                'label' => 'SK',
                'options' => [
                    'ADA_SESUAI' => 'Ada & Sesuai',
                    'TIDAK_ADA' => 'Tidak Ada',
                    'PERLU_PERBAIKAN' => 'Perlu Perbaikan',
                ],
                'default' => 'ADA_SESUAI',
                'note_triggers' => ['TIDAK_ADA', 'PERLU_PERBAIKAN'],
            ],
            'bukti_lainnya' => [
                'label' => 'Bukti Pendukung Lainnya',
                'options' => $standardOptions,
                'default' => 'SESUAI',
                'note_triggers' => ['PERLU_PERBAIKAN'],
            ],
        ];
    }

    /**
     * Membangun komponen checklist reaktif untuk semua pemeriksa (PPK, PPSPM, Bendahara).
     *
     * @return array<int, \Filament\Schemas\Components\Component>
     */
    private static function checklistPemeriksaanFields(): array
    {
        $components = [];

        foreach (self::checklistItemDefinitions() as $key => $config) {
            $statusField = "checklist_{$key}";
            $noteField = "catatan_checklist_{$key}";
            $triggers = $config['note_triggers'];

            $components[] = Select::make($statusField)
                ->label($config['label'])
                ->options($config['options'])
                ->default($config['default'])
                ->live()
                ->required();

            $components[] = Textarea::make($noteField)
                ->label("Catatan {$config['label']}")
                ->rows(2)
                ->placeholder("Jelaskan bagian {$config['label']} yang perlu diperbaiki...")
                ->visible(fn (Get $get): bool => in_array($get($statusField), $triggers, true))
                ->required(fn (Get $get): bool => in_array($get($statusField), $triggers, true));
        }

        return [
            Fieldset::make('Checklist Kelengkapan & Kesesuaian Dokumen')
                ->schema($components)
                ->columnSpanFull(),
        ];
    }

    private static function isSummaryNoteRequired(Get $get, string $statusField): bool
    {
        $requiresNote = in_array(
            $get($statusField),
            [Constants::STATUS_DITOLAK, Constants::STATUS_DISETUJUI_DENGAN_CATATAN],
            true
        );

        if (! $requiresNote) {
            return false;
        }

        foreach (self::checklistItemDefinitions() as $key => $config) {
            if (in_array($get("checklist_{$key}"), $config['note_triggers'], true)) {
                return false;
            }
        }

        return true;
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
                        ->preload()
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
                            ->preload()
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
