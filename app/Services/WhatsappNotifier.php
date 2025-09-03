<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log; // 1. Import Log Facade
use Illuminate\Http\Client\RequestException; // 2. Import untuk menangani error koneksi
use Throwable; // 3. Import untuk menangani error umum

class WhatsappNotifier
{
    /**
     * Mengirim pesan WhatsApp menggunakan Fonnte API dengan logging.
     *
     * @param string|null $targetNumber Nomor tujuan (contoh: '6281234567890')
     * @param string $message Isi pesan yang akan dikirim
     * @return \Illuminate\Http\Client\Response|null Respons dari HTTP client atau null jika gagal total.
     */
    public static function send($targetNumber, $message)
    {
        $token = env('FONNTE_TOKEN');

        // Pengecekan awal untuk token dan nomor tujuan
        if (empty($token)) {
            Log::critical('FONNTE_TOKEN is not set in .env file.');
            return null;
        }

        if (empty($targetNumber)) {
            Log::warning('WhatsappNotifier: Attempted to send message to an empty target number.', [
                'message' => $message
            ]);
            return null;
        }

        try {
            // Catat percobaan pengiriman
            Log::info('Attempting to send WhatsApp message via Fonnte.', [
                'target' => $targetNumber,
            ]);

            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->asForm()->post('https://api.fonnte.com/send', [
                'target' => $targetNumber,
                'message' => $message,
                'countryCode' => '62', // Opsional, bisa dihapus jika default sudah sesuai
            ]);

            // Periksa apakah request HTTP berhasil dan respons dari Fonnte juga sukses
            // Berdasarkan dokumentasi Fonnte, respons sukses memiliki status 200 OK.
            if ($response->successful()) {
                // Fonnte tidak selalu memberikan body JSON, kadang hanya teks, jadi kita cek.
                $responseBody = $response->json() ?? ['raw_body' => $response->body()];
                Log::info('WhatsApp message sent successfully.', [
                    'target' => $targetNumber,
                    'response' => $responseBody,
                ]);
            } else {
                // Jika request HTTP gagal (misal: status 401, 403, 500)
                Log::error('Failed to send WhatsApp message. Fonnte API returned an HTTP error.', [
                    'target' => $targetNumber,
                    'status_code' => $response->status(),
                    'response_body' => $response->body(),
                ]);
            }

            return $response;
        } catch (RequestException $e) {
            // Menangkap error koneksi (misal: timeout, DNS error, API down)
            Log::critical('Failed to connect to Fonnte API.', [
                'target' => $targetNumber,
                'error_message' => $e->getMessage(),
            ]);
            return null; // Mengembalikan null karena tidak ada objek response

        } catch (Throwable $e) {
            // Menangkap error tak terduga lainnya
            Log::critical('An unexpected error occurred in WhatsappNotifier.', [
                'target' => $targetNumber,
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }
}
