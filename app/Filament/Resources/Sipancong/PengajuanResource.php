<?php

namespace App\Filament\Resources\Sipancong;

use App\Filament\Resources\Sipancong\PengajuanResource\Forms\PengajuanForms;
use App\Filament\Resources\Sipancong\PengajuanResource\Pages;
use App\Models\Sipancong\Pengajuan;
use App\Services\Sipancong\PengajuanServices;
use App\Supports\SipancongConstants as Constants;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use App\Models\Sipancong\StatusPembayaran;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PengajuanResource extends Resource
{
    protected static ?string $model = Pengajuan::class;
    protected static ?string $label = "Pengajuan";
    protected static ?string $navigationLabel = "Pengajuan";
    protected static ?string $pluralModelLabel = "Pengajuan";
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static string | \UnitEnum | null $navigationGroup = "Pembayaran";
    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return auth()->check();
    }

    public static function form(Schema $schema): Schema
    {
        // Form ini hanya untuk super_admin, form per aksi ada di PengajuanForms
        return $schema->schema(PengajuanForms::fullForm());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->defaultSort("updated_at", "desc")
            ->actions([
                ActionGroup::make([
                    EditAction::make('edit_admin')
                        ->label('Edit (Admin)')
                        // ->form(PengajuanForms::fullForm()) // EditAction akan menggunakan form() utama secara default
                        ->hidden(fn(): bool => !auth()->user()->hasRole(['super_admin', 'Admin'])),

                    Action::make("linkfolder")
                        ->label("Lihat Dokumen")
                        ->icon("heroicon-m-link")
                        ->url(fn(Pengajuan $record): string => $record->link_folder_dokumen ?? '#')
                        ->openUrlInNewTab()
                        ->hidden(fn(Pengajuan $record) => !$record->link_folder_dokumen),

                    // --- AKSI UNTUK PENGAJU ---
                    Action::make("Perbaiki Pengajuan")
                        ->icon("heroicon-o-pencil")
                        ->form(PengajuanForms::pengajuanPembayaran())
                        // -- TAMBAHKAN BLOK INI --
                        ->mountUsing(function (Schema $form, Pengajuan $record) {
                            $form->fill($record->toArray());
                        })
                        // ------------------------
                        ->action(fn(array $data, Pengajuan $record) => PengajuanServices::ubahPengajuan($data, $record))
                        ->hidden(fn(Pengajuan $record): bool => !PengajuanServices::canShowPengajuActions($record)),

                    Action::make("Tanggapan Pengaju")
                        ->icon("heroicon-o-chat-bubble-left-right")
                        ->form(PengajuanForms::tanggapanPengaju())
                        // -- TAMBAHKAN BLOK INI --
                        ->mountUsing(function (Schema $form, Pengajuan $record) {
                            $form->fill($record->toArray());
                        })
                        // ------------------------
                        ->action(fn(array $data, Pengajuan $record) => PengajuanServices::tanggapi($data, $record))
                        ->hidden(fn(Pengajuan $record): bool => !PengajuanServices::canShowPengajuActions($record)),

                    // --- AKSI UNTUK PEMERIKSA ---
                    Action::make("Aksi PPK")
                        ->modalHeading('Pemeriksaan PPK')
                        ->icon("heroicon-o-check")
                        ->form(PengajuanForms::pemeriksaanPpk())
                        // -- TAMBAHKAN BLOK INI --
                        ->mountUsing(function (Schema $form, Pengajuan $record) {
                            $form->fill($record->toArray());
                        })
                        // ------------------------
                        ->action(fn(array $data, Pengajuan $record) => PengajuanServices::pemeriksaanPpk($data, $record))
                        ->hidden(fn(Pengajuan $record): bool => !PengajuanServices::canShowPpkActions($record)),

                    Action::make("Aksi PPSPM")
                        ->modalHeading('Pemeriksaan PPSPM')
                        ->icon("heroicon-o-check")
                        ->form(PengajuanForms::pemeriksaanPpspm())
                        // -- TAMBAHKAN BLOK INI --
                        ->mountUsing(function (Schema $form, Pengajuan $record) {
                            $form->fill($record->toArray());
                        })
                        // ------------------------
                        ->action(fn(array $data, Pengajuan $record) => PengajuanServices::pemeriksaanPpspm($data, $record))
                        ->hidden(fn(Pengajuan $record): bool => !PengajuanServices::canShowPpspmActions($record)),

                    // --- AKSI UNTUK BENDAHARA (DIBAGI DUA) ---
                    Action::make("Aksi Verifikasi Bendahara")
                        ->label('Verifikasi Bendahara')
                        ->modalHeading('Pemeriksaan/Verifikasi Bendahara')
                        ->icon("heroicon-o-check")
                        ->form(PengajuanForms::pemeriksaanBendahara())
                        // -- TAMBAHKAN BLOK INI --
                        ->mountUsing(function (Schema $form, Pengajuan $record) {
                            $form->fill($record->toArray());
                        })
                        // ------------------------
                        ->action(fn(array $data, Pengajuan $record) => PengajuanServices::pemeriksaanBendahara($data, $record))
                        ->hidden(fn(Pengajuan $record): bool => !PengajuanServices::canShowBendaharaVerificationAction($record)),

                    Action::make("Proses Pembayaran")
                        ->label("Proses Pembayaran")
                        ->modalHeading('Pemrosesan Pembayaran')
                        ->icon("heroicon-o-credit-card")
                        ->form(PengajuanForms::pemrosesanBendahara())
                        // -- TAMBAHKAN BLOK INI --
                        ->mountUsing(function (Schema $form, Pengajuan $record) {
                            $form->fill($record->toArray());
                        })
                        // ------------------------
                        ->action(fn(array $data, Pengajuan $record) => PengajuanServices::pemrosesanBendahara($data, $record))
                        ->hidden(fn(Pengajuan $record): bool => !PengajuanServices::canShowBendaharaPaymentAction($record)),

                    DeleteAction::make("hapus")
                        ->hidden(fn(): bool => !auth()->user()->hasRole(['super_admin', 'Admin'])),

                ])->link()->label("Aksi"),
            ], position: RecordActionsPosition::BeforeColumns)
            ->columns([
                // Kolom-kolom Anda tidak perlu diubah, sudah benar
                TextColumn::make('posisiDokumen.nama')
                    ->label("Posisi Dokumen")
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Di Pengaju Pembayaran' => 'warning',
                        'Di PPK' => 'info',
                        'Di PPSPM' => 'info',
                        'Di Bendahara' => 'primary',
                        'Selesai' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('nomor_pengajuan')
                    ->label("No")
                    ->sortable(query: fn($query, $direction) => $query->orderBy(DB::raw('CAST(nomor_pengajuan AS UNSIGNED)'), $direction))
                    ->searchable(),
                TextColumn::make("uraian_pengajuan")->sortable()->searchable()->label("Uraian")->limit(30)->tooltip(fn($state) => $state),
                TextColumn::make('penanggungJawab.panggilan')->label("Pj.")->sortable()->searchable(),
                TextColumn::make('pegawai.panggilan')->label("Pengaju")->sortable()->searchable(),
                TextColumn::make('statusPengajuanPpk.nama')->label("PPK")->badge()->sortable(),
                TextColumn::make('statusPengajuanPpspm.nama')->label("PPSPM")->badge()->sortable(),
                TextColumn::make('statusPengajuanBendahara.nama')->label("Bdh.")->badge()->sortable(),
                TextColumn::make('statusPembayaran.nama')->label("Bayar")->badge()->sortable(),
                TextColumn::make('nominal_pengajuan')->label("Nominal")->numeric(locale: 'id_ID')->sortable()->searchable(),
                TextColumn::make('updated_at')->label("Last Update")->since()->sortable(),
                TextColumn::make('created_at')->label("Diajukan Pada")->date()->sortable(),
            ])
            ->recordUrl(null)
            ->filters([
                // ... Filters
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    // 1. Verifikasi Terpilih (Bendahara)
                    BulkAction::make('bulk_verifikasi_bendahara')
                        ->label('Verifikasi Terpilih (Bendahara)')
                        ->icon('heroicon-o-check-circle')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->modalHeading('Verifikasi Dokumen Terpilih (Bendahara)')
                        ->modalDescription('Anda akan menyetujui verifikasi dokumen (tanpa catatan) untuk pengajuan yang dipilih. Dokumen yang belum berada di meja Bendahara akan dilewati secara aman.')
                        ->modalSubmitActionLabel('Ya, Verifikasi Terpilih')
                        ->hidden(fn(): bool => !auth()->user()->hasAnyRole(['super_admin', 'Admin', 'bendahara']))
                        ->action(function (Collection $records) {
                            $count = PengajuanServices::bulkPemeriksaanBendahara($records);
                            Notification::make()
                                ->success()
                                ->title("Berhasil Memverifikasi {$count} Pengajuan")
                                ->body("{$count} pengajuan telah diverifikasi Bendahara dan siap untuk proses pembayaran.")
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    // 2. Cairkan / Bayar Terpilih (Bendahara)
                    BulkAction::make('bulk_pembayaran_bendahara')
                        ->label('Cairkan / Bayar Terpilih (Bendahara)')
                        ->icon('heroicon-o-credit-card')
                        ->color('success')
                        ->form([
                            Select::make('status_pembayaran_id')
                                ->label('Metode / Status Pembayaran')
                                ->options(StatusPembayaran::pluck('nama', 'id'))
                                ->default(Constants::PEMBAYARAN_SUDAH_CMS)
                                ->required(),
                            DatePicker::make('tanggal_pembayaran')
                                ->label('Tanggal Pembayaran / Cair')
                                ->default(now())
                                ->required(),
                            Toggle::make('kirim_wa')
                                ->label('Kirim Notifikasi WhatsApp ke Pengaju')
                                ->default(false)
                                ->helperText('Kirim notifikasi otomatis ke nomor WhatsApp pengaju bahwa dana telah dicairkan.'),
                        ])
                        ->modalHeading('Pencairan Dana Pengajuan Terpilih')
                        ->modalDescription('Pengajuan terpilih yang telah diverifikasi akan dicairkan secara penuh (nominal dibayarkan = nominal pengajuan). Dokumen akan otomatis berstatus Selesai.')
                        ->modalSubmitActionLabel('Ya, Cairkan Terpilih')
                        ->hidden(fn(): bool => !auth()->user()->hasAnyRole(['super_admin', 'Admin', 'bendahara']))
                        ->action(function (array $data, Collection $records) {
                            $sendWa = (bool)($data['kirim_wa'] ?? false);
                            $count = PengajuanServices::bulkPemrosesanBendahara($records, $data, $sendWa);
                            Notification::make()
                                ->success()
                                ->title("Berhasil Mencairkan {$count} Pengajuan")
                                ->body("{$count} pengajuan telah dicairkan dan posisinya dipindahkan ke Selesai.")
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    // 3. Verifikasi Terpilih (PPSPM)
                    BulkAction::make('bulk_verifikasi_ppspm')
                        ->label('Verifikasi Terpilih (PPSPM)')
                        ->icon('heroicon-o-arrow-right-circle')
                        ->color('info')
                        ->requiresConfirmation()
                        ->modalHeading('Verifikasi PPSPM Terpilih')
                        ->modalDescription('Pengajuan yang berada di PPSPM akan disetujui dan diteruskan ke meja Bendahara.')
                        ->modalSubmitActionLabel('Ya, Teruskan ke Bendahara')
                        ->hidden(fn(): bool => !auth()->user()->hasAnyRole(['super_admin', 'Admin', 'ppspm']))
                        ->action(function (Collection $records) {
                            $count = PengajuanServices::bulkPemeriksaanPpspm($records);
                            Notification::make()
                                ->success()
                                ->title("Berhasil Menerima {$count} Pengajuan PPSPM")
                                ->body("{$count} pengajuan telah disetujui PPSPM dan dipindahkan ke meja Bendahara.")
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    // 4. Verifikasi Terpilih (PPK)
                    BulkAction::make('bulk_verifikasi_ppk')
                        ->label('Verifikasi Terpilih (PPK)')
                        ->icon('heroicon-o-check')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Verifikasi PPK Terpilih')
                        ->modalDescription('Pengajuan yang berada di PPK akan disetujui dan diteruskan ke meja PPSPM.')
                        ->modalSubmitActionLabel('Ya, Teruskan ke PPSPM')
                        ->hidden(fn(): bool => !auth()->user()->hasAnyRole(['super_admin', 'Admin', 'ppk']))
                        ->action(function (Collection $records) {
                            $count = PengajuanServices::bulkPemeriksaanPpk($records);
                            Notification::make()
                                ->success()
                                ->title("Berhasil Menyetujui {$count} Pengajuan PPK")
                                ->body("{$count} pengajuan telah disetujui PPK dan dipindahkan ke meja PPSPM.")
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getTabs(): array
    {
        $user = auth()->user();
        $queryPengaju = ($user->hasRole(['super_admin', 'Admin'])) ? PengajuanServices::rawPerluPerbaikanPengaju() : PengajuanServices::rawPerluPerbaikanPengaju() . " AND nip_pengaju = '{$user->pegawai?->nip}'";

        return [
            'Semua' => Tab::make(),
            'Perlu Perbaikan' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->whereRaw($queryPengaju))
                ->badge(PengajuanServices::jumlahPerluPerbaikanPengaju())
                ->badgeColor('warning'),
            'PPK' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->whereRaw(PengajuanServices::rawPerluPemeriksaanPpk()))
                ->badge(PengajuanServices::jumlahPerluPemeriksaanPpk())
                ->badgeColor('info')
                ->hidden(fn(): bool => !auth()->user()->hasAnyRole(['super_admin', 'Admin', 'ppk'])),
            'PPSPM' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->whereRaw(PengajuanServices::rawPerluPemeriksaanPpspm()))
                ->badge(PengajuanServices::jumlahPerluPemeriksaanPpspm())
                ->badgeColor('info')
                ->hidden(fn(): bool => !auth()->user()->hasAnyRole(['super_admin', 'Admin', 'ppspm'])),
            'Bendahara' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->whereRaw(PengajuanServices::rawPerluPemeriksaanAtauProsesBendahara()))
                ->badge(PengajuanServices::jumlahPerluPemeriksaanAtauProsesBendahara())
                ->badgeColor('primary')
                ->hidden(fn(): bool => !auth()->user()->hasAnyRole(['super_admin', 'Admin', 'bendahara'])),
            'Selesai' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('posisi_dokumen_id', Constants::POSISI_SELESAI)),
        ];
    }

    public static function getRelations(): array
    {
        return [];
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPengajuans::route('/'),
            // Hapus create dan edit default jika kamu ingin semua aksi via modal
            // 'create' => Pages\CreatePengajuan::route('/create'),
            // 'edit' => Pages\EditPengajuan::route('/{record}/edit'),
        ];
    }
}
