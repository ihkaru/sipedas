<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PenugasanResource\Pages;
use App\Filament\Resources\PenugasanResource\RelationManagers;
use App\Models\Penugasan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PenugasanResource extends Resource
{
    protected static ?string $model = Penugasan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('tbh_hari_jalan_akhir')
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
                    ->maxLength(255),
                Forms\Components\TextInput::make('status')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('tgl_surat_pengajuan'),
                Forms\Components\DateTimePicker::make('tgl_surat_diterima'),
                Forms\Components\DateTimePicker::make('tgl_surat_cetak'),
                Forms\Components\DateTimePicker::make('tgl_surat_kembali'),
                Forms\Components\DateTimePicker::make('tgl_pencairan'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nip')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kegiatan_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jenis_perjadin')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tgl_mulai_tugas')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tgl_akhir_tugas')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tbh_hari_jalan_awal')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tbh_hari_jalan_akhir')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('prov_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kabkot_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kecamatan_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('desa_kel_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jenis_surat_tugas')
                    ->searchable(),
                Tables\Columns\TextColumn::make('surat_tugas_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pegawai_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('transportasi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tgl_surat_pengajuan')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tgl_surat_diterima')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tgl_surat_cetak')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tgl_surat_kembali')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tgl_pencairan')
                    ->dateTime()
                    ->sortable(),
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
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'create' => Pages\CreatePenugasan::route('/create'),
            'edit' => Pages\EditPenugasan::route('/{record}/edit'),
        ];
    }
}
