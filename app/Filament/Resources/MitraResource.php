<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MitraResource\Pages;
use App\Filament\Resources\MitraResource\RelationManagers;
use App\Models\Mitra;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MitraResource extends Resource
{
    protected static ?string $model = Mitra::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('id_sobat')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('nama_1')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('kabupaten_domisili')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('kecamatan_domisili')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('desa_domisili')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('nik')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('nama_2')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('posisi')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('status_seleksi_1_terpilih_2_tidak_terpilih')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('alamat_prov')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('alamat_kab')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('alamat_kec')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('alamat_desa')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('alamat_detail')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('domisili_sama')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('tanggal_lahir_dd_mm_yyyy')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('npwp')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('jenis_kelamin')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('agama')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('status_perkawinan')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('pendidikan')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('pekerjaan')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('deskripsi_pekerjaan_lain')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('no_telp')
                    ->tel()
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('mengikuti_pendataan_bps')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('sp')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('st')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('se')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('susenas')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('sakernas')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('sbh')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('catatan')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('posisi_daftar')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('username')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('sobat_id')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('id_desa')
                    ->maxLength(255)
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id_sobat')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_1')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kabupaten_domisili')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kecamatan_domisili')
                    ->searchable(),
                Tables\Columns\TextColumn::make('desa_domisili')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nik')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_2')
                    ->searchable(),
                Tables\Columns\TextColumn::make('posisi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status_seleksi_1_terpilih_2_tidak_terpilih')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('alamat_prov')
                    ->searchable(),
                Tables\Columns\TextColumn::make('alamat_kab')
                    ->searchable(),
                Tables\Columns\TextColumn::make('alamat_kec')
                    ->searchable(),
                Tables\Columns\TextColumn::make('alamat_desa')
                    ->searchable(),
                Tables\Columns\TextColumn::make('alamat_detail')
                    ->searchable(),
                Tables\Columns\TextColumn::make('domisili_sama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_lahir_dd_mm_yyyy')
                    ->searchable(),
                Tables\Columns\TextColumn::make('npwp')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jenis_kelamin')
                    ->searchable(),
                Tables\Columns\TextColumn::make('agama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status_perkawinan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pendidikan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pekerjaan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('deskripsi_pekerjaan_lain')
                    ->searchable(),
                Tables\Columns\TextColumn::make('no_telp')
                    ->searchable(),
                Tables\Columns\TextColumn::make('mengikuti_pendataan_bps')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sp')
                    ->searchable(),
                Tables\Columns\TextColumn::make('st')
                    ->searchable(),
                Tables\Columns\TextColumn::make('se')
                    ->searchable(),
                Tables\Columns\TextColumn::make('susenas')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sakernas')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sbh')
                    ->searchable(),
                Tables\Columns\TextColumn::make('catatan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('posisi_daftar')
                    ->searchable(),
                Tables\Columns\TextColumn::make('username')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sobat_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('id_desa')
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
            'index' => Pages\ListMitras::route('/'),
            'create' => Pages\CreateMitra::route('/create'),
            'edit' => Pages\EditMitra::route('/{record}/edit'),
        ];
    }
}
