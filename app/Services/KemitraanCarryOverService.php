<?php

namespace App\Services;

use App\Models\Kemitraan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class KemitraanCarryOverService
{
    /**
     * Menyalin status kemitraan mitra terpilih ke tahun tujuan (misal 2026 -> 2027)
     * dengan status operasional AKTIF namun bertanda pra-rekrutmen.
     *
     * @param  Collection<int, Model>  $mitras
     * @return array{created: int, skipped_existing: int, skipped_blacklisted: int}
     */
    public function carryOver(
        Collection $mitras,
        int $sourceYear,
        int $targetYear,
        string $statusRekrutmen = Kemitraan::REKRUTMEN_BELUM,
        ?string $catatan = null
    ): array {
        $mitraIds = $mitras->pluck('id')->filter()->unique()->values();

        if ($mitraIds->isEmpty()) {
            return [
                'created' => 0,
                'skipped_existing' => 0,
                'skipped_blacklisted' => 0,
            ];
        }

        $defaultCatatan = $catatan !== null && trim($catatan) !== ''
            ? trim($catatan)
            : "Carry-over dari {$sourceYear} untuk SPK Pelatihan — belum lulus rekrutmen mitra {$targetYear}.";

        return DB::transaction(function () use ($mitraIds, $sourceYear, $targetYear, $statusRekrutmen, $defaultCatatan) {
            $existingTargetIds = Kemitraan::query()
                ->whereIn('mitra_id', $mitraIds)
                ->where('tahun', $targetYear)
                ->pluck('mitra_id')
                ->flip();

            $blacklistedSourceIds = Kemitraan::query()
                ->whereIn('mitra_id', $mitraIds)
                ->where('tahun', $sourceYear)
                ->where('status', Kemitraan::STATUS_BLACKLISTED)
                ->pluck('mitra_id')
                ->flip();

            $created = 0;
            $skippedExisting = 0;
            $skippedBlacklisted = 0;

            foreach ($mitraIds as $mitraId) {
                if ($blacklistedSourceIds->has($mitraId)) {
                    $skippedBlacklisted++;
                    continue;
                }

                if ($existingTargetIds->has($mitraId)) {
                    $skippedExisting++;
                    continue;
                }

                Kemitraan::create([
                    'mitra_id' => $mitraId,
                    'tahun' => $targetYear,
                    'status' => Kemitraan::STATUS_AKTIF,
                    'status_rekrutmen' => $statusRekrutmen,
                    'catatan' => $defaultCatatan,
                ]);

                $created++;
            }

            return [
                'created' => $created,
                'skipped_existing' => $skippedExisting,
                'skipped_blacklisted' => $skippedBlacklisted,
            ];
        });
    }

    /**
     * Menonaktifkan sisa mitra pra-rekrutmen pada tahun tertentu setelah hasil
     * rekrutmen resmi (KEPKA) diimpor, tanpa menghapus histori SPK yang sudah ada.
     */
    public function reconcileNonVerified(int $tahun, ?string $alasan = null): int
    {
        $suffix = $alasan !== null && trim($alasan) !== ''
            ? trim($alasan)
            : "Dinonaktifkan pasca-rekrutmen {$tahun} (tidak tercatat lulus pada KEPKA {$tahun}).";

        return DB::transaction(function () use ($tahun, $suffix) {
            $records = Kemitraan::query()
                ->where('tahun', $tahun)
                ->where('status_rekrutmen', Kemitraan::REKRUTMEN_BELUM)
                ->get();

            foreach ($records as $record) {
                $existingNote = trim((string) $record->catatan);
                $newNote = $existingNote !== '' ? "{$existingNote} | {$suffix}" : $suffix;

                $record->update([
                    'status' => Kemitraan::STATUS_TIDAK_AKTIF,
                    'status_rekrutmen' => Kemitraan::REKRUTMEN_TIDAK_LULUS,
                    'catatan' => $newNote,
                ]);
            }

            return $records->count();
        });
    }
}
