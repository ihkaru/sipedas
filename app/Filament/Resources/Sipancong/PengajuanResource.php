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
                Forms\Components\TextInput::make('nomor_pengajuan')
                    ->required()
                    ->maxLength(50),
                Forms\Components\DatePicker::make('tanggal_pengajuan')
                    ->required(),
                Forms\Components\TextInput::make('nomor_form_pembayaran')
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('nomor_detail_fa')
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\Textarea::make('uraian_pengajuan')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('nominal_pengajuan')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('link_folder_dokumen')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('posisi_dokumen_id')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('status_pengajuan_id')
                    ->numeric()
                    ->default(null),
                Forms\Components\Textarea::make('catatan')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('tanggapan_pengaju')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('nominal_dibayarkan')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('nominal_dikembalikan')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('status_pembayaran_id')
                    ->numeric()
                    ->default(null),
                Forms\Components\DatePicker::make('tanggal_pembayaran'),
                Forms\Components\TextInput::make('jenis_dokumen_id')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('nomor_dokumen')
                    ->maxLength(50)
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor_pengajuan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_pengajuan')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nomor_form_pembayaran')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nomor_detail_fa')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nominal_pengajuan')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('link_folder_dokumen')
                    ->searchable(),
                Tables\Columns\TextColumn::make('posisi_dokumen_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status_pengajuan_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nominal_dibayarkan')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nominal_dikembalikan')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status_pembayaran_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_pembayaran')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jenis_dokumen_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nomor_dokumen')
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
            'index' => Pages\ListPengajuans::route('/'),
            'create' => Pages\CreatePengajuan::route('/create'),
            'edit' => Pages\EditPengajuan::route('/{record}/edit'),
        ];
    }
}
