<?php

namespace App\Filament\Pages;

use App\Models\Pegawai;
use App\Models\Setting;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Log;

class Pengaturan extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected string $view = 'filament.pages.pengaturan';
    protected static ?int $navigationSort = 100;
    protected static string | \UnitEnum | null $navigationGroup = 'Sistem';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = Setting::pluck('value', 'key')->all();
        $rawOptOut = $settings['whatsapp_opt_out_numbers'] ?? '[]';
        $optOutNumbers = is_array($rawOptOut) ? $rawOptOut : (json_decode($rawOptOut, true) ?? []);

        // Pastikan nilai default ada jika key belum ada di DB
        $this->form->fill([
            'ppk_instant_auto_approve' => (bool) ($settings['ppk_instant_auto_approve'] ?? false),
            'ppk_auto_approve_days' => $settings['ppk_auto_approve_days'] ?? 0,
            'STANDAR_BIAYA_MASUKAN_LAINNYA_NON_PNS_OB_PETUGAS_PENDATAAN_LAPANGAN_SENSUS' => $settings['STANDAR_BIAYA_MASUKAN_LAINNYA_NON_PNS_OB_PETUGAS_PENDATAAN_LAPANGAN_SENSUS'] ?? 4694000,
            'STANDAR_BIAYA_MASUKAN_LAINNYA_NON_PNS_OB_PETUGAS_PENDATAAN_LAPANGAN_SURVEI' => $settings['STANDAR_BIAYA_MASUKAN_LAINNYA_NON_PNS_OB_PETUGAS_PENDATAAN_LAPANGAN_SURVEI'] ?? 3353000,
            'allow_weekend_travel' => (bool) ($settings['allow_weekend_travel'] ?? false),
            'st_approval_mode' => $settings['st_approval_mode'] ?? 'delegasi_ppspm',
            'whatsapp_notify_st_baru' => isset($settings['whatsapp_notify_st_baru']) ? (bool) $settings['whatsapp_notify_st_baru'] : true,
            'whatsapp_notify_st_disetujui' => isset($settings['whatsapp_notify_st_disetujui']) ? (bool) $settings['whatsapp_notify_st_disetujui'] : true,
            'whatsapp_notify_st_revisi' => isset($settings['whatsapp_notify_st_revisi']) ? (bool) $settings['whatsapp_notify_st_revisi'] : true,
            'whatsapp_notify_st_ditolak' => isset($settings['whatsapp_notify_st_ditolak']) ? (bool) $settings['whatsapp_notify_st_ditolak'] : true,
            'whatsapp_notify_st_tembusan_kepala' => isset($settings['whatsapp_notify_st_tembusan_kepala']) ? (bool) $settings['whatsapp_notify_st_tembusan_kepala'] : false,
            'whatsapp_notification_enabled' => isset($settings['whatsapp_notification_enabled']) ? (bool) $settings['whatsapp_notification_enabled'] : true,
            'whatsapp_notify_pengajuan_baru' => isset($settings['whatsapp_notify_pengajuan_baru']) ? (bool) $settings['whatsapp_notify_pengajuan_baru'] : true,
            'whatsapp_notify_tanggapan_pengaju' => isset($settings['whatsapp_notify_tanggapan_pengaju']) ? (bool) $settings['whatsapp_notify_tanggapan_pengaju'] : true,
            'whatsapp_notify_hasil_pemeriksaan' => isset($settings['whatsapp_notify_hasil_pemeriksaan']) ? (bool) $settings['whatsapp_notify_hasil_pemeriksaan'] : true,
            'whatsapp_notify_pencairan' => isset($settings['whatsapp_notify_pencairan']) ? (bool) $settings['whatsapp_notify_pencairan'] : true,
            'whatsapp_opt_out_numbers' => $optOutNumbers,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
                Section::make('Persetujuan (Approval) Surat Tugas')
                    ->description('Pengaturan delegasi wewenang verifikasi dan persetujuan Surat Tugas.')
                    ->schema([
                        Select::make('st_approval_mode')
                            ->label('Wewenang Persetujuan Surat Tugas')
                            ->options([
                                'delegasi_ppspm' => 'Dual Approval: Kepala Satker & Kasubbag TU / PPSPM (Rekomendasi)',
                                'kepala_only' => 'Kepala Satker Saja',
                                'ppspm_only' => 'Delegasi Penuh: Kasubbag TU / PPSPM Saja',
                                'auto_approve' => 'Auto-Approve Instan (Bypass Persetujuan)',
                            ])
                            ->default('delegasi_ppspm')
                            ->helperText('Tentukan siapa yang berhak memeriksa, menyetujui, mengarahkan revisi, atau menolak pengajuan Surat Tugas di sistem.')
                            ->live(),

                        Toggle::make('whatsapp_notify_st_tembusan_kepala')
                            ->label('Kirim Tembusan Notifikasi WA ke Kepala Satker')
                            ->helperText('Jika mode delegasi aktif, tetap kirim pesan WhatsApp pengajuan baru ke nomor Kepala Satker sebagai tembusan/informasi.')
                            ->visible(fn (Get $get) => in_array($get('st_approval_mode'), ['delegasi_ppspm', 'ppspm_only']))
                            ->live(),
                    ]),

                Section::make('Notifikasi WhatsApp')
                    ->description('Pengaturan notifikasi pesan otomatis ke WhatsApp pegawai melalui Gateway Aina.')
                    ->schema([
                        Toggle::make('whatsapp_notification_enabled')
                            ->label('Aktifkan Notifikasi WhatsApp (Master Switch)')
                            ->helperText('Jika dinonaktifkan, seluruh pengiriman notifikasi WhatsApp dari sistem Dokter V dan SiPEDAS akan dihentikan.')
                            ->live(),

                        Select::make('whatsapp_opt_out_numbers')
                            ->label('Daftar Pegawai yang Opt-Out (Mengecualikan Notifikasi)')
                            ->helperText('Pilih pegawai yang tidak ingin menerima pesan notifikasi WhatsApp dari sistem.')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->options(fn () => Pegawai::whereNotNull('nomor_wa')->orderBy('nama')->pluck('nama', 'nomor_wa')->toArray())
                            ->visible(fn (Get $get) => (bool) $get('whatsapp_notification_enabled'))
                            ->live(),

                        Section::make('Filter Jenis Notifikasi')
                            ->description('Atur jenis event pengajuan yang memicu pengiriman pesan WhatsApp.')
                            ->compact()
                            ->schema([
                                Section::make('Pengajuan Surat Tugas (SiPEDAS)')
                                    ->compact()
                                    ->schema([
                                        Toggle::make('whatsapp_notify_st_baru')
                                            ->label('Pengajuan ST Baru (ke Penyetuju / Delegasi)')
                                            ->helperText('Kirim WhatsApp ke Penyetuju saat ada surat tugas baru diajukan.')
                                            ->live(),

                                        Toggle::make('whatsapp_notify_st_disetujui')
                                            ->label('Surat Tugas Disetujui (ke Pengaju & Pegawai)')
                                            ->helperText('Kirim WhatsApp ke pengaju saat surat tugas telah disetujui.')
                                            ->live(),

                                        Toggle::make('whatsapp_notify_st_revisi')
                                            ->label('Arahan Revisi Surat Tugas (ke Pengaju)')
                                            ->helperText('Kirim WhatsApp catatan revisi ke pengaju saat surat tugas perlu perbaikan.')
                                            ->live(),

                                        Toggle::make('whatsapp_notify_st_ditolak')
                                            ->label('Surat Tugas Ditolak (ke Pengaju)')
                                            ->helperText('Kirim WhatsApp penolakan ke pengaju saat surat tugas ditolak.')
                                            ->live(),
                                    ]),

                                Section::make('Pengajuan Pembayaran (Dokter-V)')
                                    ->compact()
                                    ->schema([
                                        Toggle::make('whatsapp_notify_pengajuan_baru')
                                            ->label('Pengajuan Baru (ke PPK)')
                                            ->helperText('Kirim WhatsApp ke PPK saat ada pengajuan baru diajukan.')
                                            ->live(),

                                        Toggle::make('whatsapp_notify_tanggapan_pengaju')
                                            ->label('Tanggapan Pengaju (ke Pemeriksa)')
                                            ->helperText('Kirim WhatsApp ke pemeriksa saat pengaju menanggapi revisi.')
                                            ->live(),

                                        Toggle::make('whatsapp_notify_hasil_pemeriksaan')
                                            ->label('Hasil Pemeriksaan / Aksi Pejabat')
                                            ->helperText('Kirim WhatsApp status disetujui / ditolak ke pihak terkait.')
                                            ->live(),

                                        Toggle::make('whatsapp_notify_pencairan')
                                            ->label('Pencairan Dana (oleh Bendahara)')
                                            ->helperText('Kirim WhatsApp ke pengaju saat pembayaran berhasil dicairkan.')
                                            ->live(),
                                    ]),
                            ])
                            ->visible(fn (Get $get) => (bool) $get('whatsapp_notification_enabled')),
                    ]),
                Section::make('Otomatisasi PPK')
                    ->description('Perubahan pada pengaturan ini akan langsung disimpan secara otomatis.')
                    ->schema([
                        Toggle::make('ppk_instant_auto_approve')
                            ->label('Aktifkan Auto-Terima Instan')
                            ->helperText('Jika aktif, setiap pengajuan baru akan langsung disetujui oleh PPK dan diteruskan ke PPSPM.')
                            ->live(), // <-- REAKTIFKAN TOGGLE

                        TextInput::make('ppk_auto_approve_days')
                            ->label('Batas Hari Pemeriksaan PPK')
                            ->numeric()
                            ->minValue(0)
                            ->helperText('Jika pengajuan berada di PPK melebihi jumlah hari ini, sistem akan menyetujuinya secara otomatis. Isi 0 untuk menonaktifkan.')
                            ->live(debounce: 500), // <-- REAKTIFKAN TEXT INPUT DENGAN DEBOUNCE
                    ]),
                Section::make('Batas Honor Mitra')
                    ->description('Pengaturan standar biaya untuk honor mitra. Perubahan akan langsung disimpan.')
                    ->schema([
                        TextInput::make('STANDAR_BIAYA_MASUKAN_LAINNYA_NON_PNS_OB_PETUGAS_PENDATAAN_LAPANGAN_SENSUS')
                            ->label('Standar Biaya Masukan Lainnya (Sensus)')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp')
                            ->live(debounce: 500),
                        TextInput::make('STANDAR_BIAYA_MASUKAN_LAINNYA_NON_PNS_OB_PETUGAS_PENDATAAN_LAPANGAN_SURVEI')
                            ->label('Standar Biaya Masukan Lainnya (Survei / Non-Sensus)')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp')
                            ->live(debounce: 500),
                    ]),
                Section::make('Perjalanan Dinas')
                    ->description('Pengaturan terkait perjalanan dinas. Perubahan akan langsung disimpan.')
                    ->schema([
                        Toggle::make('allow_weekend_travel')
                            ->label('Izinkan Perjalanan Dinas di Hari Sabtu & Minggu')
                            ->helperText('Jika aktif, tanggal pada hari Sabtu dan Minggu dapat dipilih saat mengajukan perjalanan dinas.')
                            ->live(),
                    ])
            ])
            ->statePath('data');
    }

    /**
     * Metode ini akan dipanggil secara otomatis oleh Livewire setiap kali
     * ada perubahan pada properti $data.
     *
     * @param mixed $value Nilai baru dari input.
     * @param string $key Nama dari input yang berubah (misal: 'ppk_auto_approve_days').
     */
    public function updatedData($value, string $key): void
    {
        // Simpan hanya satu key yang berubah untuk efisiensi.
        $storedValue = is_array($value) ? json_encode(array_values($value)) : $value;

        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $storedValue]
        );

        // Beri notifikasi visual bahwa data sudah tersimpan
        Notification::make()
            ->title('Pengaturan "' . str_replace('_', ' ', $key) . '" berhasil diperbarui.')
            ->success()
            ->send();
    }

    // Metode save() tidak lagi diperlukan karena sudah ditangani oleh updatedData()
    // public function save(): void { ... }

    /**
     * Metode ini menentukan siapa yang bisa mengakses halaman ini.
     *
     * @return bool
     */
    public static function canAccess(): bool
    {
        // Dapatkan user yang sedang login
        return auth()->user()->hasRole(["super_admin", "operator_umum"]);
    }
}
