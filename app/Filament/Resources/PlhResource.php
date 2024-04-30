<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlhResource\Pages;
use App\Filament\Resources\PlhResource\RelationManagers;
use App\Models\Plh;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PlhResource extends Resource
{
    protected static ?string $model = Plh::class;

    protected static ?string $label = "PLH";
    protected static ?string $navigationLabel = "PLH";
    protected static ?string $pluralModelLabel = "PLH";
    protected static ?string $navigationIcon = 'fluentui-people-swap-16-o';
    protected static ?string $navigationGroup = "Surat Tugas";

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('pegawai.nama')
                    ->label("Pegawai Pengganti")
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\DateTimePicker::make('tgl_mulai')
                    ->label("Tanggal Mulai"),
                Forms\Components\DateTimePicker::make('tgl_selesai')
                    ->label("Tanggal Selesai"),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pegawai.nama')
                    ->label("Pegawai Pengganti")
                    ->searchable(),
                Tables\Columns\TextColumn::make('tgl_mulai')
                    ->label("Tanggal Mulai")
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tgl_selesai')
                    ->label("Tanggal Selesai")
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->date()
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
            'index' => Pages\ListPlhs::route('/'),
            'create' => Pages\CreatePlh::route('/create'),
            'edit' => Pages\EditPlh::route('/{record}/edit'),
        ];
    }
}
