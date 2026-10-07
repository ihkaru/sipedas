<?php

namespace App\Services;

use App\Models\ApiAuditLog;
use Illuminate\Http\Request;

class ApiAuditService
{
    /**
     * Catat aktivitas mutasi API ke dalam tabel audit log.
     */
    public static function record(
        Request $request,
        string $action,
        string $targetModel,
        ?int $targetId = null,
        ?array $stateBefore = null,
        ?array $stateAfter = null,
        int $statusCode = 200,
        bool $isReversible = true
    ): ApiAuditLog {
        $apiKey = $request->attributes->get('api_key');
        $user = auth()->user() ?? $apiKey?->user;

        return ApiAuditLog::create([
            'user_id' => $user?->id,
            'api_key_id' => $apiKey?->id,
            'action' => $action,
            'method' => $request->method(),
            'endpoint' => '/' . ltrim($request->path(), '/'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'payload' => $request->except(['api_key', 'token', 'password']),
            'response_status' => $statusCode,
            'target_model' => $targetModel,
            'target_id' => $targetId,
            'state_before' => $stateBefore,
            'state_after' => $stateAfter,
            'is_reversible' => $isReversible,
        ]);
    }
}
