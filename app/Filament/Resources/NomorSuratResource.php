<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NomorSuratResource\Pages;
use App\Filament\Resources\NomorSuratResource\RelationManagers;
use App\Models\NomorSurat;
use App\Supports\Constants;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class NomorSuratResource extends Resource
{
    protected static ?string $model = NomorSurat::class;

    protected static ?string $label = "Nomor Surat";
    protected static ?string $navigationLabel = "Nomor Surat";
    protected static ?string $pluralModelLabel = "Nomor Surat";
    protected static ?string $navigationIcon = 'fluentui-number-symbol-20';
    protected static ?string $navigationGroup = "Surat Tugas";

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nomor')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('jenis')
                    ->options(Constants::JENIS_NOMOR_SURAT_OPTIONS)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jenis')
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
            'index' => Pages\ListNomorSurats::route('/'),
            'create' => Pages\CreateNomorSurat::route('/create'),
            'edit' => Pages\EditNomorSurat::route('/{record}/edit'),
        ];
    }
}
