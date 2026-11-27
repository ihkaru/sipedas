<?php

namespace App\Filament\Resources;

use App\Filament\Actions\CetakUlangLampiranKontrakAction;
use App\Filament\Resources\AlokasiHonorResource\Pages;
use App\Models\AlokasiHonor;
use App\Services\LampiranKontrakService;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AlokasiHonorResource extends Resource
{
    protected static ?string $model = AlokasiHonor::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-check';
    protected static string | \UnitEnum | null $navigationGroup = "Honor Mitra";
    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        // Form hanya untuk view/edit, bukan untuk create dari sini
        return $schema->schema([
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
                Tables\Columns\TextColumn::make('honor.kegiatanManmit.jenis_kegiatan')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        LampiranKontrakService::JENIS_SENSUS => 'warning',
                        default => 'success',
                    }),
                Tables\Columns\TextColumn::make('honor.jabatan')->label('Jabatan')->badge(),
                Tables\Columns\TextColumn::make('honor.tanggal_akhir_kegiatan')->date()->label('Tanggal Akhir Kegiatan')->sortable()->badge(),
                Tables\Columns\TextColumn::make('total_honor')->money('IDR')->sortable(),
                // Tables\Columns\TextColumn::make('kontrak.nomor_surat')->label('No. Kontrak')->searchable(),
                // Tables\Columns\TextColumn::make('bast.nomor_surat')->label('No. BAST')->searchable(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime('d M Y')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('honor.tanggal_akhir_kegiatan', 'desc')
            ->filters([
                Filter::make('periode_akhir_kegiatan')
                    ->label('Periode Akhir Kegiatan')
                    ->form([
                        Forms\Components\Select::make('bulan')
                            ->label('Bulan')
                            ->options(CetakUlangLampiranKontrakAction::monthOptions())
                            ->placeholder('Semua Bulan'),
                        Forms\Components\Select::make('tahun')
                            ->label('Tahun')
                            ->options(CetakUlangLampiranKontrakAction::yearOptions())
                            ->placeholder('Semua Tahun'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                ! empty($data['tahun']),
                                fn (Builder $q): Builder => $q->whereHas(
                                    'honor',
                                    fn (Builder $h): Builder => $h->whereYear('tanggal_akhir_kegiatan', (int) $data['tahun'])
                                )
                            )
                            ->when(
                                ! empty($data['bulan']),
                                fn (Builder $q): Builder => $q->whereHas(
                                    'honor',
                                    fn (Builder $h): Builder => $h->whereMonth('tanggal_akhir_kegiatan', (int) $data['bulan'])
                                )
                            );
                    }),

                SelectFilter::make('jenis_kegiatan')
                    ->label('Jenis Kegiatan')
                    ->options([
                        LampiranKontrakService::JENIS_SURVEI => 'Survei',
                        LampiranKontrakService::JENIS_SENSUS => 'Sensus',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        if ($data['value'] === LampiranKontrakService::JENIS_SENSUS) {
                            return $query->whereHas('honor.kegiatanManmit', fn (Builder $q): Builder => $q->where('jenis_kegiatan', LampiranKontrakService::JENIS_SENSUS));
                        }

                        return $query->whereHas('honor.kegiatanManmit', function (Builder $q): void {
                            $q->where('jenis_kegiatan', '!=', LampiranKontrakService::JENIS_SENSUS)
                                ->orWhereNull('jenis_kegiatan');
                        });
                    }),
            ])
            ->actions([
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
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
