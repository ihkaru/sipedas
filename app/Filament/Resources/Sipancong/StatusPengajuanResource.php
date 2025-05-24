<?php

namespace App\Filament\Resources\Sipancong;

use App\Filament\Resources\Sipancong\StatusPengajuanResource\Pages;
use App\Filament\Resources\Sipancong\StatusPengajuanResource\RelationManagers;
use App\Models\Sipancong\StatusPengajuan;
use Filament\Forms;
use Filament\Forms\Form;
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
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = "Pembayaran";
    protected static ?int $navigationSort = 5;

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
            'index' => Pages\ListStatusPengajuans::route('/'),
            'create' => Pages\CreateStatusPengajuan::route('/create'),
            'edit' => Pages\EditStatusPengajuan::route('/{record}/edit'),
        ];
    }
}
