<?php

namespace App\Services;

use Carbon\Carbon;

class HonorService
{
    /**
     * Menghitung proporsi honor untuk bulan dan tahun tertentu berdasarkan hari kalender.
     * Hasil dibulatkan ke bawah (floor).
     *
     * @param float|int $totalHonor
     * @param string|Carbon $startDate
     * @param string|Carbon $endDate
     * @param int $targetMonth
     * @param int $targetYear
     * @return int
     */
    public static function calculateMonthlyProportion($totalHonor, $startDate, $endDate, $targetMonth, $targetYear): int
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        
        // 1. Hitung total hari kalender dalam kontrak
        $totalDays = $start->diffInDays($end) + 1;
        
        if ($totalDays <= 0) return 0;

        // 2. Tentukan rentang bulan target
        $targetStart = Carbon::create($targetYear, $targetMonth, 1)->startOfMonth()->startOfDay();
        $targetEnd = Carbon::create($targetYear, $targetMonth, 1)->endOfMonth()->endOfDay();

        // 3. Cari irisan hari (kapan kontrak aktif di bulan target)
        $overlapStart = $start->greaterThan($targetStart) ? $start : $targetStart;
        $overlapEnd = $end->lessThan($targetEnd) ? $end : $targetEnd;

        if ($overlapStart->greaterThan($overlapEnd)) {
            return 0; // Tidak ada irisan di bulan ini
        }

        $activeDaysInMonth = $overlapStart->diffInDays($overlapEnd) + 1;

