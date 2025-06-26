<?php

namespace App\Filament\Resources\Sipancong;

use App\Filament\Resources\Sipancong\PengajuanResource\Forms\PengajuanForms;
use App\Filament\Resources\Sipancong\PengajuanResource\Pages;
use App\Models\Sipancong\Pengajuan;
use App\Services\Sipancong\PengajuanServices;
use App\Supports\SipancongConstants as Constants;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Components\Tab;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class PengajuanResource extends Resource
{
    protected static ?string $model = Pengajuan::class;
    protected static ?string $label = "Pengajuan";
    protected static ?string $navigationLabel = "Pengajuan";
    protected static ?string $pluralModelLabel = "Pengajuan";
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = "Pembayaran";
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        // Form ini hanya untuk super_admin, form per aksi ada di PengajuanForms
        return $form->schema(PengajuanForms::fullForm());
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
                        ->form(PengajuanForms::fullForm())
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
                        ->action(fn(array $data, Pengajuan $record) => PengajuanServices::ubahPengajuan($data, $record))
                        ->hidden(fn(Pengajuan $record): bool => !PengajuanServices::canShowPengajuActions($record)),

                    Action::make("Tanggapan Pengaju")
                        ->icon("heroicon-o-chat-bubble-left-right")
                        ->form(PengajuanForms::tanggapanPengaju())
                        ->action(fn(array $data, Pengajuan $record) => PengajuanServices::tanggapi($data, $record))
                        ->hidden(fn(Pengajuan $record): bool => !PengajuanServices::canShowPengajuActions($record)),

                    // --- AKSI UNTUK PEMERIKSA ---
                    Action::make("Aksi PPK")
                        ->modalHeading('Pemeriksaan PPK')
                        ->icon("heroicon-o-check")
                        ->form(PengajuanForms::pemeriksaanPpk())
                        ->action(fn(array $data, Pengajuan $record) => PengajuanServices::pemeriksaanPpk($data, $record))
                        ->hidden(fn(Pengajuan $record): bool => !PengajuanServices::canShowPpkActions($record)),

                    Action::make("Aksi PPSPM")
                        ->modalHeading('Pemeriksaan PPSPM')
                        ->icon("heroicon-o-check")
                        ->form(PengajuanForms::pemeriksaanPpspm())
                        ->action(fn(array $data, Pengajuan $record) => PengajuanServices::pemeriksaanPpspm($data, $record))
                        ->hidden(fn(Pengajuan $record): bool => !PengajuanServices::canShowPpspmActions($record)),

                    // --- AKSI UNTUK BENDAHARA (DIBAGI DUA) ---
                    Action::make("Aksi Verifikasi Bendahara")
                        ->label('Verifikasi Bendahara')
                        ->modalHeading('Pemeriksaan/Verifikasi Bendahara')
                        ->icon("heroicon-o-check")
                        ->form(PengajuanForms::pemeriksaanBendahara())
                        ->action(fn(array $data, Pengajuan $record) => PengajuanServices::pemeriksaanBendahara($data, $record))
                        ->hidden(fn(Pengajuan $record): bool => !PengajuanServices::canShowBendaharaVerificationAction($record)),

                    Action::make("Proses Pembayaran")
                        ->label("Proses Pembayaran")
                        ->modalHeading('Pemrosesan Pembayaran')
                        ->icon("heroicon-o-credit-card")
                        ->form(PengajuanForms::pemrosesanBendahara())
                        ->action(fn(array $data, Pengajuan $record) => PengajuanServices::pemrosesanBendahara($data, $record))
                        ->hidden(fn(Pengajuan $record): bool => !PengajuanServices::canShowBendaharaPaymentAction($record)),

                    DeleteAction::make("hapus")
                        ->hidden(fn(): bool => !auth()->user()->hasRole(['super_admin', 'Admin'])),

                ])->link()->label("Aksi"),
            ], position: ActionsPosition::BeforeColumns)
            ->columns([
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
                TextColumn::make("uraian_pengajuan")->searchable()->label("Uraian"),
                TextColumn::make('penanggungJawab.panggilan')->label("Pj.")->sortable()->searchable(),
                TextColumn::make('pengaju.panggilan')->label("Pengaju")->sortable()->searchable(),
                TextColumn::make('statusPengajuanPpk.nama')->label("PPK")->badge()->sortable(),
                TextColumn::make('statusPengajuanPpspm.nama')->label("PPSPM")->badge()->sortable(),
                TextColumn::make('statusPengajuanBendahara.nama')->label("Bdh.")->badge()->sortable(),
                TextColumn::make('statusPembayaran.nama')->label("Bayar")->badge()->sortable(),
                TextColumn::make('nominal_pengajuan')->label("Nominal")->numeric()->sortable()->searchable(),
                TextColumn::make('updated_at')->label("Last Update")->since()->sortable(),
            ])
            ->recordUrl(null)
            ->filters([
                // Filters from your original code are fine
                // SelectFilter::make('nip_penanggung_jawab')->label("Penanggung Jawab")->relationship('penanggungJawab', 'nama')->searchable()->preload(),
                // SelectFilter::make('nip_pengaju')->label("Pengaju")->relationship('pengaju', 'nama')->multiple()->searchable()->preload(),
                // SelectFilter::make('sub_fungsi_id')->label("Sub Fungsi")->relationship('subfungsi', 'nama')->multiple()->preload(),
                // SelectFilter::make('posisi_dokumen_id')->label("Posisi Dokumen")->relationship('posisiDokumen', 'nama')->multiple()->preload(),
            ])
            ->bulkActions([]);
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
