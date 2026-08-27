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

        $upperInput = strtoupper(trim(str_ireplace(['kecamatan', 'kabupaten', 'kota'], '', $kecamatanName)));
        
        // Handle historical/common aliases in Kab. Mempawah
        if (str_contains($upperInput, 'SIANTAN') || str_contains($upperInput, 'JUNGKAT')) {
            $upperInput = 'JONGKAT';
        }

        $presets = self::where('is_active', true)->get();
        foreach ($presets as $preset) {
            $presetName = strtoupper($preset->nama_kecamatan);
            if (str_contains($upperInput, $presetName) || str_contains($presetName, $upperInput)) {
                return $preset;
            }
        }

        // Fallback search
        $matched = self::where('is_active', true)
            ->where(function ($q) use ($upperInput) {
                $q->where('nama_kecamatan', 'LIKE', "%{$upperInput}%")
                  ->orWhereRaw("UPPER(nama_kecamatan) = ?", [$upperInput]);
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

        $preset = self::getPresetForKecamatan($kecamatanName);

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

    /**
     * Get list of all available Mempawah kecamatan options for UI dropdowns.
     *
     * @return array<string, string> Key: Full name e.g. "Kecamatan Segedong", Value: Label with duration
     */
    public static function getAllKecamatanOptions(): array
    {
        $presets = self::where('is_active', true)->orderBy('id')->get();
        if ($presets->isEmpty()) {
            return [
                'Kecamatan Mempawah Hilir' => 'Kecamatan Mempawah Hilir (~15 mnt - Dalam Kota)',
                'Kecamatan Mempawah Timur' => 'Kecamatan Mempawah Timur (~30 mnt - Dekat)',
                'Kecamatan Sungai Pinyuh' => 'Kecamatan Sungai Pinyuh (~45 mnt - Sedang)',
                'Kecamatan Anjongan' => 'Kecamatan Anjongan (~50 mnt - Sedang)',
                'Kecamatan Segedong' => 'Kecamatan Segedong (~50 mnt - Sedang)',
                'Kecamatan Jongkat' => 'Kecamatan Jongkat (~60 mnt - Jauh)',
                'Kecamatan Sungai Kunyit' => 'Kecamatan Sungai Kunyit (~50 mnt - Sedang)',
                'Kecamatan Toho' => 'Kecamatan Toho (~80 mnt - Jauh)',
                'Kecamatan Sadaniang' => 'Kecamatan Sadaniang (~105 mnt - Terjauh)',
            ];
        }

        $options = [];
        foreach ($presets as $p) {
            $formattedName = 'Kecamatan ' . \Illuminate\Support\Str::title(strtolower($p->nama_kecamatan));
            $options[$formattedName] = "{$formattedName} (~{$p->estimasi_menit} mnt - {$p->jarak_kategori})";
        }

        return $options;
    }

    /**
     * Get list of villages/desa for a given kecamatan.
     *
     * @param string|null $kecamatanName
     * @return array<string> List of village names (e.g. "Desa Parit Bugis", "Desa Peniti Besar")
     */
    public static function getDesaListForKecamatan(?string $kecamatanName): array
    {
        if (!$kecamatanName) {
            return [];
        }

        $clean = strtoupper(trim(str_ireplace(['kecamatan', 'kabupaten', 'kota'], '', $kecamatanName)));
        if (str_contains($clean, 'SIANTAN') || str_contains($clean, 'JUNGKAT')) {
            $clean = 'JONGKAT';
        }

        // Fallback curated mapping for all 9 subdistricts in Mempawah
        $fallbackDesas = [
            'SEGEDONG' => ['Desa Parit Bugis', 'Desa Peniti Besar', 'Desa Peniti Dalam I', 'Desa Peniti Dalam II', 'Desa Sungai Burung', 'Desa Sungai Purun Besar'],
            'MEMPAWAH HILIR' => ['Kelurahan Tengah', 'Kelurahan Terusan', 'Kelurahan Tanjung', 'Desa Kuala Secapah', 'Desa Pasir', 'Desa Penibung', 'Desa Sengkubang', 'Desa Malikian'],
            'MEMPAWAH TIMUR' => ['Kelurahan Pulau Pedalaman', 'Desa Pasir Wan Salim', 'Desa Sungai Bakau Kecil', 'Desa Pasir Panjang', 'Desa Pasir Palembang', 'Desa Antibar', 'Desa Sejegi', 'Desa Parit Banjar'],
            'SUNGAI PINYUH' => ['Kelurahan Sungai Pinyuh', 'Desa Sungai Purun Kecil', 'Desa Peniraman', 'Desa Nusapati', 'Desa Galang', 'Desa Sungai Rasau', 'Desa Sungai Batang', 'Desa Sungai Bakau Besar Laut', 'Desa Sungai Bakau Besar Darat'],
            'ANJONGAN' => ['Kelurahan Anjungan Melancar', 'Desa Anjungan Dalam', 'Desa Pak Bulu', 'Desa Dema', 'Desa Kepayang'],
            'JONGKAT' => ['Desa Jungkat', 'Desa Sungai Nipah', 'Desa Wajok Hilir', 'Desa Wajok Hulu', 'Desa Peniti Luar'],
            'SUNGAI KUNYIT' => ['Desa Semudun', 'Desa Semparong Parit Raden', 'Desa Mendalok', 'Desa Sungai Dungun', 'Desa Sungai Limau', 'Desa Sungai Kunyit Laut', 'Desa Sungai Kunyit Dalam', 'Desa Sungai Kunyit Hulu', 'Desa Bukit Batu', 'Desa Sungai Bundung Laut', 'Desa Sungai Duri I', 'Desa Sungai Duri II'],
            'TOHO' => ['Desa Sambora', 'Desa Benuang', 'Desa Pak Utan', 'Desa Sepang', 'Desa Pak Laheng', 'Desa Terap', 'Desa Kecurit', 'Desa Toho Ilir'],
            'SADANIANG' => ['Desa Pentek', 'Desa Sekabuk', 'Desa Bumbun', 'Desa Amawang', 'Desa Ansiap', 'Desa Suak Barangan'],
        ];

        // Try DB lookup via MasterSls first
        try {
            if (class_exists(MasterSls::class)) {
                $dbDesas = MasterSls::where('kecamatan', 'LIKE', "%{$clean}%")
                    ->orWhereRaw("UPPER(kecamatan) = ?", [$clean])
                    ->select('desa_kel')
                    ->distinct()
                    ->pluck('desa_kel')
                    ->map(function ($d) {
                        $title = \Illuminate\Support\Str::title(strtolower($d));
                        if (in_array(strtoupper($d), ['TENGAH', 'TERUSAN', 'TANJUNG', 'PULAU PEDALAMAN', 'SUNGAI PINYUH', 'ANJUNGAN MELANCAR'])) {
                            return 'Kelurahan ' . $title;
                        }
                        return 'Desa ' . $title;
                    })
                    ->values()
                    ->toArray();

                if (!empty($dbDesas)) {
                    return $dbDesas;
                }
            }
        } catch (\Throwable $e) {
            // Silently fallback
        }

        // Return fallback if found
        foreach ($fallbackDesas as $k => $desasList) {
            if (str_contains($clean, $k) || str_contains($k, $clean)) {
                return $desasList;
            }
        }

        return [];
    }

    /**
     * Resolve a formatted location/address string for a specific spot and kecamatan.
     */
    public static function getResolvedAddressForSpot(?string $kecamatanName, ?string $spotName = null): string
    {
        $preset = self::getPresetForKecamatan($kecamatanName);
        $kecTitle = $preset ? 'Kec. ' . \Illuminate\Support\Str::title(strtolower($preset->nama_kecamatan)) : 'Kec. Mempawah Hilir';
        
        if ($spotName && !empty(trim($spotName))) {
            $cleanSpot = trim(str_ireplace(['(Visum SPPD)', '(Visum SPPD & Koordinasi)'], '', $spotName));
            if (!str_starts_with($cleanSpot, 'Lokasi Lapangan') && !str_starts_with($cleanSpot, 'Titik Utama')) {
                return "{$cleanSpot}, {$kecTitle}, Kab. Mempawah";
            }
        }

        if ($preset && !empty($preset->kantor_camat_alamat)) {
            $alamat = $preset->kantor_camat_alamat;
            if (!str_contains($alamat, 'Kab. Mempawah') && !str_contains($alamat, 'Mempawah')) {
                $alamat .= ', Kab. Mempawah';
            }
            return $alamat;
        }

        return "{$kecTitle}, Kab. Mempawah";
    }

    /**
     * Resolve a formatted GPS coordinate string for a specific spot and kecamatan.
     */
    public static function getResolvedCoordinateForSpot(?string $kecamatanName, ?string $coordInput = null, ?string $spotName = null): string
    {
        if (!empty($coordInput) && str_contains($coordInput, ',')) {
            $coord = trim($coordInput);
        } else {
            $preset = self::getPresetForKecamatan($kecamatanName);
            $coord = $preset !== null && !empty($preset->kantor_camat_koordinat) ? $preset->kantor_camat_koordinat : '0.354167, 108.961111';
        }

        if ($spotName && !empty(trim($spotName))) {
            $cleanSpot = trim(str_ireplace(['(Visum SPPD)', '(Visum SPPD & Koordinasi)'], '', $spotName));
            if (!str_starts_with($cleanSpot, 'Lokasi Lapangan') && !str_starts_with($cleanSpot, 'Titik Utama')) {
                return "{$coord} ({$cleanSpot})";
            }
        }

        return $coord;
    }

    /**
     * Format a clean, formal, publication-ready photo caption for BPS official report attachments.
     */
    public static function formatCleanPhotoCaption(?string $spotName, ?string $uraian, ?string $kecamatanName, string $tglLabel): string
    {
        $preset = self::getPresetForKecamatan($kecamatanName);
        $cleanKec = $preset ? 'Kec. ' . \Illuminate\Support\Str::title(strtolower($preset->nama_kecamatan)) : 'Kec. Mempawah Hilir';
        $kecBaseName = $preset ? \Illuminate\Support\Str::title(strtolower($preset->nama_kecamatan)) : 'Mempawah Hilir';
        
        $spotRaw = trim($spotName ?? '');
        $isGenericSpot = empty($spotRaw) || str_starts_with($spotRaw, 'Lokasi Lapangan') || str_starts_with($spotRaw, 'Titik Utama');

        // Priority 1: User explicitly typed a specific village / spot name
        if (!$isGenericSpot) {
            $cleanSpot = trim(str_ireplace(['(Visum SPPD)', '(Visum SPPD & Koordinasi)'], '', $spotRaw));
            if (!str_contains(strtolower($cleanSpot), strtolower($kecBaseName))) {
                return "{$cleanSpot}, {$cleanKec} ({$tglLabel})";
            }
            return "{$cleanSpot} ({$tglLabel})";
        }

        // Priority 2: User provided a note/uraian -> Smart sanitize into formal Title Case phrase
        if (!empty(trim($uraian ?? ''))) {
            $text = strip_tags($uraian);
            
            // Fix common typos / informal words
            $text = (string) preg_replace('/\bpengaawasan\b/i', 'pengawasan', $text);
            $text = (string) preg_replace('/\bkoordinasii\b/i', 'koordinasi', $text);
            $text = (string) preg_replace('/^(melakukan|mengikuti|melaksanakan)\s+/i', '', $text);
            
            // Split into words
            $words = preg_split('/\s+/', trim($text));
            if ($words !== false) {
                if (count($words) > 6) {
                    $words = array_slice($words, 0, 6);
                }
                
                // Remove trailing conjunctions / prepositions at cut boundary
                while (!empty($words)) {
                    $lastWord = strtolower(end($words));
                    if (in_array($lastWord, ['dan', 'da', 'yang', 'di', 'ke', 'untuk', 'serta', 'terkait', 'dalam', 'dengan', 'pada', 'tentang', 'fasih'])) {
                        array_pop($words);
                    } else {
                        break;
                    }
                }

                if (!empty($words)) {
                    $phrase = implode(' ', $words);
                    $titleCased = \Illuminate\Support\Str::title($phrase);
                    
                    // Preserve official BPS acronyms
                    $titleCased = (string) preg_replace('/\bPpl\b/', 'PPL', $titleCased);
                    $titleCased = (string) preg_replace('/\bPml\b/', 'PML', $titleCased);
                    $titleCased = (string) preg_replace('/\bSppd\b/', 'SPPD', $titleCased);
                    $titleCased = (string) preg_replace('/\bBps\b/', 'BPS', $titleCased);
                    $titleCased = (string) preg_replace('/\bKsa\b/', 'KSA', $titleCased);
                    $titleCased = (string) preg_replace('/\bFasih\b/i', 'FASIH', $titleCased);
                    
                    if (!str_contains(strtolower($titleCased), strtolower($kecBaseName))) {
                        return "{$titleCased} - {$cleanKec} ({$tglLabel})";
                    }
                    return "{$titleCased} ({$tglLabel})";
                }
            }
        }

        // Priority 3: Fallback standard official caption
        return "Dokumentasi Kegiatan di {$cleanKec} ({$tglLabel})";
    }
}
