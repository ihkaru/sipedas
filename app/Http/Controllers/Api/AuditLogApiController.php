<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiAuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogApiController extends Controller
{
    /**
     * Tampilkan riwayat audit log aktivitas API.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ApiAuditLog::with(['user', 'apiKey', 'rolledBackByUser'])->latest('id');

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->has('is_rolled_back')) {
            $query->where('is_rolled_back', $request->boolean('is_rolled_back'));
        }

        $perPage = min((int)$request->input('per_page', 20), 100);
        $logs = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $logs->items(),
            'meta' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'total' => $logs->total(),
            ],
        ]);
    }

    /**
     * Eksekusi rollback terhadap aksi mutasi tertentu.
     */
    public function rollback($id, Request $request): JsonResponse
    {
        $log = ApiAuditLog::find($id);

        if (!$log) {
            return response()->json([
                'status' => 'error',
                'message' => 'Catatan audit log tidak ditemukan.',
            ], 404);
        }

        try {
            $reason = $request->input('reason', 'Rollback dipicu via REST API');
            $currentUser = auth()->user();

            $result = $log->executeRollback($currentUser, $reason);

            return response()->json([
                'status' => 'success',
                'message' => $result['message'],
                'data' => [
                    'audit_log_id' => $log->id,
                    'action' => $log->action,
                    'is_rolled_back' => true,
                    'rolled_back_at' => $log->rolled_back_at?->toISOString(),
                ],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat memproses rollback: ' . $e->getMessage(),
            ], 500);
        }
    }
}
