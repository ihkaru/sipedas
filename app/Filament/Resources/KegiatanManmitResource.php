<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KegiatanManmitResource\Pages;
use App\Filament\Resources\KegiatanManmitResource\RelationManagers\AlokasiHonorRelationManager;
use App\Models\KegiatanManmit;
use App\Supports\Constants;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Str;

class KegiatanManmitResource extends Resource
{
    protected static ?string $model = KegiatanManmit::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $slug = 'kegiatan-manmit';

    protected static ?string $navigationGroup = "Honor Mitra";
    protected static ?int $navigationSort = 1;



    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Utama')
                    ->schema([
                        Forms\Components\TextInput::make('nama')
                            ->label('Nama Kegiatan Utama')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->helperText('Contoh: (VHTS25) Survei Hotel Bulanan 2025'),

                        Forms\Components\Select::make('frekuensi_kegiatan')
                            ->options(Constants::FREKUENSI_KEGIATAN_OPTIONS)
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                // Reset periods when frequency changes
                                $options = [];
                                switch ($state) {
                                    case Constants::FREKUENSI_TRIWULANAN:
                                        $options = Constants::TRIWULAN_OPTIONS;
                                        break;
                                    case Constants::FREKUENSI_BULANAN:
                                        $options = Constants::BULAN_OPTIONS;
                                        break;
                                    case Constants::FREKUENSI_SEMESTERAN:
                                        $options = Constants::SEMESTER_OPTIONS;
                                        break;
                                    case Constants::FREKUENSI_SUBROUND:
                                        $options = Constants::SUBROUND_OPTIONS;
                                        break;
                                }

                                $items = [];
                                foreach ($options as $key => $name) {
                                    $items[] = [
                                        'period_key' => $key,
                                        'period_name' => $name,
                                        'tgl_mulai' => null,
                                        'tgl_akhir' => null
                                    ];
                                }
                                $set('periods', $items);
                            }),
                    ]),

                // --- REPEATER DINAMIS YANG SUDAH DIPERBAIKI ---
                Repeater::make('periods')
                    ->label('Jadwal Periode Kegiatan')
                    ->schema([
                        Forms\Components\Hidden::make('period_key'),
                        Forms\Components\TextInput::make('period_name')
                            ->label('Periode')
                            ->disabled(),
                        Forms\Components\DatePicker::make('tgl_mulai')
                            ->date()
                            ->label('Tanggal Mulai')
                            ->required(),
                        Forms\Components\DatePicker::make('tgl_akhir')
                            ->date()
                            ->label('Tanggal Selesai')
                            ->required(),
                    ])
                    ->columns(4)
                    ->addable(false)
                    ->deletable(true)
                    ->reorderable(false)
                    ->visible(fn(Get $get) => !in_array($get('frekuensi_kegiatan'), Constants::SINGLE_OCCURRENCE_FREQUENCIES))
                    ->default([]), // Start with empty array

                // --- BAGIAN UNTUK FREKUENSI TUNGGAL ---
                Forms\Components\Section::make('Jadwal Kegiatan')
                    ->schema([
                        Forms\Components\DatePicker::make('tgl_mulai_pelaksanaan')
                            ->date()
                            ->required(),
                        Forms\Components\DatePicker::make('tgl_akhir_pelaksanaan')
                            ->date()
                            ->required(),
                    ])
                    ->columns(2)
                    ->visible(fn(Get $get) => in_array($get('frekuensi_kegiatan'), Constants::SINGLE_OCCURRENCE_FREQUENCIES)),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort("updated_at", "desc")
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('jenis_kegiatan')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        Constants::SENSUS => 'danger',
                        Constants::SURVEI => 'success',
                        default => 'gray',
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('frekuensi_kegiatan')
                    ->badge()
                    ->searchable(),

                Tables\Columns\TextColumn::make('tgl_mulai_pelaksanaan')
                    ->label('Mulai Pelaksanaan')
                    ->dateTime('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tgl_akhir_pelaksanaan')
                    ->label('Akhir Pelaksanaan')
                    ->dateTime('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('id')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            AlokasiHonorRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKegiatanManmits::route('/'),
            'create' => Pages\CreateKegiatanManmit::route('/create'),
            'edit' => Pages\EditKegiatanManmit::route('/{record}/edit'),
        ];
    }
}
