<?php

namespace App\Filament\Resources\MitraResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class KemitraansRelationManager extends RelationManager
{
    protected static string $relationship = 'kemitraans';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('tahun')
                    ->required()
                    ->numeric()
                    ->minValue(2020)
                    ->maxValue(date('Y') + 5), // Batasi input tahun

                Forms\Components\Select::make('status')
                    ->options([
                        'AKTIF' => 'Aktif',
                        'TIDAK_AKTIF' => 'Tidak Aktif',
                        'BLACKLISTED' => 'Blacklisted',
                    ])
                    ->required()
                    ->default('AKTIF'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('tahun')
            ->columns([
                Tables\Columns\TextColumn::make('tahun')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'AKTIF' => 'success',
                        'TIDAK_AKTIF' => 'warning',
                        'BLACKLISTED' => 'danger',
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
