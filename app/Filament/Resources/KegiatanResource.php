<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KegiatanResource\Pages;
use App\Filament\Resources\KegiatanResource\RelationManagers;
use App\Models\Kegiatan;
use App\Models\Pegawai;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class KegiatanResource extends Resource
{
    protected static ?string $model = Kegiatan::class;

    protected static ?string $label = "Kegiatan";
    protected static ?string $navigationLabel = "Kegiatan";
    protected static ?string $pluralModelLabel = "Kegiatan";
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = "Surat Tugas";

    public static function canViewAny(): bool{
        return auth()->user()->hasRole('kepala_kantor') || auth()->user()->hasRole('operator_umum') || auth()->user()->hasRole('pegawai');
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('tgl_awal_perjadin')
                    ->label("Awal Kegiatan")
                    ->required(),
                Forms\Components\DatePicker::make('tgl_akhir_perjadin')
                    ->label("Akhir Kegiatan")
                    ->required(),
                Forms\Components\Select::make('pj_kegiatan_id')
                    ->options(function(){
                        return Pegawai::get()->pluck('nama','nip');
                    })
                    ->searchable(['nama'])
                    ->label("PJ Kegiatan")
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('tgl_awal_perjadin')
                    ->sortable()
                    ->label("Awal Kegiatan")
                    ->date()
                    ->searchable(),
                Tables\Columns\TextColumn::make('tgl_akhir_perjadin')
                    ->sortable()
                    ->label("Akhir Kegiatan")
                    ->date()
                    ->searchable(),
                Tables\Columns\TextColumn::make('pj_kegiatan_id')
                    ->sortable()
                    ->label("PJ Kegiatan")
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->sortable()
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
            'index' => Pages\ListKegiatans::route('/'),
            'create' => Pages\CreateKegiatan::route('/create'),
            'edit' => Pages\EditKegiatan::route('/{record}/edit'),
        ];
    }
}
