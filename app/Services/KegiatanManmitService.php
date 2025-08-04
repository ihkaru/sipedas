<?php

namespace App\Services;

use App\Models\KegiatanManmit;
use Carbon\Carbon; // <-- TAMBAHKAN USE STATEMENT INI
use Exception;
use Illuminate\Support\Facades\DB;

class KegiatanManmitService
{
    public static function importFromJsonString(string $jsonString): int
    {
        $jsonData = json_decode($jsonString, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Format JSON tidak valid. Periksa kembali teks yang Anda salin.');
        }
        if (!isset($jsonData['data']) || !is_array($jsonData['data'])) {
            throw new Exception('Struktur JSON tidak valid. Kunci "data" tidak ditemukan atau bukan array.');
        }

        $processedCount = 0;

        DB::transaction(function () use ($jsonData, &$processedCount) {
            foreach ($jsonData['data'] as $item) {
                if (empty($item['kd_survei']) || empty($item['nama_survei'])) {
                    continue;
                }

                KegiatanManmit::updateOrCreate(
                    [
                        'id' => $item['kd_survei']
                    ],
                    [
                        'nama' => sprintf('(%s) %s', $item['kd_survei'], $item['nama_survei']),

                        // --- PERUBAHAN UTAMA DI SINI ---
                        // Menggunakan Carbon::parse untuk mengonversi format tanggal
                        'tgl_mulai_pelaksanaan'   => isset($item['tgl_mulai']) ? Carbon::parse($item['tgl_mulai']) : null,
                        'tgl_akhir_pelaksanaan'   => isset($item['tgl_selesai']) ? Carbon::parse($item['tgl_selesai']) : null,
                        'tgl_mulai_penawaran'     => isset($item['tgl_rek_mulai']) ? Carbon::parse($item['tgl_rek_mulai']) : null,
                        'tgl_akhir_penawaran'     => isset($item['tgl_rek_selesai']) ? Carbon::parse($item['tgl_rek_selesai']) : null,
                    ]
                );

                $processedCount++;
            }
        });

        return $processedCount;
    }
}
