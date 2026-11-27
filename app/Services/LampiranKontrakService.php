<?php

namespace App\Services;

use App\Models\AlokasiHonor;
use App\Models\Honor;
use App\Models\KegiatanManmit;
use App\Models\Mitra;
use App\Models\NomorSurat;
use App\Supports\Constants;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class LampiranKontrakService
{
    public const JENIS_ALL = 'ALL';

    public const JENIS_SURVEI = 'SURVEI';

    public const JENIS_SENSUS = 'SENSUS';

    /**
     * Normalisasi parameter filter jenis kegiatan.
     */
    public static function normalizeJenisKegiatan(?string $jenisKegiatan): string
    {
        $normalized = strtoupper(trim((string) $jenisKegiatan));

        return match ($normalized) {
            self::JENIS_SURVEI => self::JENIS_SURVEI,
            self::JENIS_SENSUS => self::JENIS_SENSUS,
            default => self::JENIS_ALL,
        };
    }

    /**
     * Opsi dropdown jenis kegiatan untuk UI Filament.
     *
     * @return array<string, string>
     */
    public static function jenisKegiatanOptions(): array
    {
        return [
            self::JENIS_ALL => 'Semua (Survei & Sensus)',
            self::JENIS_SURVEI => 'Survei Saja',
            self::JENIS_SENSUS => 'Sensus Saja',
        ];
    }

    /**
     * Label manusiawi untuk jenis kegiatan.
     */
    public static function jenisKegiatanLabel(?string $jenisKegiatan): string
    {
        return self::jenisKegiatanOptions()[self::normalizeJenisKegiatan($jenisKegiatan)];
    }

    /**
     * Bangun query AlokasiHonor yang memiliki SPK pada bulan & tahun
     * berdasarkan honor.tanggal_akhir_kegiatan dan filter jenis kegiatan.
     *
     * @return Builder<AlokasiHonor>
     */
    public static function buildQuery(int $tahun, int $bulan, ?string $jenisKegiatan = null): Builder
    {
        $normalizedJenis = self::normalizeJenisKegiatan($jenisKegiatan);

        return AlokasiHonor::query()
            ->with([
                'mitra',
                'honor.kegiatanManmit',
                'kontrak' => function ($query): void {
                    $query->where('jenis', Constants::JENIS_NOMOR_SURAT_PERJANJIAN_KERJA);
                },
            ])
            ->whereHas('kontrak', function (Builder $query): void {
                $query->where('jenis', Constants::JENIS_NOMOR_SURAT_PERJANJIAN_KERJA);
            })
            ->whereHas('honor', function (Builder $query) use ($tahun, $bulan, $normalizedJenis): void {
                $query->whereYear('tanggal_akhir_kegiatan', $tahun)
                    ->whereMonth('tanggal_akhir_kegiatan', $bulan);

                if ($normalizedJenis === self::JENIS_SENSUS) {
                    $query->whereHas('kegiatanManmit', function (Builder $kegiatanQuery): void {
                        $kegiatanQuery->where('jenis_kegiatan', self::JENIS_SENSUS);
                    });
                } elseif ($normalizedJenis === self::JENIS_SURVEI) {
                    $query->whereHas('kegiatanManmit', function (Builder $kegiatanQuery): void {
                        $kegiatanQuery->where(function (Builder $subQuery): void {
                            $subQuery->where('jenis_kegiatan', '!=', self::JENIS_SENSUS)
                                ->orWhereNull('jenis_kegiatan');
                        });
                    });
                }
            });
    }

    /**
     * Ambil daftar lampiran yang sudah dikelompokkan per SPK (kontrak + mitra)
     * dan diurutkan berdasarkan nomor SPK lalu nama mitra.
     */
    public static function getGroupedLampiran(int $tahun, int $bulan, ?string $jenisKegiatan = null): Collection
    {
        $alokasiList = self::buildQuery($tahun, $bulan, $jenisKegiatan)->get()->toBase();

        $grouped = $alokasiList
            ->filter(fn (AlokasiHonor $alokasi): bool => $alokasi->kontrak instanceof NomorSurat)
            ->groupBy(fn (AlokasiHonor $alokasi): string => $alokasi->surat_perjanjian_kerja_id.'|'.$alokasi->mitra_id)
            ->map(function (Collection $items): array {
                /** @var Collection<int, AlokasiHonor> $sortedItems */
                $sortedItems = $items->sortBy(function (AlokasiHonor $alokasi): array {
                    $honor = $alokasi->honor;
                    $tanggalAkhir = $honor instanceof Honor && $honor->tanggal_akhir_kegiatan
                        ? $honor->tanggal_akhir_kegiatan->format('Y-m-d')
                        : '';
                    $kegiatan = $honor instanceof Honor ? $honor->kegiatanManmit : null;
                    $namaKegiatan = $kegiatan instanceof KegiatanManmit ? $kegiatan->nama : '';

                    return [
                        $tanggalAkhir,
                        strtolower($namaKegiatan),
                    ];
                })->values();

                /** @var AlokasiHonor $first */
                $first = $sortedItems->first();
                /** @var NomorSurat $kontrak */
                $kontrak = $first->kontrak;
                $mitra = $first->mitra instanceof Mitra ? $first->mitra : null;

                $isSensus = $sortedItems->contains(function (AlokasiHonor $alokasi): bool {
                    $honor = $alokasi->honor;
                    $kegiatan = $honor instanceof Honor ? $honor->kegiatanManmit : null;

                    return $kegiatan instanceof KegiatanManmit
                        && $kegiatan->jenis_kegiatan === self::JENIS_SENSUS;
                });

                return [
                    'kontrak' => $kontrak,
                    'mitra' => $mitra,
                    'jenis_kegiatan' => $isSensus ? self::JENIS_SENSUS : self::JENIS_SURVEI,
                    'alokasi' => $sortedItems,
                    'total_honor' => (float) $sortedItems->sum('total_honor'),
                ];
            })
            ->sortBy(function (array $group): array {
                $mitra = $group['mitra'];
                $namaMitra = $mitra instanceof Mitra ? (string) $mitra->nama_1 : '';

                return [
                    (int) ($group['kontrak']->nomor ?? 0),
                    (int) ($group['kontrak']->sub_nomor ?? 0),
                    strtolower($namaMitra),
                ];
            })
            ->values();

        return $grouped;
    }

    /**
     * Ringkasan jumlah lampiran, mitra, alokasi, dan total honor untuk preview UI.
     *
     * @return array{total_lampiran: int, total_alokasi: int, total_mitra: int, total_honor: float}
     */
    public static function getSummary(int $tahun, int $bulan, ?string $jenisKegiatan = null): array
    {
        $groups = self::getGroupedLampiran($tahun, $bulan, $jenisKegiatan);

        return [
            'total_lampiran' => $groups->count(),
            'total_alokasi' => $groups->sum(fn (array $group): int => $group['alokasi']->count()),
            'total_mitra' => $groups->pluck('mitra.id')->filter()->unique()->count(),
            'total_honor' => (float) $groups->sum('total_honor'),
        ];
    }
}
