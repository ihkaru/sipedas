<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $this->extractToken($request);

        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: API Key tidak disertakan. Gunakan header X-API-KEY atau Authorization Bearer token.',
            ], 401);
        }

        // 1. Cek apakah token cocok dengan master DOKTER_V_API_KEY / SIPEDAS_API_KEY dari env
        $configuredApiKey = config('dokter_v.api_key') ?: config('sipedas.api_key');
        if (!empty($configuredApiKey) && hash_equals($configuredApiKey, $token)) {
            return $next($request);
        }

        // 2. Cek apakah token cocok dengan ApiKey model pengguna (Filament managed)
        $apiKeyModel = ApiKey::where('key', $token)->first();
        if ($apiKeyModel) {
            if (!$apiKeyModel->isValid()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized: API Key telah dinonaktifkan atau kedaluwarsa.',
                ], 401);
            }

            // Catat waktu dan IP terakhir kali API Key digunakan
            $apiKeyModel->update([
                'last_used_at' => now(),
                'last_used_ip' => $request->ip(),
            ]);

            if ($apiKeyModel->user) {
                auth()->setUser($apiKeyModel->user);
            }
            $request->attributes->set('api_key', $apiKeyModel);

            return $next($request);
        }

        // 3. Cek apakah token cocok dengan Sanctum Personal Access Token
        $sanctumToken = PersonalAccessToken::findToken($token);
        if ($sanctumToken && $sanctumToken->tokenable) {
            if ($sanctumToken->expires_at && $sanctumToken->expires_at->isPast()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized: API Key (Sanctum Token) telah kedaluwarsa.',
                ], 401);
            }

            auth()->setUser($sanctumToken->tokenable);
            return $next($request);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Unauthorized: API Key tidak valid.',
        ], 401);
    }

    /**
     * Ekstraksi token dari header atau query string.
     */
    protected function extractToken(Request $request): ?string
    {
        // 1. Header X-API-KEY
        $apiKey = $request->header('X-API-KEY');
        if ($apiKey) {
            return trim($apiKey);
        }

        // 2. Authorization Bearer Token
        $bearer = $request->bearerToken();
        if ($bearer) {
            return trim($bearer);
        }

        // 3. Fallback query parameter 'api_key'
        $queryKey = $request->query('api_key');
        if ($queryKey && is_string($queryKey)) {
            return trim($queryKey);
        }

        return null;
    }
}
