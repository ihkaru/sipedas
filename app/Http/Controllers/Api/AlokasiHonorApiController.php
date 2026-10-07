<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CheckEligibilityRequest;
use App\Http\Requests\Api\StoreAlokasiHonorRequest;
use App\Http\Resources\Api\AlokasiHonorResource;
use App\Models\AlokasiHonor;
use App\Services\ApiAuditService;
use App\Services\HonorAllocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AlokasiHonorApiController extends Controller
{
    /**
     * Tampilkan daftar Alokasi Honor.
     */
    public function index(Request $request): JsonResponse
    {
        $query = AlokasiHonor::with(['mitra', 'honor.kegiatanManmit', 'kontrak', 'bast']);

        if ($request->filled('honor_id')) {
            $query->where('honor_id', $request->input('honor_id'));
        }

        if ($request->filled('mitra_id')) {
            $query->where('mitra_id', $request->input('mitra_id'));
        }

        if ($request->filled('kegiatan_manmit_id')) {
            $kegiatanId = $request->input('kegiatan_manmit_id');
            $query->whereHas('honor', fn($q) => $q->where('kegiatan_manmit_id', $kegiatanId));
        }

        if ($request->filled('tahun')) {
            $query->whereYear('tanggal_mulai_perjanjian', $request->input('tahun'));
        }

        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal_mulai_perjanjian', $request->input('bulan'));
        }

        $perPage = min((int)$request->input('per_page', 20), 100);
        $paginated = $query->latest('id')->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => AlokasiHonorResource::collection($paginated),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
        ]);
    }

    /**
     * Dry-run / Pre-flight check kelayakan alokasi mitra.
     */
    public function check(CheckEligibilityRequest $request): JsonResponse
    {
        $mitraIdentifier = $request->input('mitra_id') ?? $request->input('id_sobat');
        $honorId = $request->input('honor_id');
        $target = (float)$request->input('target');

        $result = HonorAllocationService::checkEligibility($mitraIdentifier, $honorId, $target);

        return response()->json([
            'status' => 'success',
            'message' => $result['message'],
            'data' => $result,
        ]);
    }

    /**
     * Buat alokasi baru dan terbitkan/hubungkan nomor SPK & BAST secara otomatis.
     */
    public function store(StoreAlokasiHonorRequest $request): JsonResponse
    {
        if ($request->has('allocations')) {
            $batchResult = HonorAllocationService::allocateBatch($request->input('allocations'));

            $createdIds = collect($batchResult['results'])->pluck('id')->values()->toArray();

            ApiAuditService::record(
                request: $request,
                action: 'BATCH_ALLOCATE',
                targetModel: AlokasiHonor::class,
                targetId: null,
                stateBefore: null,
                stateAfter: [
                    'created_ids' => $createdIds,
                    'success_count' => $batchResult['success_count'],
                    'failed_count' => $batchResult['failed_count'],
                ],
                statusCode: $batchResult['failed_count'] === 0 ? 201 : 207,
                isReversible: count($createdIds) > 0
            );

            $transformedResults = collect($batchResult['results'])
                ->map(fn($alokasi) => new AlokasiHonorResource($alokasi));

            return response()->json([
                'status' => $batchResult['failed_count'] === 0 ? 'success' : 'partial',
                'message' => "Proses batch selesai: {$batchResult['success_count']} berhasil, {$batchResult['failed_count']} gagal.",
                'data' => [
                    'success_count' => $batchResult['success_count'],
                    'failed_count' => $batchResult['failed_count'],
                    'successful_allocations' => $transformedResults,
                    'errors' => $batchResult['errors'],
                ],
            ], $batchResult['failed_count'] === 0 ? 201 : 207);
        }

        $mitraIdentifier = $request->input('mitra_id') ?? $request->input('id_sobat');
        $honorId = $request->input('honor_id');
        $target = (float)$request->input('target');

        try {
            $alokasi = HonorAllocationService::allocate($mitraIdentifier, $honorId, $target);

            // Rekam audit log dengan state snapshot
            ApiAuditService::record(
                request: $request,
                action: 'ALLOCATE_HONOR',
                targetModel: AlokasiHonor::class,
                targetId: $alokasi->id,
                stateBefore: null,
                stateAfter: $alokasi->toArray(),
                statusCode: 201,
                isReversible: true
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Alokasi honor berhasil dibuat dan nomor SPK/BAST telah diterbitkan.',
                'data' => new AlokasiHonorResource($alokasi),
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi bisnis gagal.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Tampilkan detail satu alokasi honor.
     */
    public function show($id): JsonResponse
    {
        $alokasi = AlokasiHonor::with(['mitra', 'honor.kegiatanManmit', 'kontrak', 'bast'])->find($id);

        if (!$alokasi) {
            return response()->json([
                'status' => 'error',
                'message' => 'Alokasi honor tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => new AlokasiHonorResource($alokasi),
        ]);
    }

    /**
     * Hapus alokasi honor.
     */
    public function destroy($id, Request $request): JsonResponse
    {
        $alokasi = AlokasiHonor::find($id);

        if (!$alokasi) {
            return response()->json([
                'status' => 'error',
                'message' => 'Alokasi honor tidak ditemukan.',
            ], 404);
        }

        $stateBefore = $alokasi->toArray();
        $alokasiId = $alokasi->id;

        $alokasi->delete();

        // Rekam audit log
        ApiAuditService::record(
            request: $request,
            action: 'DELETE_ALLOCATION',
            targetModel: AlokasiHonor::class,
            targetId: $alokasiId,
            stateBefore: $stateBefore,
            stateAfter: null,
            statusCode: 200,
            isReversible: true
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Alokasi honor berhasil dihapus.',
        ]);
    }
}
