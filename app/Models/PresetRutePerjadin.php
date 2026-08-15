<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class PresetRutePerjadin extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'steps' => 'array',
            'is_active' => 'boolean',
            'estimasi_menit' => 'integer',
        ];
    }

    /**
     * Find matching preset record for a kecamatan.
     */
    public static function getPresetForKecamatan(?string $kecamatanName): ?self
    {
        if (!$kecamatanName) {
            return self::where('nama_kecamatan', 'MEMPAWAH HILIR')->first();
        }

        $upperInput = strtoupper(trim($kecamatanName));
        
        $presets = self::where('is_active', true)->get();
        foreach ($presets as $preset) {
            if (str_contains($upperInput, strtoupper($preset->nama_kecamatan))) {
                return $preset;
            }
        }

        // Fallback search
        $cleanName = strtoupper(trim(str_ireplace(['kecamatan', 'kabupaten', 'kota'], '', $kecamatanName)));
        $matched = self::where('is_active', true)
            ->where(function ($q) use ($cleanName) {
                $q->where('nama_kecamatan', 'LIKE', "%{$cleanName}%")
                  ->orWhereRaw("UPPER(nama_kecamatan) = ?", [$cleanName]);
            })
            ->first();

        return $matched ?: self::where('nama_kecamatan', 'MEMPAWAH HILIR')->first();
    }

    /**
     * Get default itinerary steps for a specific kecamatan name with prayer & break time adjustments.
     *
     * @param string|null $kecamatanName
     * @param string|null $dateString YYYY-MM-DD
     * @return array
     */
    public static function getStepsForKecamatan(?string $kecamatanName, ?string $dateString = null): array
    {
        $isFriday = false;
        if ($dateString) {
            try {
                $isFriday = Carbon::parse($dateString)->isFriday();
            } catch (\Exception $e) {
                $isFriday = false;
            }
        }

        $cleanName = $kecamatanName ? strtoupper(trim(str_ireplace('kecamatan', '', $kecamatanName))) : '';
        $preset = null;

        if ($cleanName) {
            $preset = self::where('is_active', true)
                ->where(function ($q) use ($cleanName) {
                    $q->where('nama_kecamatan', 'LIKE', "%{$cleanName}%")
                      ->orWhereRaw("UPPER(nama_kecamatan) = ?", [$cleanName]);
                })
                ->first();
        }

        // Base travel start times
        $startCoord = '08.00 - 08.30';
        $travelMinutes = 30;

        if ($preset) {
            $travelMinutes = $preset->estimasi_menit ?? 30;
            if (!empty($preset->steps[0]['waktu'])) {
                $startCoord = $preset->steps[0]['waktu'];
            }
        }

        $pagiEnd = explode('-', $startCoord)[1] ?? '08.30';
        $pagiEnd = trim($pagiEnd);

        if ($isFriday) {
            // JADWAL HARI JUMAT:
            // 1. Berangkat & Cap SPPD
            // 2. Lapangan Sesi I (sampai 11.30)
            // 3. Istirahat & Sholat Jumat (11.30 - 13.00 -> 1.5 Jam)
            // 4. Lapangan Sesi II (13.00 - 15.00)
            // 5. Istirahat & Sholat Ashar (15.00 - 15.15 -> 15 Menit)
            // 6. Pulang (15.15 - ...)
            $pulangEnd = Carbon::createFromFormat('H.i', '15.15')->addMinutes($travelMinutes)->format('H.i');

            return [
                ['step' => 1, 'waktu' => $startCoord, 'kategori' => 'berangkat_koordinasi', 'uraian' => 'Perjalanan dari Mempawah menuju lokasi penugasan dan berkoordinasi dengan pihak kecamatan serta penandatanganan/cap visum SPPD.'],
                ['step' => 2, 'waktu' => "{$pagiEnd} - 11.30", 'kategori' => 'lapangan_1', 'uraian' => 'Perjalanan menuju lokasi sampel/wilayah sasaran dan melaksanakan kegiatan pengawasan/pendataan lapangan.'],
                ['step' => 3, 'waktu' => '11.30 - 13.00', 'kategori' => 'ishoma_jumat', 'uraian' => 'Istirahat, Ibadah Sholat Jumat, dan Makan Siang'],
                ['step' => 4, 'waktu' => '13.00 - 15.00', 'kategori' => 'lapangan_2', 'uraian' => 'Melanjutkan pelaksanaan kegiatan lapangan, verifikasi data sampel, dan evaluasi hasil kegiatan.'],
                ['step' => 5, 'waktu' => '15.00 - 15.15', 'kategori' => 'sholat_ashar', 'uraian' => 'Istirahat dan Sholat Ashar'],
                ['step' => 6, 'waktu' => "15.15 - {$pulangEnd}", 'kategori' => 'kembali', 'uraian' => 'Perjalanan kembali ke Mempawah.'],
            ];
        }

        // JADWAL HARI BIASA (SENIN - KAMIS & AKHIR PEKAN):
        // 1. Berangkat & Cap SPPD
        // 2. Lapangan Sesi I (sampai 12.00)
        // 3. Istirahat & Sholat Zhuhur (12.00 - 13.00 -> 1 Jam)
        // 4. Lapangan Sesi II (13.00 - 15.00)
        // 5. Istirahat & Sholat Ashar (15.00 - 15.15 -> 15 Menit)
        // 6. Pulang (15.15 - ...)
        $pulangEnd = Carbon::createFromFormat('H.i', '15.15')->addMinutes($travelMinutes)->format('H.i');

        return [
            ['step' => 1, 'waktu' => $startCoord, 'kategori' => 'berangkat_koordinasi', 'uraian' => 'Perjalanan dari Mempawah menuju lokasi penugasan dan berkoordinasi dengan pihak kecamatan serta penandatanganan/cap visum SPPD.'],
            ['step' => 2, 'waktu' => "{$pagiEnd} - 12.00", 'kategori' => 'lapangan_1', 'uraian' => 'Perjalanan menuju lokasi sampel/wilayah sasaran dan melaksanakan kegiatan pengawasan/pendataan lapangan.'],
            ['step' => 3, 'waktu' => '12.00 - 13.00', 'kategori' => 'ishoma_zhuhur', 'uraian' => 'Istirahat, Sholat Zhuhur, dan Makan Siang'],
            ['step' => 4, 'waktu' => '13.00 - 15.00', 'kategori' => 'lapangan_2', 'uraian' => 'Melanjutkan pelaksanaan kegiatan lapangan, verifikasi data sampel, dan evaluasi hasil kegiatan.'],
            ['step' => 5, 'waktu' => '15.00 - 15.15', 'kategori' => 'sholat_ashar', 'uraian' => 'Istirahat dan Sholat Ashar'],
            ['step' => 6, 'waktu' => "15.15 - {$pulangEnd}", 'kategori' => 'kembali', 'uraian' => 'Perjalanan kembali ke Mempawah.'],
        ];
    }
}
