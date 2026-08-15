<?php

namespace App\Filament\Resources\Sipancong;

use App\Filament\Resources\Sipancong\JenisDokumenResource\Pages;
use App\Filament\Resources\Sipancong\JenisDokumenResource\RelationManagers;
use App\Models\Sipancong\JenisDokumen;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class JenisDokumenResource extends Resource
{
    protected static ?string $model = JenisDokumen::class;

    protected static ?string $label = "Jenis Dokumen";
    protected static ?string $navigationLabel = "Jenis Dokumen";
    protected static ?string $pluralModelLabel = "Jenis Dokumen";
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document';
    protected static string | \UnitEnum | null $navigationGroup = "Pembayaran";
    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole("super_admin") || auth()->user()->hasRole("operator_umum");
    }


    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
                Forms\Components\TextInput::make('nama')
                    ->required()
                    ->maxLength(10),
                Forms\Components\TextInput::make('deskripsi')
                    ->maxLength(255)
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('deskripsi')
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
                \Filament\Actions\EditAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListJenisDokumens::route('/'),
            'create' => Pages\CreateJenisDokumen::route('/create'),
            'edit' => Pages\EditJenisDokumen::route('/{record}/edit'),
        ];
    }
}
