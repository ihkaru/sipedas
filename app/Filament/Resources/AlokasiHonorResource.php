<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AlokasiHonorResource\Pages;
use App\Filament\Resources\AlokasiHonorResource\RelationManagers;
use App\Models\AlokasiHonor;
use App\Models\Honor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AlokasiHonorResource extends Resource
{
    protected static ?string $model = AlokasiHonor::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';
    protected static ?string $navigationGroup = "Honor Mitra";
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        // Form hanya untuk view/edit, bukan untuk create dari sini
        return $form
            ->schema([
                Forms\Components\Select::make('mitra_id')->relationship('mitra', 'nama_1')->disabled(),
                Forms\Components\Select::make('honor_id')->relationship('honor', 'id')->disabled(),
                Forms\Components\TextInput::make('total_honor')->disabled(),
                Forms\Components\TextInput::make('target_per_satuan_honor')->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('mitra.nama_1')->label('Nama Mitra')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('honor.kegiatanManmit.nama')->label('Kegiatan')->searchable()->sortable()->wrap(),
                Tables\Columns\TextColumn::make('honor.jabatan')->label('Jabatan')->badge(),
                Tables\Columns\TextColumn::make('honor.tanggal_akhir_kegiatan')->date()->label('Tanggal Akhir Kegiatan')->sortable()->badge(),
                Tables\Columns\TextColumn::make('total_honor')->money('IDR')->sortable(),
                // Tables\Columns\TextColumn::make('kontrak.nomor_surat')->label('No. Kontrak')->searchable(),
                // Tables\Columns\TextColumn::make('bast.nomor_surat')->label('No. BAST')->searchable(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime('d M Y')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('honor.tanggal_akhir_kegiatan', 'desc')
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function canCreate(): bool
    {
        return false;
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
            'index' => Pages\ListAlokasiHonors::route('/'),
            'create' => Pages\CreateAlokasiHonor::route('/create'),
            'edit' => Pages\EditAlokasiHonor::route('/{record}/edit'),
        ];
    }
}
