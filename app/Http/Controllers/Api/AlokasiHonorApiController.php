<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CheckEligibilityRequest;
use App\Http\Requests\Api\StoreAlokasiHonorRequest;
use App\Http\Resources\Api\AlokasiHonorResource;
use App\Models\AlokasiHonor;
use App\Models\Honor;
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

        // 1. Search (Keyword pada nama mitra, id_sobat, nik, nama kegiatan, no SPK, no BAST)
        $search = $request->input('q') ?? $request->input('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('mitra', function ($m) use ($search) {
                    $m->where('nama_1', 'like', "%{$search}%")
                      ->orWhere('id_sobat', 'like', "%{$search}%")
                      ->orWhere('nik', 'like', "%{$search}%");
                })
                ->orWhereHas('honor.kegiatanManmit', function ($k) use ($search) {
                    $k->where('nama', 'like', "%{$search}%")
                      ->orWhere('id', 'like', "%{$search}%");
                })
                ->orWhereHas('kontrak', function ($ns) use ($search) {
                    $ns->searchNomor($search);
                })
                ->orWhereHas('bast', function ($ns) use ($search) {
                    $ns->searchNomor($search);
                });
            });
        }

        // 2. Filter Honor ID
        if ($request->filled('honor_id')) {
            $query->where('honor_id', $request->input('honor_id'));
        }

        // 3. Filter Mitra ID / ID Sobat
        if ($request->filled('mitra_id')) {
            $query->where('mitra_id', $request->input('mitra_id'));
        } elseif ($request->filled('id_sobat')) {
            $idSobat = $request->input('id_sobat');
            $query->whereHas('mitra', fn($m) => $m->where('id_sobat', $idSobat));
        }

        // 4. Filter Kegiatan ID
        $kegiatanId = $request->input('kegiatan_manmit_id') ?? $request->input('kegiatan_id');
        if ($kegiatanId) {
            $query->whereHas('honor', fn($q) => $q->where('kegiatan_manmit_id', $kegiatanId));
        }

        // 5. Filter Tahun & Bulan
        if ($request->filled('tahun')) {
            $query->whereYear('tanggal_mulai_perjanjian', $request->input('tahun'));
        }

        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal_mulai_perjanjian', $request->input('bulan'));
        }

        // 6. Sorting
        $allowedSorts = ['id', 'total_honor', 'target_per_satuan_honor', 'tanggal_mulai_perjanjian', 'created_at'];
        $sortBy = in_array($request->input('sort_by'), $allowedSorts) ? $request->input('sort_by') : 'id';
        $sortOrder = strtolower($request->input('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortBy, $sortOrder);

        // 7. Pagination
        $perPage = min(max((int)($request->input('per_page') ?? $request->input('limit') ?? 20), 1), 100);
        $paginated = $query->paginate($perPage);

        // 8. Compact / Token-Dense Mode
        $isCompact = $request->boolean('compact', false) || $request->boolean('summary', false);

        if ($isCompact) {
            $data = collect($paginated->items())->map(function ($alokasi) {
                return [
                    'id' => $alokasi->id,
                    'mitra_id' => $alokasi->mitra_id,
                    'nama_mitra' => $alokasi->mitra?->nama_1,
                    'id_sobat' => $alokasi->mitra?->id_sobat,
                    'kegiatan_id' => $alokasi->honor?->kegiatan_manmit_id,
                    'nama_kegiatan' => $alokasi->honor?->kegiatanManmit?->nama,
                    'honor_id' => $alokasi->honor_id,
                    'target' => (float)$alokasi->target_per_satuan_honor,
                    'total_honor' => (float)$alokasi->total_honor,
                    'nomor_spk' => $alokasi->kontrak?->nomor_surat_perjanjian_kerja,
                    'nomor_bast' => $alokasi->bast?->nomor_surat_bast,
                    'created_at' => $alokasi->created_at?->toISOString(),
                ];
            });
        } else {
            $data = AlokasiHonorResource::collection($paginated);
        }

        return response()->json([
            'status' => 'success',
            'data' => $data,
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

    /**
     * Update alokasi honor (target volume, tanggal, status, atau honor_id).
     * Memvalidasi ulang pagu SBML & bentrok sensus, serta mencatat audit log reversible.
     */
    public function update(int $id, Request $request): JsonResponse
    {
        $alokasi = AlokasiHonor::with(['mitra', 'honor.kegiatanManmit', 'kontrak', 'bast'])->find($id);

        if (!$alokasi) {
            return response()->json([
                'status' => 'error',
                'message' => "Alokasi honor dengan ID #{$id} tidak ditemukan.",
            ], 404);
        }

        $request->validate([
            'target' => ['nullable', 'numeric', 'gt:0'],
            'target_per_satuan_honor' => ['nullable', 'numeric', 'gt:0'],
            'honor_id' => ['nullable', 'string', 'exists:honors,id'],
            'status' => ['nullable', 'string', 'max:50'],
            'tanggal_mulai_perjanjian' => ['nullable', 'date'],
            'tanggal_akhir_perjanjian' => ['nullable', 'date', 'after_or_equal:tanggal_mulai_perjanjian'],
            'tanggal_penanda_tanganan_spk_oleh_petugas' => ['nullable', 'date'],
        ]);

        $honor = $request->filled('honor_id')
            ? Honor::with('kegiatanManmit')->find($request->input('honor_id'))
            : $alokasi->honor;

        $targetInput = $request->input('target') ?? $request->input('target_per_satuan_honor');
        $newTarget = $targetInput !== null ? (float)$targetInput : (float)$alokasi->target_per_satuan_honor;

        // Jika target atau honor berubah, validasi ulang limit SBML & bentrok sensus
        if ($targetInput !== null || $request->filled('honor_id')) {
            $eligibility = HonorAllocationService::validateEligibility(
                mitra: $alokasi->mitra,
                honor: $honor,
                target: $newTarget,
                excludeAlokasiId: $alokasi->id
            );

            if (!$eligibility['eligible']) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pembaruan alokasi gagal validasi bisnis: ' . $eligibility['message'],
                ], 422);
            }
        }

        $stateBefore = $alokasi->toArray();

        $updates = [];
        if ($targetInput !== null) {
            $updates['target_per_satuan_honor'] = $newTarget;
            $updates['total_honor'] = $newTarget * (float)$honor->harga_per_satuan;
        }
        if ($request->filled('honor_id')) {
            $updates['honor_id'] = $request->input('honor_id');
            if ($targetInput === null) {
                $updates['total_honor'] = (float)$alokasi->target_per_satuan_honor * (float)$honor->harga_per_satuan;
            }
        }
        if ($request->has('status')) {
            $updates['status'] = $request->input('status');
        }
        if ($request->has('tanggal_mulai_perjanjian')) {
            $updates['tanggal_mulai_perjanjian'] = $request->input('tanggal_mulai_perjanjian');
        }
        if ($request->has('tanggal_akhir_perjanjian')) {
            $updates['tanggal_akhir_perjanjian'] = $request->input('tanggal_akhir_perjanjian');
        }
        if ($request->has('tanggal_penanda_tanganan_spk_oleh_petugas')) {
            $updates['tanggal_penanda_tanganan_spk_oleh_petugas'] = $request->input('tanggal_penanda_tanganan_spk_oleh_petugas');
        }

        $alokasi->update($updates);

        $stateAfter = $alokasi->fresh(['mitra', 'honor.kegiatanManmit', 'kontrak', 'bast'])->toArray();

        ApiAuditService::record(
            request: $request,
            action: 'UPDATE_ALOKASI',
            targetModel: AlokasiHonor::class,
            targetId: $alokasi->id,
            stateBefore: $stateBefore,
            stateAfter: $stateAfter,
            statusCode: 200,
            isReversible: true
        );

        return response()->json([
            'status' => 'success',
            'message' => "Alokasi honor #{$alokasi->id} berhasil diperbarui.",
            'data' => new AlokasiHonorResource($alokasi->fresh(['mitra', 'honor.kegiatanManmit', 'kontrak', 'bast'])),
        ]);
    }
}
