<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class AIService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $model;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.ai.base_url', 'https://ai.dvlpid.my.id/v1'), '/');
        $this->apiKey = config('services.ai.api_key', 'sk-af6376fcf20b4a148672456a6cae1902');
        $this->model = config('services.ai.model', 'gemini-3-flash');
    }

    /**
     * Generate structured travel report (5-column official BPS timeline format) from raw user inputs.
     *
     * @param array $rawInput Daily logs from user input with preset steps
     * @param array $metaContext Context including kegiatan, kecamatan, moda transportasi, surat tugas
     * @return array Polished report details structured as JSON matching official BPS format
     * @throws Exception
     */
    public function generateStructuredReport(array $rawInput, array $metaContext): array
    {
        $prompt = $this->buildPrompt($rawInput, $metaContext);

        try {
            Log::info("AIService: Sending request to LLM using model {$this->model}...");
            
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
            ])
            ->timeout(60)
            ->post("{$this->baseUrl}/chat/completions", [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Anda adalah asisten AI profesional untuk penulisan Laporan Perjalanan Dinas resmi Badan Pusat Statistik (BPS). Anda selalu membalas dengan struktur JSON yang valid sesuai petunjuk tanpa penjelasan tambahan di luar JSON.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ]
                ],
                'response_format' => ['type' => 'json_object']
            ]);

            if ($response->failed()) {
                throw new Exception("LLM request failed with status: " . $response->status() . " - " . $response->body());
            }

            $result = $response->json();
            $content = $result['choices'][0]['message']['content'] ?? '';
            
            Log::info("AIService: Received response from LLM.", ['content_length' => strlen($content)]);

            // Clean markdown code blocks if present
            $contentCleaned = trim($content);
            if (str_starts_with($contentCleaned, '```json')) {
                $contentCleaned = substr($contentCleaned, 7);
            }
            if (str_ends_with($contentCleaned, '```')) {
                $contentCleaned = substr($contentCleaned, 0, -3);
            }
            $contentCleaned = trim($contentCleaned);

            $parsedJson = json_decode($contentCleaned, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Failed to parse JSON from LLM: " . json_last_error_msg() . "\nRaw response: " . $content);
            }

            return $parsedJson;

        } catch (Exception $e) {
            Log::error("AIService Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Build the prompt text for the LLM request.
     */
    protected function buildPrompt(array $rawInput, array $metaContext): string
    {
        $kegiatan = $metaContext['kegiatan_nama'] ?? '';
        $suratTugas = $metaContext['nomor_surat_tugas'] ?? '';
        $transportasi = $metaContext['moda_transportasi'] ?? 'Kendaraan Pribadi';
        $daerah = $metaContext['daerah_dikunjungi'] ?? 'Kecamatan Mempawah Timur';
        $pelaksana = $metaContext['pelaksana_nama'] ?? '';
        $inputStr = json_encode($rawInput, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        
        return <<<PROMPT
Tugas Anda: Ubah draf catatan perjalanan dinas pegawai di bawah ini menjadi format resmi TABEL MATRIKS LAPORAN PERJALANAN DINAS BADAN PUSAT STATISTIK (BPS) persis seperti standar baku kedinasan.

Informasi Konteks Penugasan:
- Nama Kegiatan: "{$kegiatan}"
- Dasar Pelaksanaan: "{$suratTugas}"
- Moda Transportasi: "{$transportasi}"
- Daerah yang dikunjungi: "{$daerah}"
- Pelaksana: "{$pelaksana}"

Input Data Pegawai & Preset Rute Waktu:
{$inputStr}

Ketentuan Khusus Pembagian Slot Waktu & Auto-Timing (Kronologis Standar BPS):
Untuk setiap tanggal kegiatan, baca daftar `titik_kegiatan` secara berurutan dan susun tabel kronologis jam-per-jam yang saling berkesinambungan:

1. Slot Keberangkatan & Koordinasi (Kantor Camat / Desa):
   - Waktu: Sesuai preset keberangkatan kecamatan (contoh: 08.00 - 08.30).
   - Uraian: Perjalanan dari Mempawah menuju {$daerah} dan berkoordinasi dengan pihak kecamatan/desa terkait pelaksanaan {$kegiatan} serta penandatanganan/cap visum SPPD.
   - Permasalahan/Pemecahan: "-" (atau kendala administrasi jika ada di draf).
   - Keterangan: "-".

2. Distribusi Titik Lapangan (Sesi Pagi & Sesi Siang):
   - Perhatikan daftar `titik_kegiatan` yang diinput pegawai:
     * JIKA HARI JUMAT: Sesi pagi berlangsung pukul 08.30 - 11.30. Sesi siang pukul 13.00 - 15.00.
     * JIKA SELAIN JUMAT: Sesi pagi berlangsung pukul 08.30 - 12.00. Sesi siang pukul 13.00 - 15.00.
   - Jika ada lebih dari 1 titik lapangan, bagi slot waktunya secara logis dan proporsional (contoh jika ada 2 titik lapangan: Titik 1 ditempatkan di sesi pagi 08.30 - 12.00, Titik 2 ditempatkan di sesi siang 13.00 - 15.00. Jika ada 3 titik: bagi 2 di pagi dan 1 di siang, dst).
   - Uraian: Tuliskan narasi formal pelaksanaan kegiatan di lokasi tersebut (sebutkan nama tempat/desa/sampel).
   - Permasalahan/Pemecahan: Ambil kendala & solusi spesifik yang terjadi di titik tersebut. Jika lancar, isi "-".
   - Keterangan: Tuliskan capaian/output di titik tersebut (contoh: "1. Pendataan terlaksana sesuai SOP\n2. Berhasil mengumpulkan data pada 2 responden sampel").

3. Slot Istirahat & Sholat (Wajib Diselipkan):
   - JIKA HARI JUMAT: Waktu "11.30 - 13.00" (1.5 jam), Uraian: "Istirahat, Ibadah Sholat Jumat, dan Makan Siang".
   - JIKA SELAIN JUMAT: Waktu "12.00 - 13.00" (1 jam), Uraian: "Istirahat, Sholat Zhuhur, dan Makan Siang".
   - Permasalahan/Pemecahan: "-".
   - Keterangan: "-".

4. Slot Sholat Ashar (15 Menit):
   - Waktu: "15.00 - 15.15".
   - Uraian: "Istirahat dan Sholat Ashar".
   - Permasalahan/Pemecahan: "-".
   - Keterangan: "-".

5. Slot Perjalanan Pulang:
   - Waktu: Sesuai estimasi waktu kembali (contoh: 15.15 - 15.45).
   - Uraian: Perjalanan kembali ke Mempawah.
   - Permasalahan/Pemecahan: "-".
   - Keterangan: "-".

Anda HARUS merespons dalam format JSON persis seperti struktur berikut:
{
  "dasar_pelaksanaan": "{$suratTugas}",
  "moda_transportasi": "{$transportasi}",
  "daerah_dikunjungi": "{$daerah}",
  "tabel_kegiatan": [
    {
      "tanggal": "YYYY-MM-DD",
      "waktu": "08.00 - 08.30",
      "uraian_kegiatan": "Perjalanan dari Mempawah menuju ... dan berkoordinasi ...",
      "permasalahan_pemecahan": "-",
      "keterangan": "-"
    },
    {
      "tanggal": "YYYY-MM-DD",
      "waktu": "08.30 - 12.00",
      "uraian_kegiatan": "Perjalanan menuju lokasi sampel di desa ... dan melakukan ...",
      "permasalahan_pemecahan": "Petani tidak mengetahui dengan pasti ukuran lahan yang dimiliki/ melakukan pendekatan dengan penggunaan bibit",
      "keterangan": "1. Pendataan berjalan lancar dan dilaksanakan sesuai SOP\\n2. Berhasil melakukan pendataan pada 2 rumah tangga sampel"
    },
    {
      "tanggal": "YYYY-MM-DD",
      "waktu": "12.00 - 13.00",
      "uraian_kegiatan": "Istirahat",
      "permasalahan_pemecahan": "-",
      "keterangan": "-"
    },
    {
      "tanggal": "YYYY-MM-DD",
      "waktu": "13.00 - 14.30",
      "uraian_kegiatan": "Melanjutkan pelaksanaan pengawasan lapangan ...",
      "permasalahan_pemecahan": "-",
      "keterangan": "1. Pendataan berjalan lancar dan sesuai SOP\\n2. Hasil sampel ..."
    },
    {
      "tanggal": "YYYY-MM-DD",
      "waktu": "14.30 - 15.00",
      "uraian_kegiatan": "Perjalanan kembali ke Mempawah",
      "permasalahan_pemecahan": "-",
      "keterangan": "-"
    }
  ],
  "ringkasan": "Ringkasan formal perjalanan dinas keseluruhan (1 paragraf padat).",
  "kesimpulan": "Kesimpulan hasil pelaksanaan tugas.",
  "tindak_lanjut": "Tindak lanjut yang diperlukan."
}

PENTING:
1. Pastikan output HANYA berupa JSON valid, tanpa kata pengantar atau penutup.
2. Gunakan bahasa Indonesia formal (EYD) baku kedinasan BPS.
PROMPT;
    }

    /**
     * Generate structured periodic travel report from raw user inputs.
     *
     * @param array $periodikData Summary data from user input
     * @param array $metaContext Context metadata
     * @return array Polished report details structured as JSON
     * @throws Exception
     */
    public function generatePeriodicStructuredReport(array $periodikData, array $metaContext): array
    {
        $prompt = $this->buildPeriodicPrompt($periodikData, $metaContext);

        try {
            Log::info("AIService: Sending periodic report request to LLM using model {$this->model}...");
            
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
            ])
            ->timeout(60)
            ->post("{$this->baseUrl}/chat/completions", [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Anda adalah asisten AI profesional untuk penulisan Laporan Perjalanan Dinas resmi Badan Pusat Statistik (BPS). Anda selalu membalas dengan struktur JSON yang valid sesuai petunjuk tanpa penjelasan tambahan di luar JSON.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ]
                ],
                'response_format' => ['type' => 'json_object']
            ]);

            if ($response->failed()) {
                throw new Exception("LLM request failed with status: " . $response->status() . " - " . $response->body());
            }

            $result = $response->json();
            $content = $result['choices'][0]['message']['content'] ?? '';
            
            Log::info("AIService: Received response from LLM (Periodic).", ['content_length' => strlen($content)]);

            // Clean markdown code blocks if present
            $contentCleaned = trim($content);
            if (str_starts_with($contentCleaned, '```json')) {
                $contentCleaned = substr($contentCleaned, 7);
            }
            if (str_ends_with($contentCleaned, '```')) {
                $contentCleaned = substr($contentCleaned, 0, -3);
            }
            $contentCleaned = trim($contentCleaned);

            $parsedJson = json_decode($contentCleaned, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Failed to parse JSON from LLM: " . json_last_error_msg() . "\nRaw response: " . $content);
            }

            return $parsedJson;

        } catch (Exception $e) {
            Log::error("AIService Periodic Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Build the prompt text for periodic report.
     */
    protected function buildPeriodicPrompt(array $periodikData, array $metaContext): string
    {
        $kegiatan = $metaContext['kegiatan_nama'] ?? '';
        $suratTugas = $metaContext['nomor_surat_tugas'] ?? '';
        $transportasi = $metaContext['moda_transportasi'] ?? 'Kendaraan Pribadi';
        $daerah = $metaContext['daerah_dikunjungi'] ?? ($periodikData['cakupan_wilayah'] ?? 'Kecamatan di Kab. Mempawah');
        $periodeStr = $metaContext['periode_str'] ?? '';
        $cakupan = $periodikData['cakupan_wilayah'] ?: $daerah;
        $uraianDraft = $periodikData['uraian_draft'] ?? '';
        $kendalaDraft = $periodikData['kendala'] ?: 'Tidak ada kendala yang berarti dalam pelaksanaan kegiatan.';
        $koordinat = $periodikData['koordinat'] ?: '-';

        return <<<PROMPT
Rapikan dan transformasikan draf catatan pelaksanaan tugas perjalanan dinas/survei periodik di bawah ini menjadi format Laporan Resmi Hasil Pelaksanaan Tugas BPS yang komprehensif, terstruktur, padat, dan profesional.

Informasi Konteks Penugasan:
- Nama Kegiatan: "{$kegiatan}"
- Dasar Pelaksanaan: "{$suratTugas}"
- Moda Transportasi: "{$transportasi}"
- Periode Penugasan: "{$periodeStr}"
- Daerah/Cakupan Wilayah: "{$cakupan}"
- Titik Koordinat: "{$koordinat}"

Draf Catatan Pelaksanaan & Capaian:
"{$uraianDraft}"

Draf Kendala Lapangan:
"{$kendalaDraft}"

Anda harus merespons dalam format JSON dengan struktur yang persis seperti berikut:
{
  "dasar_pelaksanaan": "{$suratTugas}",
  "moda_transportasi": "{$transportasi}",
  "daerah_dikunjungi": "{$cakupan}",
  "ringkasan": "Isi dengan Ringkasan Eksekutif pelaksanaan tugas selama periode tersebut (1-2 paragraf formal dan padat).",
  "cakupan_wilayah": "Tuliskan ringkasan cakupan wilayah dan sasaran pelaksanaan tugas secara formal.",
  "uraian_kegiatan_polished": "Tuliskan uraian narasi komprehensif mengenai metodologi, tahapan pelaksanaan, capaian target kegiatan, serta progres pengumpulan/supervisi data selama periode tugas dengan bahasa Indonesia baku (EYD).",
  "kendala_polished": "Tuliskan analisis kendala lapangan dalam bahasa dinas yang formal dan objektif.",
  "solusi_polished": "Tuliskan langkah pemecahan masalah atau mitigasi yang rasional dan efektif.",
  "kesimpulan": "Isi dengan kesimpulan umum terhadap efektivitas dan capaian kegiatan dinas tersebut.",
  "tindak_lanjut": "Isi dengan rekomendasi tindak lanjut bagi instansi atau kegiatan survei tahap berikutnya."
}

PENTING:
1. Pastikan output hanya berupa JSON valid tanpa penjelasan tambahan di luar JSON.
2. Gunakan peristilahan statistik dan kedinasan yang baku.
PROMPT;
    }

    /**
     * Revise an existing structured report based on user feedback/instructions.
     *
     * @param array $currentReport The current generated report
     * @param string $instruction User's revision instruction
     * @param array $metaContext Context metadata
     * @return array Revised structured report
     * @throws Exception
     */
    public function reviseStructuredReport(array $currentReport, string $instruction, array $metaContext): array
    {
        $currentJson = json_encode($currentReport, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $kegiatan = $metaContext['kegiatan_nama'] ?? '';
        $daerah = $metaContext['daerah_dikunjungi'] ?? 'Kecamatan Mempawah Timur';

        $prompt = <<<PROMPT
Tugas Anda: Lakukan REVISI & PENYEMPURNAAN terhadap tabel laporan perjalanan dinas BPS berikut sesuai instruksi spesifik dari pegawai.

Konteks Kegiatan:
- Kegiatan: "{$kegiatan}"
- Wilayah: "{$daerah}"

Tabel Laporan Saat Ini:
{$currentJson}

Instruksi Revisi dari Pegawai:
"{$instruction}"

Ketentuan:
1. Pertahankan struktur data JSON 5-kolom yang sudah ada.
2. Terapkan perubahan/revisi persis sesuai instruksi pegawai (misalnya: penyesuaian jam, penyingkatan narasi, penambahan detail kendala, atau perbaikan istilah).
3. Pastikan urutan waktu tetap kronologis, logis, dan slot istirahat/sholat tetap terjaga.

Keluarkan HANYA JSON valid dengan struktur yang sama seperti tabel saat ini:
{
  "dasar_pelaksanaan": "...",
  "moda_transportasi": "...",
  "daerah_dikunjungi": "...",
  "tabel_kegiatan": [ ... ],
  "ringkasan": "...",
  "kesimpulan": "...",
  "tindak_lanjut": "..."
}
PROMPT;

        try {
            Log::info("AIService: Sending report revision request to LLM using model {$this->model}...");
            
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
            ])
            ->timeout(60)
            ->post("{$this->baseUrl}/chat/completions", [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Anda adalah asisten AI profesional untuk penulisan Laporan Perjalanan Dinas resmi BPS. Anda selalu membalas dengan struktur JSON yang valid sesuai petunjuk revisi.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ]
                ],
                'response_format' => ['type' => 'json_object']
            ]);

            if ($response->failed()) {
                throw new Exception("LLM request failed with status: " . $response->status() . " - " . $response->body());
            }

            $result = $response->json();
            $content = $result['choices'][0]['message']['content'] ?? '';
            
            $contentCleaned = trim($content);
            if (str_starts_with($contentCleaned, '```json')) {
                $contentCleaned = substr($contentCleaned, 7);
            }
            if (str_ends_with($contentCleaned, '```')) {
                $contentCleaned = substr($contentCleaned, 0, -3);
            }
            $contentCleaned = trim($contentCleaned);

            $parsedJson = json_decode($contentCleaned, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Failed to parse LLM revision JSON response: " . json_last_error_msg());
            }

            return $parsedJson;

        } catch (Exception $e) {
            Log::error("AIService Revision Error: " . $e->getMessage());
            throw $e;
        }
    }
}
