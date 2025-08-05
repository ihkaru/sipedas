<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MitraResource\Pages;
use App\Filament\Resources\MitraResource\RelationManagers;
use App\Filament\Resources\MitraResource\RelationManagers\KemitraansRelationManager;
use App\Models\Mitra;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MitraResource extends Resource
{
    protected static ?string $model = Mitra::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Manajemen Mitra';

    public static function canViewAny(): bool
    {
        return true;
    }

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
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kemitraans_status')
                    ->label('Status Kemitraan')
                    ->html(),

                Tables\Columns\TextColumn::make('kabupaten_name')
                    ->label('Kabupaten Domisili'),

                Tables\Columns\TextColumn::make('kecamatan_name')
                    ->label('Kecamatan Domisili'),

                Tables\Columns\TextColumn::make('desa_name')
                    ->label('Desa/Kel Domisili'),
                Tables\Columns\TextColumn::make('nik')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_2')->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('posisi')->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('status_seleksi_1_terpilih_2_tidak_terpilih')->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('alamat_prov')->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('alamat_kab')->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('alamat_kec')->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('alamat_desa')->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('alamat_detail')->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('domisili_sama')->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_lahir_dd_mm_yyyy')->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('npwp')->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('jenis_kelamin')
                    ->searchable(),
                Tables\Columns\TextColumn::make('agama')->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('status_perkawinan')->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('pendidikan')->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('pekerjaan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('deskripsi_pekerjaan_lain')
                    ->searchable(),
                Tables\Columns\TextColumn::make('no_telp')
                    ->searchable(),
                Tables\Columns\TextColumn::make('mengikuti_pendataan_bps')->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('sp')->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('st')->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('se')->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('susenas')->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('sakernas')->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('sbh')->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('catatan')->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('posisi_daftar')->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('username')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sobat_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('id_desa')->toggleable(isToggledHiddenByDefault: true)
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
                // --- BLOK FILTER YANG DIPERBAIKI ---
                SelectFilter::make('status') // Kita bisa sederhanakan namanya
                    ->label('Status Kemitraan')

                    // 1. HAPUS BARIS INI: ->relationship('kemitraans', 'status')

                    // 2. GUNAKAN opsi statis yang bersih dan bebas duplikasi
                    ->options([
                        'AKTIF' => 'Aktif',
                        'TIDAK_AKTIF' => 'Tidak Aktif',
                        'BLACKLISTED' => 'Blacklisted',
                    ])

                    // 3. TAMBAHKAN logika query kustom ini
                    ->query(function (Builder $query, array $data): Builder {
                        // Jika tidak ada nilai yang dipilih, jangan lakukan apa-apa
                        if (empty($data['value'])) {
                            return $query;
                        }

                        // Jika ada nilai, terapkan filter 'whereHas' pada relasi kemitraans
                        return $query->whereHas('kemitraans', function (Builder $q) use ($data) {
                            $q->where('status', $data['value']);
                        });
                    }),
                SelectFilter::make('kecamatan')
                    ->label('Filter Kecamatan')
                    ->searchable()
                    ->options(
                        // Ambil data unik dari master SLS
                        fn() => \App\Models\MasterSls::query()
                            ->distinct()
                            ->pluck('kecamatan', 'kec_id')
                            ->all()
                    )
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }
                        // 'value' berisi kec_id, contoh: "6104080"
                        $kecId = $data['value'];
                        $provId = substr($kecId, 0, 2); // "61"
                        $kabId = substr($kecId, 2, 2); // "04"
                        $kecOnlyId = substr($kecId, 4, 3); // "080"

                        return $query
                            ->where('alamat_prov', $provId)
                            ->where('alamat_kab', $kabId)
                            ->where('alamat_kec', $kecOnlyId);
                    }),

                SelectFilter::make('desa')
                    ->label('Filter Desa/Kelurahan')
                    ->searchable()
                    ->options(
                        fn() => \App\Models\MasterSls::query()
                            ->distinct()
                            ->pluck('desa_kel', 'desa_kel_id')
                            ->all()
                    )
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }
                        // 'value' berisi desa_kel_id, contoh: "6104080001"
                        $desaId = $data['value'];
                        $provId = substr($desaId, 0, 2); // "61"
                        $kabId = substr($desaId, 2, 2); // "04"
                        $kecId = substr($desaId, 4, 3); // "080"
                        $desaOnlyId = substr($desaId, 7, 3); // "001"

                        return $query
                            ->where('alamat_prov', $provId)
                            ->where('alamat_kab', $kabId)
                            ->where('alamat_kec', $kecId)
                            ->where('alamat_desa', $desaOnlyId);
                    }),
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
            KemitraansRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            // Arahkan ke kelas ListMitras yang baru
            'index' => Pages\ListMitras::route('/'),
            'create' => Pages\CreateMitra::route('/create'),
            'edit' => Pages\EditMitra::route('/{record}/edit'),
        ];
    }
}
