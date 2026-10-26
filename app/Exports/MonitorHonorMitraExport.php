<?php

namespace App\Exports;

use App\Exports\Sheets\MonitorHonorMitraDetailSheet;
use App\Exports\Sheets\MonitorHonorMitraSummarySheet;
use App\Models\AlokasiHonor;
use App\Models\Mitra;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MonitorHonorMitraExport implements WithMultipleSheets
{
    protected Builder|Collection|null $queryOrRecords;

    protected int $month;

    protected int $year;

    protected float $limitSensus;

    protected float $limitSurvei;

    protected ?string $kecamatanLabel;

    protected ?string $desaLabel;

    public function __construct(
        Builder|Collection|null $queryOrRecords = null,
        ?int $month = null,
        ?int $year = null,
        ?float $limitSensus = null,
        ?float $limitSurvei = null,
        ?string $kecamatanLabel = null,
        ?string $desaLabel = null
    ) {
        $this->queryOrRecords = $queryOrRecords;
        $this->month = $month ?? now()->month;
        $this->year = $year ?? now()->year;
        $this->limitSensus = $limitSensus ?? 4694000.0;
        $this->limitSurvei = $limitSurvei ?? 3353000.0;
        $this->kecamatanLabel = $kecamatanLabel;
        $this->desaLabel = $desaLabel;
    }

    public function sheets(): array
    {
        $records = $this->resolveRecords();

        if ($records instanceof \Illuminate\Database\Eloquent\Collection) {
            $records->loadMissing('kemitraans');
        }

        $alokasisByMitra = $this->loadAlokasis($records);

        return [
            new MonitorHonorMitraDetailSheet(
                $records,
                $alokasisByMitra,
                $this->month,
                $this->year,
                $this->limitSensus,
                $this->limitSurvei,
                $this->kecamatanLabel,
                $this->desaLabel
            ),
            new MonitorHonorMitraSummarySheet(
                $records,
                $alokasisByMitra,
                $this->month,
                $this->year,
                $this->limitSensus,
                $this->limitSurvei,
                $this->kecamatanLabel,
                $this->desaLabel
            ),
        ];
    }

    protected function resolveRecords(): Collection
    {
        if ($this->queryOrRecords instanceof Collection) {
            return $this->queryOrRecords;
        }

        if ($this->queryOrRecords instanceof Builder) {
            return (clone $this->queryOrRecords)->get();
        }

        return Mitra::all();
    }

    protected function loadAlokasis(Collection $records): Collection
    {
        $mitraIds = $records->pluck('id')->filter()->values();
        if ($mitraIds->isEmpty()) {
            return collect();
        }

        $targetStart = Carbon::create($this->year, $this->month, 1)->startOfMonth()->toDateString();
        $targetEnd = Carbon::create($this->year, $this->month, 1)->endOfMonth()->toDateString();

        return AlokasiHonor::query()
            ->whereIn('mitra_id', $mitraIds)
            ->where('tanggal_mulai_perjanjian', '<=', $targetEnd)
            ->where('tanggal_akhir_perjanjian', '>=', $targetStart)
            ->with(['honor.kegiatanManmit'])
            ->get()
            ->groupBy('mitra_id');
    }
}
