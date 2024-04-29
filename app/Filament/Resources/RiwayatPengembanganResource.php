<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RiwayatPengembanganResource\Pages;
use App\Filament\Resources\RiwayatPengembanganResource\RelationManagers;
use App\Models\RiwayatPengembangan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RiwayatPengembanganResource extends Resource
{
    protected static ?string $model = RiwayatPengembangan::class;

    protected static ?string $label = "Riwayat Pengembangan";
    protected static ?string $navigationLabel = "Riwayat Pengembangan";
    protected static ?string $pluralModelLabel = "Riwayat Pengembangan";
    protected static ?string $navigationIcon = 'fluentui-branch-request-20';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('versi')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('deskripsi')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('versi')
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
            'index' => Pages\ListRiwayatPengembangans::route('/'),
            'create' => Pages\CreateRiwayatPengembangan::route('/create'),
            'edit' => Pages\EditRiwayatPengembangan::route('/{record}/edit'),
        ];
    }
}
