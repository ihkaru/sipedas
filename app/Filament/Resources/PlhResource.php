<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlhResource\Pages;
use App\Filament\Resources\PlhResource\RelationManagers;
use App\Models\Pegawai;
use App\Models\Pengaturan;
use App\Models\Plh;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use PHPUnit\TextUI\Configuration\Constant;

class PlhResource extends Resource
{
    protected static ?string $model = Plh::class;

    protected static ?string $label = "PLH";
    protected static ?string $navigationLabel = "PLH";
    protected static ?string $pluralModelLabel = "PLH";
    protected static ?string $navigationIcon = 'fluentui-people-swap-16-o';
    protected static ?string $navigationGroup = "Surat Tugas";
    protected static ?int $navigationSort = 7;


    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole('kepala_satker') || auth()->user()->hasRole('operator_umum');
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('pegawai_digantikan_id')
                    ->default(Pengaturan::key('ID_PLH_DEFAULT')->nilai),
                Select::make('pegawai_pengganti_id')
                    ->options(function () {
                        return Pegawai::pluck('nama', 'nip');
                    })
                    ->searchable(['nama'])
                    ->label("Pegawai Pengganti"),
                Forms\Components\DatePicker::make('tgl_mulai')
                    ->label("Tanggal Mulai"),
                Forms\Components\DatePicker::make('tgl_selesai')
                    ->label("Tanggal Selesai"),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pegawaiPengganti.nama')
                    ->label("Pegawai Pengganti")
                    ->searchable(),
                Tables\Columns\TextColumn::make('pegawaiDigantikan.nama')
                    ->label("Kepala")
                    ->searchable(),
                Tables\Columns\TextColumn::make('tgl_mulai')
                    ->label("Tanggal Mulai")
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tgl_selesai')
                    ->label("Tanggal Selesai")
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->date()
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
