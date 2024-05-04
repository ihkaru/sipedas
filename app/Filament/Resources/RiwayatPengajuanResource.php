<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RiwayatPengajuanResource\Pages;
use App\Filament\Resources\RiwayatPengajuanResource\RelationManagers;
use App\Models\RiwayatPengajuan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RiwayatPengajuanResource extends Resource
{
    protected static ?string $model = RiwayatPengajuan::class;

    protected static ?string $label = "Riwayat Pengajuan";
    protected static ?string $navigationLabel = "Riwayat Pengajuan";
    protected static ?string $pluralModelLabel = "Riwayat Pengajuan";
    protected static ?string $navigationIcon = 'fluentui-document-bullet-list-clock-20-o';
    protected static ?string $navigationGroup = "Surat Tugas";

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('penugasan_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('status')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('catatan_ditolak')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('catatan_butuh_perbaikan')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('tgl_dikirim'),
                Forms\Components\DateTimePicker::make('tgl_diterima'),
                Forms\Components\DateTimePicker::make('tgl_dibuat'),
                Forms\Components\DateTimePicker::make('tgl_dikumpulkan'),
                Forms\Components\DateTimePicker::make('tgl_butuh_perbaikan'),
                Forms\Components\DateTimePicker::make('tgl_pencairan'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('penugasan_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tgl_dikirim')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tgl_diterima')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tgl_dibatalkan')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tgl_arahan_revisi')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tgl_dibuat')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tgl_dikumpulkan')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tgl_butuh_perbaikan')
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
            'index' => Pages\ListRiwayatPengajuans::route('/'),
            'create' => Pages\CreateRiwayatPengajuan::route('/create'),
            'edit' => Pages\EditRiwayatPengajuan::route('/{record}/edit'),
        ];
    }
}
