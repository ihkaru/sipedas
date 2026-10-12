<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\RequestException;
use Throwable;

class WhatsappNotifier
{
    /**
     * Mengirim pesan WhatsApp menggunakan Aina Whatsmeow Gateway (default) atau Fonnte API dengan logging terpusat.
     * Mendukung opt-out notifikasi secara global, per jenis event, atau per nomor pegawai melalui menu Pengaturan.
     *
     * @param string|null $targetNumber Nomor tujuan (contoh: '6281234567890', '081234567890', atau '628xxx@s.whatsapp.net')
     * @param string $message Isi pesan yang akan dikirim
     * @param string|null $event Kategori event pengajuan ('whatsapp_notify_pengajuan_baru', 'whatsapp_notify_tanggapan_pengaju', 'whatsapp_notify_hasil_pemeriksaan', 'whatsapp_notify_pencairan')
     * @return \Illuminate\Http\Client\Response|null Respons dari HTTP client atau null jika gagal/opt-out.
     */
    public static function send($targetNumber, $message, ?string $event = null)
    {
        if (empty($targetNumber)) {
            Log::warning('WhatsappNotifier: Attempted to send message to an empty target number.', [
                'message' => $message,
            ]);
            return null;
        }

        // 1. Cek Master Opt-Out: apakah notifikasi WhatsApp aktif secara global di Setting
        if (!self::isNotificationEnabled()) {
            Log::info('WhatsappNotifier: Notification suppressed because WhatsApp notifications are disabled globally in settings.', [
                'target' => $targetNumber,
                'event' => $event,
            ]);
            return null;
        }

        // 2. Cek Event Opt-Out: apakah kategori event ini dinonaktifkan di Setting
        if ($event && !self::isEventEnabled($event)) {
            Log::info("WhatsappNotifier: Notification suppressed because event '{$event}' is disabled in settings.", [
                'target' => $targetNumber,
                'event' => $event,
            ]);
            return null;
        }

        // 3. Cek Recipient Opt-Out: apakah nomor tujuan terdaftar dalam daftar opt-out
        if (self::isNumberOptedOut($targetNumber)) {
            Log::info('WhatsappNotifier: Notification suppressed because recipient opted out of WhatsApp notifications.', [
                'target' => $targetNumber,
                'event' => $event,
            ]);
            return null;
        }

        $provider = strtolower((string) config('services.whatsapp.provider', env('WHATSAPP_PROVIDER', 'aina')));

        if ($provider === 'aina' || $provider === 'whatsmeow') {
            return self::sendViaAina($targetNumber, $message);
        }

        return self::sendViaFonnte($targetNumber, $message);
    }

