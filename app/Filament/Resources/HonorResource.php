<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HonorResource\Pages;
use App\Models\Honor;
use App\Supports\Constants;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
// ... (use statements lain)
use Filament\Tables\Actions\Action; // Ganti ImportAction dengan Action
use Filament\Forms\Components\FileUpload;
use Maatwebsite\Excel\Facades\Excel; // Import facade Excel
use App\Imports\HonorImport; // Import kelas importer kita
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification; // Untuk notifikasi
use Filament\Tables\Columns\TextColumn;

class HonorResource extends Resource
{
    protected static ?string $model = Honor::class;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = "Honor Mitra";
    protected static ?int $navigationSort = 2;



    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Honor')
                    ->schema([
                        Select::make('kegiatan_manmit_id')
                            ->relationship('kegiatanManmit', 'nama')
                            ->getOptionLabelFromRecordUsing(fn($record) => "({$record->id}) {$record->nama}")
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live() // Agar ID Batasan Honor terupdate
                            ->label('Kegiatan Manmit'),

                        Select::make('jabatan')
                            ->options(Constants::JABATAN_MITRA_OPTIONS)
                            ->live() // Agar ID Batasan Honor terupdate
                            ->required(),

                        Select::make('jenis_honor')
                            ->options(Constants::JENIS_HONOR_OPTIONS)
                            ->required(),

                        Select::make('satuan_honor')
                            ->options(Constants::SATUAN_HONOR_OPTIONS)
                            ->required()
                            ->label('Satuan Honor'),

                        TextInput::make('harga_per_satuan')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),

                        DatePicker::make('tanggal_akhir_kegiatan')
                            ->required(),

                        // Menampilkan ID Batasan Honor sebagai informasi (tidak bisa di-edit)
                        Placeholder::make('id_batasan_honor')
                            ->label('ID Batasan Honor')
                            ->content(function (Forms\Get $get, ?Honor $record): string {
                                if ($record) {
                                    return $record->id_batasan_honor;
                                }
                                $kegiatan = \App\Models\KegiatanManmit::find($get('kegiatan_manmit_id'));
                                if ($kegiatan && $get('jabatan')) {
                                    return $kegiatan->jenis_kegiatan . '-' . $get('jabatan');
                                }
                                return 'Akan dibuat setelah form diisi';
                            }),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                TextColumn::make('id')->searchable(),
                TextColumn::make('kegiatanManmit.nama')->label('Nama Kegiatan')->searchable()->sortable()->wrap(),
                TextColumn::make('jabatan')->searchable()->badge(),
                TextColumn::make('id_batasan_honor')->label('ID Batasan'),
                TextColumn::make('harga_per_satuan')->money('IDR')->sortable(),
                TextColumn::make('tanggal_akhir_kegiatan')->date('d M Y')->sortable()->label('Akhir Kegiatan'),
                TextColumn::make('tanggal_pembayaran_maksimal')->date('d M Y')->sortable()->label('Batas Bayar'),
            ])
            ->headerActions([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHonors::route('/'),
            'create' => Pages\CreateHonor::route('/create'),
            'edit' => Pages\EditHonor::route('/{record}/edit'),
        ];
    }
}
