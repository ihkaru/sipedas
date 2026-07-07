<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RiwayatPpkResource\Pages;
use App\Models\Pegawai;
use App\Models\RiwayatPpk;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RiwayatPpkResource extends Resource
{
    protected static ?string $model = RiwayatPpk::class;

    protected static ?string $label = 'Riwayat PPK';
    protected static ?string $pluralModelLabel = 'Riwayat PPK';
    protected static ?string $navigationLabel = 'Riwayat PPK';
    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationGroup = 'Sistem';
    protected static ?int $navigationSort = 5;

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole('super_admin') ||
            auth()->user()->hasRole('kepala_satker') ||
            auth()->user()->hasRole('operator_umum');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasRole('super_admin') ||
            auth()->user()->hasRole('operator_umum');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->hasRole('super_admin') ||
            auth()->user()->hasRole('operator_umum');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->hasRole('super_admin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi PPK')
                    ->description('Tentukan pegawai yang menjabat sebagai Pejabat Pembuat Komitmen (PPK) beserta masa aktifnya.')
                    ->icon('heroicon-o-identification')
                    ->schema([
                        Forms\Components\Select::make('nip_ppk')
                            ->label('Pegawai PPK')
                            ->options(fn () => Pegawai::orderBy('nama')->pluck('nama', 'nip'))
                            ->searchable()
                            ->required()
                            ->helperText('Pilih pegawai yang akan/pernah menjabat sebagai PPK.'),

                        Forms\Components\DatePicker::make('tgl_mulai')
                            ->label('Tanggal Mulai Menjabat')
                            ->required()
                            ->native(false)
                            ->displayFormat('d M Y')
                            ->helperText('Tanggal pertama PPK ini aktif menandatangani dokumen.'),

                        Forms\Components\DatePicker::make('tgl_selesai')
                            ->label('Tanggal Selesai Menjabat')
                            ->native(false)
                            ->displayFormat('d M Y')
                            ->helperText('Kosongkan jika PPK ini masih aktif saat ini.')
                            ->afterOrEqual('tgl_mulai'),

                        Forms\Components\Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->rows(2)
                            ->columnSpanFull()
                            ->placeholder('Contoh: Mutasi ke instansi lain, pensiun, dsb.')
                            ->helperText('Opsional — alasan pergantian atau catatan tambahan.'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('tgl_mulai', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('pegawai.nama')
                    ->label('Nama PPK')
                    ->searchable()
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('pegawai.jabatan')
                    ->label('Jabatan')
                    ->color('gray'),

                Tables\Columns\TextColumn::make('tgl_mulai')
                    ->label('Mulai Menjabat')
                    ->date('d M Y')
                    ->sortable()
                    ->icon('heroicon-m-calendar-days'),

                Tables\Columns\TextColumn::make('tgl_selesai')
                    ->label('Selesai Menjabat')
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('— Masih Aktif —')
                    ->icon('heroicon-m-calendar-days'),

                Tables\Columns\IconColumn::make('is_aktif')
                    ->label('Status')
                    ->boolean()
                    ->state(fn (RiwayatPpk $r): bool => $r->is_aktif)
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(50)
                    ->placeholder('-')
                    ->color('gray'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make()->label('Ubah'),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->visible(fn () => auth()->user()->hasRole('super_admin')),
            ])
            ->bulkActions([])
            ->emptyStateIcon('heroicon-o-identification')
            ->emptyStateHeading('Belum ada data PPK')
            ->emptyStateDescription('Tambahkan riwayat PPK agar sistem dapat menentukan penandatangan dokumen secara otomatis.');
    }

    public static function getNavigationBadge(): ?string
    {
        $aktif = RiwayatPpk::aktif()->with('pegawai')->first()?->pegawai?->nama;
        return $aktif ? 'Aktif' : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListRiwayatPpks::route('/'),
            'create' => Pages\CreateRiwayatPpk::route('/create'),
            'edit'   => Pages\EditRiwayatPpk::route('/{record}/edit'),
        ];
    }
}