    /**
     * Memeriksa apakah notifikasi WhatsApp diaktifkan secara global di Setting.
     */
    public static function isNotificationEnabled(): bool
    {
        try {
            $setting = Setting::where('key', 'whatsapp_notification_enabled')->value('value');
            if ($setting !== null) {
                return filter_var($setting, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? ($setting !== '0');
            }
        } catch (Throwable $e) {
            Log::warning('WhatsappNotifier: Failed to read whatsapp_notification_enabled from settings: ' . $e->getMessage());
        }

        return true;
    }

    /**
     * Memeriksa apakah kategori event notifikasi tertentu aktif di Setting.
     */
    public static function isEventEnabled(?string $eventKey): bool
    {
        if (empty($eventKey)) {
            return true;
        }

        try {
            $setting = Setting::where('key', $eventKey)->value('value');
            if ($setting !== null) {
                return filter_var($setting, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? ($setting !== '0');
            }
        } catch (Throwable $e) {
            Log::warning("WhatsappNotifier: Failed to read event setting '{$eventKey}': " . $e->getMessage());
        }

        return true;
    }

    /**
     * Memeriksa apakah nomor target terdaftar dalam daftar opt-out (menolak notifikasi).
     */
    public static function isNumberOptedOut(?string $targetNumber): bool
    {
        if (empty($targetNumber)) {
            return false;
        }

        try {
            $rawSetting = Setting::where('key', 'whatsapp_opt_out_numbers')->value('value');
            if (empty($rawSetting)) {
                return false;
            }

            $optedOutList = is_array($rawSetting)
                ? $rawSetting
                : (json_decode($rawSetting, true) ?? array_map('trim', explode(',', $rawSetting)));

            if (!is_array($optedOutList) || empty($optedOutList)) {
                return false;
            }

            $targetDigits = preg_replace('/[^0-9]/', '', $targetNumber);
            if (empty($targetDigits)) {
                return false;
            }

            foreach ($optedOutList as $opted) {
                $optedDigits = preg_replace('/[^0-9]/', '', (string) $opted);
                if (empty($optedDigits)) {
                    continue;
                }

                // Cek nomor sama persis
                if ($targetDigits === $optedDigits) {
                    return true;
                }

                // Cek sufiks 8-9 digit terakhir (mengakomodasi awalan 08 vs 628)
                $suffixLen = min(9, min(strlen($targetDigits), strlen($optedDigits)));
                if ($suffixLen >= 7 && substr($targetDigits, -$suffixLen) === substr($optedDigits, -$suffixLen)) {
                    return true;
                }
            }
        } catch (Throwable $e) {
            Log::warning('WhatsappNotifier: Failed to evaluate opt-out setting: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Mengirim pesan via Gateway Whatsmeow (Nomor Bot Aina).
     */
    protected static function sendViaAina(string $targetNumber, string $message)
    {
        $baseUrl = rtrim((string) config('services.whatsapp.aina.base_url', env('WHATSMEOW_BASE_URL', 'https://aina-wa.dvlpid.my.id')), '/');
        $apiKey = (string) config('services.whatsapp.aina.api_key', env('WHATSMEOW_API_KEY', ''));

        // Jika API Key Aina belum diset tapi Fonnte token tersedia, fallback otomatis ke Fonnte
        if (empty($apiKey)) {
            $fonnteToken = config('services.whatsapp.fonnte.token', env('FONNTE_TOKEN'));
            if (!empty($fonnteToken)) {
                Log::warning('WhatsappNotifier: WHATSMEOW_API_KEY is empty. Falling back to Fonnte provider.');
                return self::sendViaFonnte($targetNumber, $message);
            }

            Log::critical('WhatsappNotifier: WHATSMEOW_API_KEY is not configured in .env file.');
            return null;
        }

        $recipient = self::normalizeAinaRecipient($targetNumber);

        try {
            Log::info('Attempting to send WhatsApp message via Aina Whatsmeow Gateway.', [
                'target' => $recipient,
                'gateway' => $baseUrl,
            ]);

            $endpoint = "{$baseUrl}/api/v1/messages/send-text";
            $headers = [
                'X-API-Key' => $apiKey,
                'Authorization' => "Bearer {$apiKey}",
                'Accept' => 'application/json',
            ];

            $payload = [
                'recipient' => $recipient,
                'content' => $message,
                'to' => $recipient,
                'message' => $message,
            ];

            $response = Http::withHeaders($headers)
                ->timeout(15)
                ->post($endpoint, $payload);

            // Fallback ke legacy endpoint jika endpoint v1 menghasilkan 404
            if ($response->status() === 404) {
                Log::warning('WhatsappNotifier: /api/v1/messages/send-text returned 404, falling back to /send/message.');
                $response = Http::withHeaders($headers)
                    ->timeout(15)
                    ->post("{$baseUrl}/send/message", $payload);
            }

            if ($response->successful()) {
                $responseBody = $response->json() ?? ['raw_body' => $response->body()];
                Log::info('WhatsApp message sent successfully via Aina Whatsmeow Gateway.', [
                    'target' => $recipient,
                    'response' => $responseBody,
                ]);
            } else {
                Log::error('Failed to send WhatsApp message via Aina Whatsmeow Gateway.', [
                    'target' => $recipient,
                    'status_code' => $response->status(),
                    'response_body' => $response->body(),
                ]);
            }

            return $response;
        } catch (RequestException $e) {
            Log::critical('Connection error to Aina Whatsmeow Gateway.', [
                'target' => $recipient,
                'error_message' => $e->getMessage(),
            ]);
            return null;
        } catch (Throwable $e) {
            Log::critical('An unexpected error occurred in WhatsappNotifier (Aina).', [
                'target' => $recipient,
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Mengirim pesan via Fonnte API (Vendor Pihak Ketiga).
     */
    protected static function sendViaFonnte(string $targetNumber, string $message)
    {
        $token = (string) config('services.whatsapp.fonnte.token', env('FONNTE_TOKEN'));

        if (empty($token)) {
            Log::critical('FONNTE_TOKEN is not set in .env file.');
            return null;
        }

        $cleanTarget = preg_replace('/[^0-9]/', '', $targetNumber);

        try {
            Log::info('Attempting to send WhatsApp message via Fonnte.', [
                'target' => $cleanTarget,
            ]);

            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->timeout(10)->asForm()->post('https://api.fonnte.com/send', [
                'target' => $cleanTarget,
                'message' => $message,
                'countryCode' => '62',
            ]);

            if ($response->successful()) {
                $responseBody = $response->json() ?? ['raw_body' => $response->body()];
                Log::info('WhatsApp message sent successfully via Fonnte.', [
                    'target' => $cleanTarget,
                    'response' => $responseBody,
                ]);
            } else {
                Log::error('Failed to send WhatsApp message. Fonnte API returned an HTTP error.', [
                    'target' => $cleanTarget,
                    'status_code' => $response->status(),
                    'response_body' => $response->body(),
                ]);
            }

            return $response;
        } catch (RequestException $e) {
            Log::critical('Failed to connect to Fonnte API.', [
                'target' => $cleanTarget,
                'error_message' => $e->getMessage(),
            ]);
            return null;
        } catch (Throwable $e) {
            Log::critical('An unexpected error occurred in WhatsappNotifier (Fonnte).', [
                'target' => $cleanTarget,
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Normalisasi nomor telepon ke format target Aina Whatsmeow (JID atau nomor standar).
     */
    public static function normalizeAinaRecipient(string $rawNumber): string
    {
        $trimmed = trim($rawNumber);

        // Jika sudah berbentuk JID (mengandung @s.whatsapp.net atau @g.us), gunakan langsung
        if (str_contains($trimmed, '@')) {
            return $trimmed;
        }

        // Bersihkan seluruh karakter non-angka
        $digits = preg_replace('/[^0-9]/', '', $trimmed);

        // Format angka lokal Indonesia ke standar internasional
        if (str_starts_with($digits, '08')) {
            $digits = '628' . substr($digits, 2);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62' . $digits;
        }

        return $digits . '@s.whatsapp.net';
    }
}
