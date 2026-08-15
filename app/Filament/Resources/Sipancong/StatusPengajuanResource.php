<?php

namespace App\Filament\Resources\Sipancong;

use App\Filament\Resources\Sipancong\StatusPengajuanResource\Pages;
use App\Filament\Resources\Sipancong\StatusPengajuanResource\RelationManagers;
use App\Models\Sipancong\StatusPengajuan;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StatusPengajuanResource extends Resource
{
    protected static ?string $model = StatusPengajuan::class;

    protected static ?string $label = "Status Pengajuan";
    protected static ?string $navigationLabel = "Status Pengajuan";
    protected static ?string $pluralModelLabel = "Status Pengajuan";
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static string | \UnitEnum | null $navigationGroup = "Pembayaran";
    protected static ?int $navigationSort = 5;

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole("super_admin") || auth()->user()->hasRole("operator_umum");
    }


    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
                Forms\Components\TextInput::make('nama')
                    ->required()
                    ->maxLength(50),
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
            'index' => Pages\ListStatusPengajuans::route('/'),
            'create' => Pages\CreateStatusPengajuan::route('/create'),
            'edit' => Pages\EditStatusPengajuan::route('/{record}/edit'),
        ];
    }
}
