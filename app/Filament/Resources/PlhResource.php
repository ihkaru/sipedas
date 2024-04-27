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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('pegawai_peganti_id')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('pegawai_diganti_id')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\DateTimePicker::make('tgl_mulai'),
                Forms\Components\DateTimePicker::make('tgl_selesai'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pegawai_peganti_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pegawai_diganti_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tgl_mulai')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tgl_selesai')
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
            'index' => Pages\ListPlhs::route('/'),
            'create' => Pages\CreatePlh::route('/create'),
            'edit' => Pages\EditPlh::route('/{record}/edit'),
        ];
    }
}
