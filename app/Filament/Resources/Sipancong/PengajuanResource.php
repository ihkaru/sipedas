<?php

namespace App\Filament\Resources\Sipancong;

use App\Filament\Resources\Sipancong\PengajuanResource\Pages;
use App\Filament\Resources\Sipancong\PengajuanResource\RelationManagers;
use App\Models\Sipancong\Pengajuan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;

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
        return $form
            ->schema([
                TextInput::make('nomor_pengajuan')
                    ->required()
                    ->maxLength(50),
                DatePicker::make('tanggal_pengajuan')
                    ->required(),
                TextInput::make('nomor_form_pembayaran')
                    ->maxLength(50)
                    ->default(null),
                TextInput::make('nomor_detail_fa')
                    ->maxLength(50)
                    ->default(null),
                Textarea::make('uraian_pengajuan')
                    ->columnSpanFull(),
                TextInput::make('nominal_pengajuan')
                    ->required()
                    ->numeric(),
                TextInput::make('link_folder_dokumen')
                    ->maxLength(255)
                    ->default(null),
                TextInput::make('posisi_dokumen_id')
                    ->numeric()
                    ->default(null),
                TextInput::make('status_pengajuan_ppk_id')
                    ->numeric()
                    ->default(null),
                TextInput::make('status_pengajuan_ppspm_id')
                    ->numeric()
                    ->default(null),
                Textarea::make('catatan_ppk')
                    ->columnSpanFull(),
                Textarea::make('catatan_bendahara')
                    ->columnSpanFull(),
                Textarea::make('catatan_ppspm')
                    ->columnSpanFull(),
                Textarea::make('tanggapan_pengaju_ke_ppk')
                    ->columnSpanFull(),
                Textarea::make('tanggapan_pengaju_ke_ppspm')
                    ->columnSpanFull(),
                Textarea::make('tanggapan_pengaju_ke_bendahara')
                    ->columnSpanFull(),
                TextInput::make('nominal_dibayarkan')
                    ->numeric()
                    ->default(null),
                TextInput::make('nominal_dikembalikan')
                    ->numeric()
                    ->default(null),
                TextInput::make('status_pembayaran_id')
                    ->numeric()
                    ->default(null),
                DatePicker::make('tanggal_pembayaran'),
                TextInput::make('jenis_dokumen_id')
                    ->numeric()
                    ->default(null),
                TextInput::make('nomor_dokumen')
                    ->maxLength(50)
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nomor_pengajuan')
                    ->searchable(),
                TextColumn::make('tanggal_pengajuan')
                    ->date()
                    ->sortable(),
                TextColumn::make('nomor_form_pembayaran')
                    ->searchable(),
                TextColumn::make('nomor_detail_fa')
                    ->searchable(),
                TextColumn::make('nominal_pengajuan')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('link_folder_dokumen')
                    ->searchable(),
                TextColumn::make('posisi_dokumen_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status_pengajuan_ppk_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status_pengajuan_ppspm_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('nominal_dibayarkan')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('nominal_dikembalikan')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status_pembayaran_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tanggal_pembayaran')
                    ->date()
                    ->sortable(),
                TextColumn::make('jenis_dokumen_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('nomor_dokumen')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
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
            'index' => Pages\ListPengajuans::route('/'),
            'create' => Pages\CreatePengajuan::route('/create'),
            'edit' => Pages\EditPengajuan::route('/{record}/edit'),
        ];
    }
}
