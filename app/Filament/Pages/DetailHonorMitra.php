<?php

namespace App\Filament\Pages;

use App\Models\AlokasiHonor;
use App\Models\Mitra;
use App\Filament\Pages\MonitorHonorMitra;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Database\Eloquent\Builder;

class DetailHonorMitra extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $title = 'Detail Kegiatan Mitra';
    protected static ?string $slug = 'detail-honor-mitra';
    protected static bool $shouldRegisterNavigation = false; // Hidden from sidebar

    protected static string $view = 'filament.pages.detail-honor-mitra';

    public ?int $mitraId = null;
    public ?int $month = null;
    public ?int $year = null;

    public function mount(): void
    {
        $this->mitraId = request()->query('mitra');
        $this->month = request()->query('month', now()->month);
        $this->year = request()->query('year', now()->year);

        if (!$this->mitraId) {
            redirect()->route('filament.admin.pages.monitor-honor-mitra');
        }
    }

    public function getTitle(): string
    {
        $mitra = Mitra::find($this->mitraId);
        return $mitra ? "Detail Honor: " . $mitra->nama_1 : "Detail Kegiatan Mitra";
    }

    public function getSensusTotal()
    {
        $month = $this->tableFilters['periode']['month'] ?? $this->month;
        $year  = $this->tableFilters['periode']['year']  ?? $this->year;

        return AlokasiHonor::query()
            ->where('mitra_id', $this->mitraId)
            ->whereMonth('tanggal_mulai_perjanjian', $month)
            ->whereYear('tanggal_mulai_perjanjian', $year)
            ->whereHas('honor.kegiatanManmit', fn($q) => $q->where('jenis_kegiatan', 'SENSUS'))
            ->sum('total_honor');
    }

    public function getSurveiTotal()
    {
        $month = $this->tableFilters['periode']['month'] ?? $this->month;
        $year  = $this->tableFilters['periode']['year']  ?? $this->year;

        return AlokasiHonor::query()
            ->where('mitra_id', $this->mitraId)
            ->whereMonth('tanggal_mulai_perjanjian', $month)
            ->whereYear('tanggal_mulai_perjanjian', $year)
            ->whereHas('honor.kegiatanManmit', fn($q) => $q->where('jenis_kegiatan', '!=', 'SENSUS'))
            ->sum('total_honor');
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('back')
                ->label('Kembali')
                ->url(fn() => MonitorHonorMitra::getUrl())
                ->color('gray')
                ->icon('heroicon-o-arrow-left'),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $month = $this->tableFilters['periode']['month'] ?? $this->month;
                $year  = $this->tableFilters['periode']['year']  ?? $this->year;

                return AlokasiHonor::query()
                    ->where('mitra_id', $this->mitraId)
                    ->whereMonth('tanggal_mulai_perjanjian', $month)
                    ->whereYear('tanggal_mulai_perjanjian', $year);
            })
            ->columns([
                TextColumn::make('honor.kegiatanManmit.nama')
                    ->label('Kegiatan')
                    ->wrap(),
                
                TextColumn::make('honor.kegiatanManmit.jenis_kegiatan')
                    ->label('Jenis Honor'),
                
                TextColumn::make('tanggal_mulai_perjanjian')
                    ->label('Mulai')
                    ->date('d M Y'),
                
                TextColumn::make('tanggal_akhir_perjanjian')
                    ->label('Selesai')
                    ->date('d M Y'),
                
                TextColumn::make('target_per_satuan_honor')
                    ->label('Target')
                    ->numeric(),
                
                TextColumn::make('total_honor')
                    ->label('Honor')
                    ->money('IDR'),
            ])
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
                            ->default($this->month),

                        Select::make('year')
                            ->label('Tahun')
                            ->options(array_combine(
                                range(now()->year, now()->year - 5),
                                range(now()->year, now()->year - 5)
                            ))
                            ->default($this->year),
                    ])
                    ->query(fn (Builder $query) => $query),
            ])
            ->filtersLayout(FiltersLayout::AboveContent);
    }
}
