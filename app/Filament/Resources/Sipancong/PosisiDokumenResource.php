<?php

namespace App\Filament\Resources\Sipancong;

use App\Filament\Resources\Sipancong\PosisiDokumenResource\Pages;
use App\Filament\Resources\Sipancong\PosisiDokumenResource\RelationManagers;
use App\Models\Sipancong\PosisiDokumen;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PosisiDokumenResource extends Resource
{
    protected static ?string $model = PosisiDokumen::class;

    protected static ?string $label = "Posisi Dokumen";
    protected static ?string $navigationLabel = "Posisi Dokumen";
    protected static ?string $pluralModelLabel = "Posisi Dokumen";
    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-up';
    protected static ?string $navigationGroup = "Pembayaran";
    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole("super_admin") || auth()->user()->hasRole("operator_umum");
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
            'index' => Pages\ListPosisiDokumens::route('/'),
            'create' => Pages\CreatePosisiDokumen::route('/create'),
            'edit' => Pages\EditPosisiDokumen::route('/{record}/edit'),
        ];
    }
}
