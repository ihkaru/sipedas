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
     * Dilengkapi search, filter reversible, compact mode hemat token, dan pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ApiAuditLog::with(['user', 'apiKey', 'rolledBackByUser'])->latest('id');

        // 1. Search keyword
        $search = $request->input('q') ?? $request->input('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->orWhere('endpoint', 'like', "%{$search}%")
                  ->orWhere('rollback_reason', 'like', "%{$search}%");
            });
        }

        // 2. Filter Action
        if ($request->filled('action')) {
            $query->where('action', strtoupper($request->input('action')));
        }

        // 3. Filter Target Record ID
        if ($request->filled('target_id')) {
            $query->where('target_id', $request->input('target_id'));
        }

        // 4. Filter User ID
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        // 5. Shortcut: Only Rollbackable (Bisa di-rollback & belum pernah di-rollback)
        if ($request->boolean('only_rollbackable', false)) {
            $query->where('is_reversible', true)
                  ->where('is_rolled_back', false);
        } else {
            if ($request->has('is_reversible')) {
                $query->where('is_reversible', $request->boolean('is_reversible'));
            }

            if ($request->has('is_rolled_back')) {
                $query->where('is_rolled_back', $request->boolean('is_rolled_back'));
            }
        }

        $perPage = min(max((int)($request->input('per_page') ?? $request->input('limit') ?? 20), 1), 100);
        $paginated = $query->paginate($perPage);

        // 6. Compact / Token-Dense Mode (Default compact kecuali full=1 atau with_state=1)
        $isFull = $request->boolean('full', false) || $request->boolean('with_state', false);

        if (!$isFull) {
            // Hilangkan payload besar JSON state_before & state_after demi menghemat token AI
            $items = collect($paginated->items())->map(function ($log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'method' => $log->method,
                    'endpoint' => $log->endpoint,
                    'target_id' => $log->target_id,
                    'status_code' => $log->response_status,
                    'is_reversible' => $log->is_reversible,
                    'is_rolled_back' => $log->is_rolled_back,
                    'rolled_back_at' => $log->rolled_back_at?->toISOString(),
                    'created_at' => $log->created_at?->toISOString(),
                    'user' => $log->user?->name ?? 'System',
                    'api_key' => $log->apiKey?->name,
                ];
            });
        } else {
            $items = $paginated->items();
        }

        return response()->json([
            'status' => 'success',
            'data' => $items,
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'has_more' => $paginated->hasMorePages(),
            ],
        ]);
    }

    /**
     * Tampilkan detail satu audit log tertentu (lengkap dengan state_before & state_after).
     */
    public function show($id): JsonResponse
    {
        $log = ApiAuditLog::with(['user', 'apiKey', 'rolledBackByUser'])->find($id);

        if (!$log) {
            return response()->json([
                'status' => 'error',
                'message' => 'Catatan audit log tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $log,
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
