<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PenugasanResource\Pages;
use App\Filament\Resources\PenugasanResource\RelationManagers;
use App\Models\Penugasan;
use App\Models\RiwayatPengajuan;
use App\Supports\Constants;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class PenugasanResource extends Resource
{
    protected static ?string $model = Penugasan::class;

    protected static ?string $label = "Pengajuan";
    protected static ?string $navigationLabel = "Pengajuan";
    protected static ?string $pluralModelLabel = "Pengajuan";
    protected static ?string $navigationIcon = 'fluentui-document-add-24-o';
    protected static ?string $navigationGroup = "Surat Tugas";


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nip')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('kegiatan_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('jenis_perjadin')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('tgl_mulai_tugas'),
                Forms\Components\DateTimePicker::make('tgl_akhir_tugas'),
                Forms\Components\TextInput::make('tbh_hari_jalan_awal')
                    ->default(0)
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('tbh_hari_jalan_akhir')
                    ->default(0)
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('prov_id')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('kabkot_id')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('kecamatan_id')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('desa_kel_id')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('jenis_surat_tugas')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('surat_tugas_id')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('pegawai_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('transportasi')
                    ->required()
                    ->maxLength(255)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pegawai.nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kegiatan.nama')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tgl_mulai_tugas')
                    ->label("Tanggal Mulai Tugas")
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tgl_akhir_tugas')
                    ->label("Tanggal Akhir Tugas")
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tbh_hari_jalan_awal')
                    ->label("Tambah Hari Perjalanan Awal")
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tbh_hari_jalan_akhir')
                    ->label("Tambah Hari Perjalanan Akhir")
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('provinsi.provinsi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kabkot.kabkot')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kecamatan.kecamatan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('desa.desa_kel')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jenis_surat_tugas')
                    ->label("Jenis Surat Tugas")
                    ->state(function (Penugasan $record): string {
                        return $record->jenis_surat;
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('surat_tugas_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('plh.nama')
                    ->label("Penyetuju")
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('riawayatPengajuan.status')
                    ->state(function (Penugasan $record): string {
                        return $record->riwayatPengajuan?->last_status ?? "";
                    })
                    ->label("Status"),
                    // ->searchable(),
                Tables\Columns\TextColumn::make('transportasi')
                    ->state(function (Penugasan $record): string {
                        return $record->jenis_transportasi;
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('pegawai')
                    ->relationship('pegawai', 'nama')
                    ->multiple()
                    ->searchable()
                    ->preload(),
                DateRangeFilter::make("tgl_mulai_tugas")
                    ->label("Tanggal Mulai Tugas"),
                DateRangeFilter::make("tgl_akhir_tugas")
                    ->label("Tanggal Akhir Tugas"),
                SelectFilter::make('riwayatPengajuan')
                    ->options(Constants::STATUS_PENGAJUAN_OPTIONS)
                    ->searchable()
                    ->multiple()
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['values']))
                        {
                            // if we have a value (the aircraft ID from our options() query), just query a nested
                            // set of whereHas() clauses to reach our target, in this case two deep
                            $query->whereHas(
                                'riwayatPengajuan',
                                fn (Builder $query) => $query->whereIn('status',$data['values'])
                            );
                        }
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make("terima")
                    ->visible(function (Penugasan $record){
                        return $record->riwayatPengajuan->status == Constants::STATUS_PENGAJUAN_DIKIRIM;
                        ;})
                    ->label("Terima"),
                Action::make("batalkan")
                    ->label("Batalkan"),
                Action::make("tolak")
                    ->label("Tolak"),
                Action::make("buat")
                    ->visible(function (Penugasan $record){
                        return $record->riwayatPengajuan->status == Constants::STATUS_PENGAJUAN_DITERIMA;
                    ;})
                    ->label("Buat"),
                Action::make("kumpulkan")
                    ->visible(function (Penugasan $record){
                        return $record->riwayatPengajuan->status == Constants::STATUS_PENGAJUAN_DIBUAT;
                    ;})
                    ->label("Kumpulkan"),
                Action::make("Cairkan")
                    ->visible(function (Penugasan $record){
                        return $record->riwayatPengajuan->status == Constants::STATUS_PENGAJUAN_DIKUMPULKAN;
                    ;})
                    ->label("Cairkan"),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPenugasans::route('/'),
            // 'create' => Pages\CreatePenugasan::route('/create'),
            'edit' => Pages\EditPenugasan::route('/{record}/edit'),
        ];
    }
}