        // 4. Hitung proporsi dan bulatkan ke bawah
        return (int) floor(($totalHonor * $activeDaysInMonth) / $totalDays);
    }

    /**
     * Mengambil sisa kuota SBML terkecil dari seluruh bulan yang bersentuhan dengan rentang tanggal.
     */
    public static function getMitraRemainingBudget($mitraId, $startDate, $endDate): array
    {
        $start = \Illuminate\Support\Carbon::parse($startDate);
        $end = \Illuminate\Support\Carbon::parse($endDate);

        $limitSensus = \App\Models\Setting::where('key', 'STANDAR_BIAYA_MASUKAN_LAINNYA_NON_PNS_OB_PETUGAS_PENDATAAN_LAPANGAN_SENSUS')->value('value') ?? 4694000;
        $limitSurvei = \App\Models\Setting::where('key', 'STANDAR_BIAYA_MASUKAN_LAINNYA_NON_PNS_OB_PETUGAS_PENDATAAN_LAPANGAN_SURVEI')->value('value') ?? 3353000;

        $minRemainingSensus = $limitSensus;
        $minRemainingSurvei = $limitSurvei;

        $current = $start->clone()->startOfMonth();
        while ($current <= $end) {
            $month = $current->month;
            $year = $current->year;

            $existingContracts = \App\Models\AlokasiHonor::query()
                ->where('mitra_id', $mitraId)
                ->where(function($q) use ($month, $year) {
                    $targetStart = \Illuminate\Support\Carbon::create($year, $month, 1)->startOfMonth();
                    $targetEnd = \Illuminate\Support\Carbon::create($year, $month, 1)->endOfMonth();
                    $q->where('tanggal_mulai_perjanjian', '<=', $targetEnd)
                      ->where('tanggal_akhir_perjanjian', '>=', $targetStart);
                })
                ->with(['honor.kegiatanManmit'])
                ->get();

            $totalSensus = 0;
            $totalSurvei = 0;

            foreach ($existingContracts as $contract) {
                $prop = self::calculateMonthlyProportion($contract->total_honor, $contract->tanggal_mulai_perjanjian, $contract->tanggal_akhir_perjanjian, $month, $year);
                if ($contract->honor?->kegiatanManmit?->jenis_kegiatan === 'SENSUS') {
                    $totalSensus += $prop;
                } else {
                    $totalSurvei += $prop;
                }
            }

            $remSensus = $limitSensus - $totalSensus;
            $remSurvei = $limitSurvei - $totalSurvei;

            if ($remSensus < $minRemainingSensus) $minRemainingSensus = $remSensus;
            if ($remSurvei < $minRemainingSurvei) $minRemainingSurvei = $remSurvei;

            $current->addMonth();
        }

        return [
            'sensus' => max(0, $minRemainingSensus),
            'survei' => max(0, $minRemainingSurvei),
        ];
    }

    /**
     * Cek apakah mitra boleh mengambil kontrak baru berdasarkan bentrok jadwal dan limit SBML.
     */
    public static function validateMitraEligibility(
        $mitraId, 
        $newStart, 
        $newEnd, 
        $newTotalHonor, 
        $isSensus, 
        $excludeAlokasiId = null
    ): array {
        $newStart = \Illuminate\Support\Carbon::parse($newStart);
        $newEnd = \Illuminate\Support\Carbon::parse($newEnd);

        // 1. Cek Bentrok Jadwal (Strict Overlap)
        $overlap = \App\Models\AlokasiHonor::query()
            ->where('mitra_id', $mitraId)
            ->when($excludeAlokasiId, fn($q) => $q->where('id', '!=', $excludeAlokasiId))
            ->where(function ($q) use ($newStart, $newEnd) {
                $q->where('tanggal_mulai_perjanjian', '<=', $newEnd)
                  ->where('tanggal_akhir_perjanjian', '>=', $newStart);
            })
            ->with('honor.kegiatanManmit')
            ->first();

        if ($overlap) {
            $otherIsSensus = $overlap->honor?->kegiatanManmit?->jenis_kegiatan === 'SENSUS';
            
            // Aturan: Bentrok hanya berlaku jika salah satu atau kedua kegiatan adalah SENSUS.
            // Jika keduanya adalah SURVEI, diperbolehkan overlap (lanjut ke cek limit SBML).
            if ($isSensus || $otherIsSensus) {
                $kegiatan = $overlap->honor?->kegiatanManmit?->nama ?? 'Kegiatan lain';
                return [
                    'eligible' => false,
                    'message' => "Jadwal bentrok dengan kegiatan '{$kegiatan}' ({$overlap->tanggal_mulai_perjanjian->format('d M')} - {$overlap->tanggal_akhir_perjanjian->format('d M')})."
                ];
            }
        }

        // 2. Cek Limit SBML Proportional untuk SETIAP bulan yang terkena dampak
        $limitSensus = \App\Models\Setting::where('key', 'STANDAR_BIAYA_MASUKAN_LAINNYA_NON_PNS_OB_PETUGAS_PENDATAAN_LAPANGAN_SENSUS')->value('value') ?? 4694000;
        $limitSurvei = \App\Models\Setting::where('key', 'STANDAR_BIAYA_MASUKAN_LAINNYA_NON_PNS_OB_PETUGAS_PENDATAAN_LAPANGAN_SURVEI')->value('value') ?? 3353000;

        // Ambil semua bulan yang bersentuhan dengan kontrak baru
        $current = $newStart->clone()->startOfMonth();
        while ($current <= $newEnd) {
            $month = $current->month;
            $year = $current->year;
            $monthName = $current->format('F Y');

            // Hitung honor dari kontrak baru di bulan ini
            $newProportion = self::calculateMonthlyProportion($newTotalHonor, $newStart, $newEnd, $month, $year);

            // Hitung akumulasi dari kontrak lama di bulan ini
            $existingContracts = \App\Models\AlokasiHonor::query()
                ->where('mitra_id', $mitraId)
                ->when($excludeAlokasiId, fn($q) => $q->where('id', '!=', $excludeAlokasiId))
                ->where(function($q) use ($month, $year) {
                    $targetStart = \Illuminate\Support\Carbon::create($year, $month, 1)->startOfMonth();
                    $targetEnd = \Illuminate\Support\Carbon::create($year, $month, 1)->endOfMonth();
                    $q->where('tanggal_mulai_perjanjian', '<=', $targetEnd)
                      ->where('tanggal_akhir_perjanjian', '>=', $targetStart);
                })
                ->with(['honor.kegiatanManmit'])
                ->get();

            $totalSensus = $isSensus ? $newProportion : 0;
            $totalSurvei = !$isSensus ? $newProportion : 0;

            foreach ($existingContracts as $contract) {
                $prop = self::calculateMonthlyProportion($contract->total_honor, $contract->tanggal_mulai_perjanjian, $contract->tanggal_akhir_perjanjian, $month, $year);
                if ($contract->honor?->kegiatanManmit?->jenis_kegiatan === 'SENSUS') {
                    $totalSensus += $prop;
                } else {
                    $totalSurvei += $prop;
                }
            }

            if ($totalSensus > $limitSensus) {
                return ['eligible' => false, 'message' => "Akumulasi honor SENSUS di bulan {$monthName} melebihi limit (Rp " . number_format($totalSensus) . " > " . number_format($limitSensus) . ")."];
            }
            if ($totalSurvei > $limitSurvei) {
                return ['eligible' => false, 'message' => "Akumulasi honor SURVEI di bulan {$monthName} melebihi limit (Rp " . number_format($totalSurvei) . " > " . number_format($limitSurvei) . ")."];
            }

            $current->addMonth();
        }

        return ['eligible' => true, 'message' => 'Mitra layak dialokasikan.'];
    }
}
