<?php

namespace App\Filament\Pages;

use App\Models\Mitra;
use App\Models\Setting;
use App\Filament\Pages\DetailHonorMitra;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MonitorHonorMitra extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $navigationLabel = 'Monitor Honor Mitra';
    protected static ?string $title = 'Monitoring Honor Bulanan Mitra';
    protected static ?string $slug = 'monitor-honor-mitra';
    protected static ?string $navigationGroup = 'Monitoring';

    protected static string $view = 'filament.pages.monitor-honor-mitra';

    // State filter periode
    public int $filterMonth;
    public int $filterYear;

    public function mount(): void
    {
        $this->filterMonth = now()->month;
        $this->filterYear = now()->year;
    }

    public static function canAccess(): bool
    {
        return true;
    }

    private function getMonthName(int $month): string
    {
        return [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ][$month] ?? '-';
    }

    public function table(Table $table): Table
    {
        $limitSensus = (float) (Setting::where('key', 'STANDAR_BIAYA_MASUKAN_LAINNYA_NON_PNS_OB_PETUGAS_PENDATAAN_LAPANGAN_SENSUS')->first()?->value ?? 4694000);
        $limitSurvei = (float) (Setting::where('key', 'STANDAR_BIAYA_MASUKAN_LAINNYA_NON_PNS_OB_PETUGAS_PENDATAAN_LAPANGAN_SURVEI')->first()?->value ?? 3353000);

        return $table
            ->query(function () {
                $month = $this->tableFilters['periode']['month'] ?? now()->month;
                $year  = $this->tableFilters['periode']['year']  ?? now()->year;

                $targetStart = \Illuminate\Support\Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
                $targetEnd = \Illuminate\Support\Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

                // Rumus Proporsi SQL: 
                // floor(total_honor * (hari_irisan) / (total_hari_kontrak))
                // hari_irisan = DATEDIFF(LEAST(akhir_kontrak, target_akhir), GREATEST(mulai_kontrak, target_awal)) + 1
                // total_hari_kontrak = DATEDIFF(akhir_kontrak, mulai_kontrak) + 1
                
                $proportionSql = "
                    FLOOR(alokasi_honors.total_honor * 
                        (DATEDIFF(LEAST(alokasi_honors.tanggal_akhir_perjanjian, '$targetEnd'), GREATEST(alokasi_honors.tanggal_mulai_perjanjian, '$targetStart')) + 1) 
                        / 
                        (DATEDIFF(alokasi_honors.tanggal_akhir_perjanjian, alokasi_honors.tanggal_mulai_perjanjian) + 1)
                    )
                ";

                return Mitra::query()
                    ->select('mitras.*')
                    ->leftJoin('alokasi_honors', function ($join) use ($targetStart, $targetEnd) {
                        $join->on('mitras.id', '=', 'alokasi_honors.mitra_id')
                             ->where('alokasi_honors.tanggal_mulai_perjanjian', '<=', $targetEnd)
                             ->where('alokasi_honors.tanggal_akhir_perjanjian', '>=', $targetStart);
                    })
                    ->leftJoin('honors', 'alokasi_honors.honor_id', '=', 'honors.id')
                    ->leftJoin('kegiatan_manmits', 'honors.kegiatan_manmit_id', '=', 'kegiatan_manmits.id')
                    ->selectRaw("COALESCE(SUM(CASE WHEN kegiatan_manmits.jenis_kegiatan = 'SENSUS' THEN $proportionSql ELSE 0 END), 0) as total_honor_sensus")
                    ->selectRaw("COALESCE(SUM(CASE WHEN kegiatan_manmits.jenis_kegiatan != 'SENSUS' OR kegiatan_manmits.jenis_kegiatan IS NULL THEN $proportionSql ELSE 0 END), 0) as total_honor_survei")
                    ->groupBy('mitras.id', 'mitras.id_sobat', 'mitras.nama_1', 'mitras.alamat_kec', 'mitras.alamat_desa', 'mitras.alamat_prov', 'mitras.alamat_kab');
            })
            ->defaultSort('total_honor_survei', 'desc')
            ->columns([
                TextColumn::make('id_sobat')
                    ->label('ID Sobat')
                    ->searchable(),

                TextColumn::make('nama_1')
                    ->label('Nama Mitra')
                    ->searchable(),

                TextColumn::make('kecamatan_name')
                    ->label('Kecamatan')
                    ->searchable(['alamat_kec']),

                TextColumn::make('desa_name')
                    ->label('Desa/Kelurahan')
                    ->searchable(['alamat_desa']),

                TextColumn::make('bulan_kontrak')
                    ->label('Bulan Kontrak')
                    ->getStateUsing(function () {
                        $month = $this->tableFilters['periode']['month'] ?? now()->month;
                        $year  = $this->tableFilters['periode']['year']  ?? now()->year;

                        return $this->getMonthName((int) $month) . ' ' . $year;
                    }),

                TextColumn::make('total_honor_sensus')
                    ->label('Honor Sensus')
                    ->money('IDR')
                    ->sortable()
                    ->default(0)
                    ->color(fn ($state) => match (true) {
                        $state > $limitSensus        => 'danger',
                        $state > $limitSensus * 0.8  => 'warning',
                        default                => 'success',
                    })
                    ->description(function ($record) use ($limitSensus) {
                        $sisa = $limitSensus - (float) $record->total_honor_sensus;
                        return "Sisa: Rp " . number_format(max(0, $sisa), 0, ',', '.');
                    }),

                TextColumn::make('total_honor_survei')
                    ->label('Honor Survei')
                    ->money('IDR')
                    ->sortable()
                    ->default(0)
                    ->color(fn ($state) => match (true) {
                        $state > $limitSurvei        => 'danger',
                        $state > $limitSurvei * 0.8  => 'warning',
                        default                => 'success',
                    })
                    ->description(function ($record) use ($limitSurvei) {
                        $sisa = $limitSurvei - (float) $record->total_honor_survei;
                        return "Sisa: Rp " . number_format(max(0, $sisa), 0, ',', '.');
                    }),

                IconColumn::make('status_limit')
                    ->label('Status')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->total_honor_sensus <= $limitSensus && $record->total_honor_survei <= $limitSurvei)
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-exclamation-triangle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->recordUrl(function ($record) {
                $month = $this->tableFilters['periode']['month'] ?? now()->month;
                $year  = $this->tableFilters['periode']['year']  ?? now()->year;

                return DetailHonorMitra::getUrl([
                    'mitra' => $record->id,
                    'month' => $month,
                    'year' => $year,
                ]);
            })
            ->filters([
                Filter::make('periode')
                    ->form([
                        Select::make('month')
                            ->label('Bulan')
                            ->options([
                                1 => 'Januari', 2 => 'Februari', 3 => 'Maret',  4 => 'April',
                                5 => 'Mei',     6 => 'Juni',     7 => 'Juli',   8 => 'Agustus',
                                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                            ])
                            ->default(now()->month),

                        Select::make('year')
                            ->label('Tahun')
                            ->options(array_combine(
                                range(now()->year, now()->year - 5),
                                range(now()->year, now()->year - 5)
                            ))
                            ->default(now()->year),
                    ])
                    ->query(fn (Builder $query) => $query), // Query handled in table->query()

                \Filament\Tables\Filters\SelectFilter::make('kecamatan')
                    ->label('Kecamatan')
                    ->searchable()
                    ->options(
                        fn() => \App\Models\MasterSls::query()
                            ->distinct()
                            ->pluck('kecamatan', 'kec_id')
                            ->all()
                    )
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }
                        $kecId = $data['value'];
                        $provId = substr($kecId, 0, 2);
                        $kabId = substr($kecId, 2, 2);
                        $kecOnlyId = substr($kecId, 4, 3);

                        return $query
                            ->where('mitras.alamat_prov', $provId)
                            ->where('mitras.alamat_kab', $kabId)
                            ->where('mitras.alamat_kec', $kecOnlyId);
                    }),

                \Filament\Tables\Filters\SelectFilter::make('desa')
                    ->label('Desa/Kelurahan')
                    ->searchable()
                    ->options(
                        fn() => \App\Models\MasterSls::query()
                            ->distinct()
                            ->pluck('desa_kel', 'desa_kel_id')
                            ->all()
                    )
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }
                        $desaId = $data['value'];
                        $provId = substr($desaId, 0, 2);
                        $kabId = substr($desaId, 2, 2);
                        $kecId = substr($desaId, 4, 3);
                        $desaOnlyId = substr($desaId, 7, 3);

                        return $query
                            ->where('mitras.alamat_prov', $provId)
                            ->where('mitras.alamat_kab', $kabId)
                            ->where('mitras.alamat_kec', $kecId)
                            ->where('mitras.alamat_desa', $desaOnlyId);
                    }),
            ])
            ->filtersLayout(FiltersLayout::AboveContent)
            ->headerActions([
                Action::make('limit_info')
                    ->label('Batas Sensus: Rp ' . number_format($limitSensus, 0, ',', '.') . ' | Batas Survei: Rp ' . number_format($limitSurvei, 0, ',', '.'))
                    ->icon('heroicon-o-information-circle')
                    ->color('gray')
                    ->disabled(),
            ]);
    }
}
