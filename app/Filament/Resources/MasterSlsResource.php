<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MasterSlsResource\Pages;
use App\Filament\Resources\MasterSlsResource\RelationManagers;
use App\Models\MasterSls;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MasterSlsResource extends Resource
{
    protected static ?string $model = MasterSls::class;

    protected static ?string $label = "Master SLS";
    protected static ?string $navigationLabel = "Master SLS";
    protected static ?string $pluralModelLabel = "Master SLS";
    protected static ?string $navigationIcon = 'heroicon-o-map';

    public static function canViewAny(): bool{
        return auth()->user()->hasRole('kepala_kantor') || auth()->user()->hasRole('operator_umum');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('desa_kel_id')
                    ->label("ID Desa")
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('kec_id')
                    ->label("ID Kecamatan")
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('sls_id')
                    ->label("ID SLS")
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('provinsi')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('kabkot')
                    ->label("Kabupaten/Kota")
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('kecamatan')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('desa_kel')
                    ->label("Desa/Kelurahan")
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('nama')
                    ->label("Nama SLS")
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('desa_kel_id')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label("ID Desa")
                    ->searchable(),
                Tables\Columns\TextColumn::make('kec_id')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label("ID Kecamatan")
                    ->searchable(),
                Tables\Columns\TextColumn::make('kabkot_id')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label("ID Kabupaten/Kota")
                    ->searchable(),
                Tables\Columns\TextColumn::make('prov_id')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label("ID Provinsi")
                    ->searchable(),
                Tables\Columns\TextColumn::make('sls_id')
                    ->label("ID SLS")
                    ->searchable(),
                Tables\Columns\TextColumn::make('provinsi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kabkot')
                    ->label("Kabupaten/Kota")
                    ->searchable(),
                Tables\Columns\TextColumn::make('kecamatan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('desa_kel')
                    ->label("Desa/Kelurahan")
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama')
                    ->label("Nama SLS")
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
            'index' => Pages\ListMasterSls::route('/'),
            'create' => Pages\CreateMasterSls::route('/create'),
            'edit' => Pages\EditMasterSls::route('/{record}/edit'),
        ];
    }
}
