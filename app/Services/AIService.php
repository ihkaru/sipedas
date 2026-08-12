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
        $this->baseUrl = 'https://ai.dvlpid.my.id/v1';
        $this->apiKey = 'sk-af6376fcf20b4a148672456a6cae1902';
        $this->model = 'gemini-2.5-flash';
    }

    /**
     * Generate structured travel report from raw user inputs.
     *
     * @param array $rawInput Daily logs from user input
     * @param string $kegiatanNama Name of the business trip activity
     * @return array Polished report details structured as JSON
     * @throws Exception
     */
    public function generateStructuredReport(array $rawInput, string $kegiatanNama): array
    {
        $prompt = $this->buildPrompt($rawInput, $kegiatanNama);

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
                        'content' => 'Anda adalah asisten AI profesional untuk penulisan Laporan Perjalanan Dinas resmi instansi pemerintah (BPS). Anda selalu membalas dengan struktur JSON yang valid sesuai petunjuk tanpa penjelasan tambahan.',
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
    protected function buildPrompt(array $rawInput, string $kegiatanNama): string
    {
        $inputStr = json_encode($rawInput, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        
        return <<<PROMPT
Rapikan dan ubah uraian kegiatan perjalanan dinas acak/tidak terstruktur di bawah ini menjadi format laporan formal yang rapi, padat, dan profesional untuk Laporan Perjalanan Dinas BPS.

Nama Kegiatan: "{$kegiatanNama}"

Input data harian (berisi tanggal, waktu, koordinat, draf uraian kegiatan, dan draf kendala):
{$inputStr}

Anda harus merespons dalam format JSON dengan struktur yang persis seperti berikut:
{
  "ringkasan": "Isi dengan ringkasan/eksekutif summary perjalanan dinas keseluruhan secara profesional dan formal (1-2 paragraf).",
  "kegiatan_harian": [
    {
      "tanggal": "YYYY-MM-DD (samakan dengan input)",
      "waktu": "HH:MM - HH:MM (samakan dengan input)",
      "koordinat": "Latitude, Longitude (samakan dengan input)",
      "uraian_polished": "Tuliskan uraian kegiatan yang telah dirapikan menggunakan bahasa Indonesia formal (EYD), padat, jelas, dan menggunakan istilah dinas/instansi yang tepat.",
      "kendala_polished": "Tuliskan kendala yang dihadapi dalam bahasa formal dan profesional. Jika tidak ada kendala di input, isi dengan 'Tidak ada kendala yang berarti dalam pelaksanaan kegiatan.'",
      "solusi_polished": "Berikan solusi yang rasional dan konstruktif terhadap kendala tersebut. Jika tidak ada kendala, isi dengan 'Pelaksanaan berjalan lancar sesuai rencana.'"
    }
  ],
  "kesimpulan": "Isi dengan kesimpulan umum dari seluruh hasil perjalanan dinas secara profesional.",
  "tindak_lanjut": "Isi dengan rekomendasi atau langkah tindak lanjut yang diperlukan setelah kegiatan ini."
}

PENTING:
1. Pastikan output hanya berupa JSON valid tersebut, tanpa ada penjelasan, pembuka, atau penutup apapun di luar JSON.
2. Perbaiki tata bahasa, singkatan tidak resmi (misalnya: "ketemu" -> "bertemu", "pake" -> "menggunakan", "dgn" -> "dengan"), dan buat narasinya mengalir secara profesional.
PROMPT;
    }
}
