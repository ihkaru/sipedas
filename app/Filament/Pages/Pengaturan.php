<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Log;

class Pengaturan extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static string $view = 'filament.pages.pengaturan';
    protected static ?int $navigationSort = 100;
    protected static ?string $navigationGroup = 'Sistem';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = Setting::pluck('value', 'key')->all();
        // Pastikan nilai default ada jika key belum ada di DB
        $this->form->fill([
            'ppk_instant_auto_approve' => $settings['ppk_instant_auto_approve'] ?? false,
            'ppk_auto_approve_days' => $settings['ppk_auto_approve_days'] ?? 0,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
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
        // $key akan menjadi 'ppk_instant_auto_approve' atau 'ppk_auto_approve_days'
        // Simpan hanya satu key yang berubah untuk efisiensi.
        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
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
