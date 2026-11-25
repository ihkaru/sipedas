<?php

namespace App\Filament\Resources\MitraResource\RelationManagers;

use App\Models\Kemitraan;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class KemitraansRelationManager extends RelationManager
{
    protected static string $relationship = 'kemitraans';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
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

                Forms\Components\Select::make('status_rekrutmen')
                    ->label('Status Rekrutmen')
                    ->options(Kemitraan::statusRekrutmenOptions())
                    ->required()
                    ->default(Kemitraan::REKRUTMEN_TERVERIFIKASI),

                Forms\Components\Textarea::make('catatan')
                    ->label('Catatan')
                    ->rows(2)
                    ->columnSpanFull()
                    ->placeholder('Misal: Carry-over dari tahun sebelumnya untuk SPK Pelatihan'),
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
                Tables\Columns\TextColumn::make('status_rekrutmen')
                    ->label('Status Rekrutmen')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        Kemitraan::REKRUTMEN_BELUM => 'Belum Lulus Rekrutmen',
                        Kemitraan::REKRUTMEN_TIDAK_LULUS => 'Tidak Lulus',
                        default => 'Terverifikasi',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        Kemitraan::REKRUTMEN_BELUM => 'warning',
                        Kemitraan::REKRUTMEN_TIDAK_LULUS => 'danger',
                        default => 'success',
                    }),
                Tables\Columns\TextColumn::make('catatan')
                    ->label('Catatan')
                    ->limit(50)
                    ->tooltip(fn (?string $state): ?string => $state)
                    ->placeholder('-'),
            ])
            ->headerActions([
                \Filament\Actions\CreateAction::make(),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
