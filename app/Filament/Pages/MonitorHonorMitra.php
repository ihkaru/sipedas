<?php

namespace App\Filament\Pages;

use App\Exports\MonitorHonorMitraExport;
use App\Models\AlokasiHonor;
use App\Models\MasterSls;
use App\Models\Mitra;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class MonitorHonorMitra extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?string $navigationLabel = 'Monitor Honor Mitra';

    protected static ?string $title = 'Monitoring Honor Bulanan Mitra';

    protected static ?string $slug = 'monitor-honor-mitra';

    protected static string|\UnitEnum|null $navigationGroup = 'Monitoring';

    protected string $view = 'filament.pages.monitor-honor-mitra';

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
                $year = $this->tableFilters['periode']['year'] ?? now()->year;

                $targetStart = Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
                $targetEnd = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

                $proportionSql = "
                    FLOOR(alokasi_honors.total_honor * 
                        (DATEDIFF(LEAST(alokasi_honors.tanggal_akhir_perjanjian, '$targetEnd'), GREATEST(alokasi_honors.tanggal_mulai_perjanjian, '$targetStart')) + 1) 
                        / 
                        (DATEDIFF(alokasi_honors.tanggal_akhir_perjanjian, alokasi_honors.tanggal_mulai_perjanjian) + 1)
                    )
                ";

                return Mitra::query()
                    ->select('mitras.*')
                    ->addSelect([
                        'total_honor_sensus' => AlokasiHonor::query()
                            ->selectRaw("COALESCE(SUM(CASE WHEN kegiatan_manmits.jenis_kegiatan = 'SENSUS' THEN $proportionSql ELSE 0 END), 0)")
                            ->join('honors', 'alokasi_honors.honor_id', '=', 'honors.id')
                            ->join('kegiatan_manmits', 'honors.kegiatan_manmit_id', '=', 'kegiatan_manmits.id')
                            ->whereColumn('alokasi_honors.mitra_id', 'mitras.id')
                            ->where('alokasi_honors.tanggal_mulai_perjanjian', '<=', $targetEnd)
                            ->where('alokasi_honors.tanggal_akhir_perjanjian', '>=', $targetStart),

                        'total_honor_survei' => AlokasiHonor::query()
                            ->selectRaw("COALESCE(SUM(CASE WHEN kegiatan_manmits.jenis_kegiatan != 'SENSUS' OR kegiatan_manmits.jenis_kegiatan IS NULL THEN $proportionSql ELSE 0 END), 0)")
                            ->leftJoin('honors', 'alokasi_honors.honor_id', '=', 'honors.id')
                            ->leftJoin('kegiatan_manmits', 'honors.kegiatan_manmit_id', '=', 'kegiatan_manmits.id')
                            ->whereColumn('alokasi_honors.mitra_id', 'mitras.id')
                            ->where('alokasi_honors.tanggal_mulai_perjanjian', '<=', $targetEnd)
                            ->where('alokasi_honors.tanggal_akhir_perjanjian', '>=', $targetStart),
                    ]);
            })
            ->defaultSort('total_honor_survei', 'desc')
            ->columns([
                TextColumn::make('id_sobat')
                    ->label('ID Sobat')
                    ->searchable(),

                TextColumn::make('nama_1')
                    ->label('Nama Mitra')
                    ->searchable(),

                TextColumn::make('rekomendasi_alokasi')
                    ->label('Kesiapan')
                    ->badge()
                    ->getStateUsing(function ($record) use ($limitSensus, $limitSurvei) {
                        $s = (float) $record->total_honor_sensus;
                        $v = (float) $record->total_honor_survei;
                        if ($s > $limitSensus || $v > $limitSurvei) {
                            return 'Over Limit';
                        }
                        if ($s >= $limitSensus * 0.8 || $v >= $limitSurvei * 0.8) {
                            return 'Mendekati Limit';
                        }
                        if ($s == 0 && $v == 0) {
                            return 'Bebas Tugas';
                        }

                        return 'Ada Kuota';
                    })
                    ->color(fn ($state) => match ($state) {
                        'Bebas Tugas' => 'success',
                        'Ada Kuota' => 'info',
                        'Mendekati Limit' => 'warning',
                        'Over Limit' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('kecamatan_name')
                    ->label('Kecamatan')
                    ->searchable(['alamat_kec']),

                TextColumn::make('desa_name')
                    ->label('Desa/Kelurahan')
                    ->searchable(['alamat_desa']),

                TextColumn::make('nomor_wa')
                    ->label('Kontak WA')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->formatStateUsing(fn ($state, $record) => $record->nomor_wa ?: ($record->no_telp ?: '-'))
                    ->url(fn ($record) => $record->whatsapp_url)
                    ->openUrlInNewTab()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('bulan_kontrak')
                    ->label('Bulan Kontrak')
                    ->getStateUsing(function () {
                        $month = $this->tableFilters['periode']['month'] ?? now()->month;
                        $year = $this->tableFilters['periode']['year'] ?? now()->year;

                        return $this->getMonthName((int) $month).' '.$year;
                    }),

                TextColumn::make('total_honor_sensus')
                    ->label('Honor Sensus')
                    ->money('IDR')
                    ->sortable()
                    ->default(0)
                    ->color(fn ($state) => match (true) {
                        $state > $limitSensus => 'danger',
                        $state > $limitSensus * 0.8 => 'warning',
                        default => 'success',
                    })
                    ->description(fn ($record) => 'Sisa: Rp '.number_format(max(0, $limitSensus - (float) $record->total_honor_sensus), 0, ',', '.')),

                TextColumn::make('total_honor_survei')
                    ->label('Honor Survei')
                    ->money('IDR')
                    ->sortable()
                    ->default(0)
                    ->color(fn ($state) => match (true) {
                        $state > $limitSurvei => 'danger',
                        $state > $limitSurvei * 0.8 => 'warning',
                        default => 'success',
                    })
                    ->description(fn ($record) => 'Sisa: Rp '.number_format(max(0, $limitSurvei - (float) $record->total_honor_survei), 0, ',', '.')),

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
                $year = $this->tableFilters['periode']['year'] ?? now()->year;

                return DetailHonorMitra::getUrl(['mitra' => $record->id, 'month' => $month, 'year' => $year]);
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
                            ->options(array_combine(range(now()->year, now()->year - 5), range(now()->year, now()->year - 5)))
                            ->default(now()->year),
                    ])
                    ->query(fn (Builder $query) => $query),

                SelectFilter::make('status_ketersediaan')
                    ->label('Kesiapan Alokasi')
                    ->options([
                        'bebas' => 'Siap Ditugaskan (Honor Rp 0)',
                        'ada_kuota' => 'Bisa Ditugaskan (Masih Ada Kuota)',
                        'mendekati_limit' => 'Mendekati Batas (>= 80% Kuota)',
                        'over_limit' => 'Tidak Bisa (Over Limit)',
                    ])
                    ->query(function (Builder $query, array $data) use ($limitSensus, $limitSurvei): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        return match ($data['value']) {
                            'bebas' => $query->havingRaw('total_honor_survei = 0 AND total_honor_sensus = 0'),
                            'ada_kuota' => $query->havingRaw('(total_honor_survei < ? AND total_honor_sensus < ?)', [$limitSurvei, $limitSensus]),
                            'mendekati_limit' => $query->havingRaw('((total_honor_survei >= ? OR total_honor_sensus >= ?) AND (total_honor_survei <= ? AND total_honor_sensus <= ?))', [$limitSurvei * 0.8, $limitSensus * 0.8, $limitSurvei, $limitSensus]),
                            'over_limit' => $query->havingRaw('(total_honor_survei > ? OR total_honor_sensus > ?)', [$limitSurvei, $limitSensus]),
                            default => $query,
                        };
                    }),

                SelectFilter::make('kecamatan')
                    ->label('Kecamatan')
                    ->searchable()
                    ->options(fn () => MasterSls::query()->distinct()->pluck('kecamatan', 'kec_id')->all())
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }
                        $k = $data['value'];

                        return $query->where('mitras.alamat_prov', substr($k, 0, 2))
                            ->where('mitras.alamat_kab', substr($k, 2, 2))
                            ->where('mitras.alamat_kec', substr($k, 4, 3));
                    }),

                SelectFilter::make('desa')
                    ->label('Desa/Kelurahan')
                    ->searchable()
                    ->options(fn () => MasterSls::query()->distinct()->pluck('desa_kel', 'desa_kel_id')->all())
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }
                        $d = $data['value'];

                        return $query->where('mitras.alamat_prov', substr($d, 0, 2))
                            ->where('mitras.alamat_kab', substr($d, 2, 2))
                            ->where('mitras.alamat_kec', substr($d, 4, 3))
                            ->where('mitras.alamat_desa', substr($d, 7, 3));
                    }),
            ])
            ->filtersLayout(FiltersLayout::AboveContent)
            ->headerActions([
                Action::make('export_excel')
                    ->label('Export Excel Perencanaan')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function () use ($limitSensus, $limitSurvei) {
                        $month = (int) ($this->tableFilters['periode']['month'] ?? now()->month);
                        $year = (int) ($this->tableFilters['periode']['year'] ?? now()->year);
                        $monthName = $this->getMonthName($month);

                        $kecVal = $this->tableFilters['kecamatan']['value'] ?? null;
                        $desaVal = $this->tableFilters['desa']['value'] ?? null;
                        $kecLabel = $kecVal ? MasterSls::where('kec_id', $kecVal)->value('kecamatan') : null;
                        $desaLabel = $desaVal ? MasterSls::where('desa_kel_id', $desaVal)->value('desa_kel') : null;

                        $query = method_exists($this, 'getTableQueryForExport')
                            ? $this->getTableQueryForExport()
                            : $this->getFilteredTableQuery();

                        $filename = sprintf('Perencanaan_Alokasi_Mitra_%s_%d_%s.xlsx', $monthName, $year, now()->format('Ymd_His'));

                        Notification::make()
                            ->title('Export Berhasil')
                            ->body("Data perencanaan mitra periode {$monthName} {$year} sedang diunduh...")
                            ->success()
                            ->send();

                        return Excel::download(
                            new MonitorHonorMitraExport($query, $month, $year, $limitSensus, $limitSurvei, $kecLabel, $desaLabel),
                            $filename
                        );
                    }),

                Action::make('limit_info')
                    ->label('Batas Sensus: Rp '.number_format($limitSensus, 0, ',', '.').' | Batas Survei: Rp '.number_format($limitSurvei, 0, ',', '.'))
                    ->icon('heroicon-o-information-circle')
                    ->color('gray')
                    ->disabled(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('export_selected')
                        ->label('Export Mitra Terpilih')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->action(function (Collection $records) use ($limitSensus, $limitSurvei) {
                            $month = (int) ($this->tableFilters['periode']['month'] ?? now()->month);
                            $year = (int) ($this->tableFilters['periode']['year'] ?? now()->year);
                            $filename = sprintf('Perencanaan_Mitra_Terpilih_%s_%d_%s.xlsx', $this->getMonthName($month), $year, now()->format('Ymd_His'));

                            return Excel::download(
                                new MonitorHonorMitraExport($records, $month, $year, $limitSensus, $limitSurvei),
                                $filename
                            );
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}
