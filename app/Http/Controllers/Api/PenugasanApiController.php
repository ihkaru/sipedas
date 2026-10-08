<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\PenugasanApiResource;
use App\Models\Penugasan;
use App\Services\ApiAuditService;
use App\Services\SuratTugas\SuratTugasService;
use App\Supports\Constants;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class PenugasanApiController extends Controller
{
    protected SuratTugasService $service;

    public function __construct(SuratTugasService $service)
    {
        $this->service = $service;
    }

    /**
     * Tampilkan daftar Penugasan (Surat Tugas & SPD).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Penugasan::with([
            'riwayatPengajuan',
            'tujuanSuratTugas',
            'suratTugas',
            'suratPerjadin',
            'pegawai',
            'mitra',
            'kegiatan',
            'pengaju',
            'plh'
        ]);

        // 1. Search (Keyword)
        $search = $request->input('q') ?? $request->input('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nip', 'like', "%{$search}%")
                  ->orWhere('id_sobat', 'like', "%{$search}%")
                  ->orWhere('kegiatan_id', 'like', "%{$search}%")
                  ->orWhere('nama_tempat_tujuan', 'like', "%{$search}%")
                  ->orWhere('grup_id', 'like', "%{$search}%")
                  ->orWhereHas('pegawai', function ($p) use ($search) {
                      $p->where('nama', 'like', "%{$search}%");
                  })
                  ->orWhereHas('mitra', function ($m) use ($search) {
                      $m->where('nama_1', 'like', "%{$search}%");
                  })
                  ->orWhereHas('kegiatan', function ($k) use ($search) {
                      $k->where('nama', 'like', "%{$search}%");
                  })
                  ->orWhereHas('suratTugas', function ($st) use ($search) {
                      $cleanNum = ltrim(preg_replace('/[^0-9]/', '', $search), '0');
                      $st->where('nomor', 'like', "%{$search}%")
                         ->orWhere('sub_nomor', 'like', "%{$search}%");
                      if (!empty($cleanNum)) {
                          $st->orWhere('nomor', $cleanNum);
                      }
                  })
                  ->orWhereHas('suratPerjadin', function ($spd) use ($search) {
                      $cleanNum = ltrim(preg_replace('/[^0-9]/', '', $search), '0');
                      $spd->where('nomor', 'like', "%{$search}%")
                          ->orWhere('sub_nomor', 'like', "%{$search}%");
                      if (!empty($cleanNum)) {
                          $spd->orWhere('nomor', $cleanNum);
                      }
                  });
            });
        }

        // 2. Filter Status
        if ($request->filled('status')) {
            $statusInput = strtoupper(trim((string) $request->input('status')));
            $statusMap = [
                'DIKIRIM' => Constants::STATUS_PENGAJUAN_DIKIRIM,
                'STATUS_PENGAJUAN_DIKIRIM' => Constants::STATUS_PENGAJUAN_DIKIRIM,
                'DISETUJUI' => Constants::STATUS_PENGAJUAN_DISETUJUI,
                'STATUS_PENGAJUAN_DISETUJUI' => Constants::STATUS_PENGAJUAN_DISETUJUI,
                'PERLU_REVISI' => Constants::STATUS_PENGAJUAN_PERLU_REVISI,
                'REVISI' => Constants::STATUS_PENGAJUAN_PERLU_REVISI,
                'STATUS_PENGAJUAN_PERLU_REVISI' => Constants::STATUS_PENGAJUAN_PERLU_REVISI,
                'DICETAK' => Constants::STATUS_PENGAJUAN_DICETAK,
                'STATUS_PENGAJUAN_DICETAK' => Constants::STATUS_PENGAJUAN_DICETAK,
                'DIKUMPULKAN' => Constants::STATUS_PENGAJUAN_DIKUMPULKAN,
                'STATUS_PENGAJUAN_DIKUMPULKAN' => Constants::STATUS_PENGAJUAN_DIKUMPULKAN,
                'DICAIRKAN' => Constants::STATUS_PENGAJUAN_DICAIRKAN,
                'STATUS_PENGAJUAN_DICAIRKAN' => Constants::STATUS_PENGAJUAN_DICAIRKAN,
                'DITOLAK' => Constants::STATUS_PENGAJUAN_DITOLAK,
                'STATUS_PENGAJUAN_DITOLAK' => Constants::STATUS_PENGAJUAN_DITOLAK,
                'DIBATALKAN' => Constants::STATUS_PENGAJUAN_DIBATALKAN,
                'STATUS_PENGAJUAN_DIBATALKAN' => Constants::STATUS_PENGAJUAN_DIBATALKAN,
            ];
            $targetStatus = $statusMap[$statusInput] ?? $request->input('status');
            $query->whereHas('riwayatPengajuan', fn($r) => $r->where('status', $targetStatus));
        }

        // 3. Filter Jenis Surat Tugas
        if ($request->filled('jenis_surat_tugas')) {
            $query->where('jenis_surat_tugas', $request->input('jenis_surat_tugas'));
        }

        // 4. Filter Kegiatan ID
        if ($request->filled('kegiatan_id')) {
            $query->where('kegiatan_id', $request->input('kegiatan_id'));
        }

        // 5. Filter NIP Pegawai / ID Sobat Mitra
        if ($request->filled('nip')) {
            $query->where('nip', $request->input('nip'));
        }
        if ($request->filled('id_sobat')) {
            $query->where('id_sobat', $request->input('id_sobat'));
        }
        if ($request->filled('grup_id')) {
            $query->where('grup_id', $request->input('grup_id'));
        }

        // 6. Filter Tahun & Bulan
        if ($request->filled('tahun')) {
            $query->whereYear('tgl_mulai_tugas', $request->input('tahun'));
        }
        if ($request->filled('bulan')) {
            $query->whereMonth('tgl_mulai_tugas', $request->input('bulan'));
        }

        // 7. Sorting
        $allowedSorts = ['id', 'created_at', 'tgl_mulai_tugas', 'tgl_akhir_tugas', 'tgl_pengajuan_tugas'];
        $sortBy = in_array($request->input('sort_by'), $allowedSorts) ? $request->input('sort_by') : 'id';
        $sortOrder = strtolower($request->input('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortBy, $sortOrder);

        // 8. Pagination
        $perPage = min(max((int) ($request->input('per_page') ?? $request->input('limit') ?? 20), 1), 100);
        $paginated = $query->paginate($perPage);

        // 9. Compact Mode (Token-Dense Agent-Native Standard)
        $isCompact = $request->boolean('compact', false) || $request->boolean('summary', false);
        if ($isCompact) {
            $data = collect($paginated->items())->map(function (Penugasan $p) {
                $statusKey = $p->riwayatPengajuan?->status ?? Constants::STATUS_PENGAJUAN_DIKIRIM;
                $isPegawai = !empty($p->nip);
                return [
                    'id' => $p->id,
                    'grup_id' => $p->grup_id,
                    'personil_id' => $isPegawai ? $p->nip : $p->id_sobat,
                    'personil_nama' => $isPegawai ? ($p->pegawai?->nama ?? $p->nip) : ($p->mitra?->nama_1 ?? $p->id_sobat),
                    'kegiatan_id' => $p->kegiatan_id,
                    'kegiatan_nama' => $p->kegiatan?->nama,
                    'jenis_surat' => $p->jenis_surat_tugas,
                    'status' => Constants::STATUS_PENGAJUAN_OPTIONS[$statusKey] ?? $statusKey,
                    'tgl_mulai' => $p->tgl_mulai_tugas ? substr((string) $p->tgl_mulai_tugas, 0, 10) : null,
                    'tgl_akhir' => $p->tgl_akhir_tugas ? substr((string) $p->tgl_akhir_tugas, 0, 10) : null,
                    'lokasi' => $p->nama_tempat_tujuan ?? $p->tujuan_penugasan ?? '-',
                    'no_st' => $p->suratTugas?->nomor_surat_tugas,
                    'no_spd' => $p->suratPerjadin?->nomor_surat_perjadin,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'total' => $paginated->total(),
                    'per_page' => $paginated->perPage(),
                    'current_page' => $paginated->currentPage(),
                    'last_page' => $paginated->lastPage(),
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => PenugasanApiResource::collection($paginated),
            'pagination' => [
                'total' => $paginated->total(),
                'per_page' => $paginated->perPage(),
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
            ],
        ]);
    }

    /**
     * Tampilkan detail Penugasan (Surat Tugas & SPD).
     */
    public function show(int $id): JsonResponse
    {
        $penugasan = Penugasan::with([
            'riwayatPengajuan',
            'tujuanSuratTugas',
            'suratTugas',
            'suratPerjadin',
            'pegawai',
            'mitra',
            'kegiatan',
            'pengaju',
            'plh'
        ])->find($id);

        if (!$penugasan) {
            return response()->json([
                'success' => false,
                'message' => "Penugasan dengan ID #{$id} tidak ditemukan.",
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new PenugasanApiResource($penugasan),
        ]);
    }

    /**
     * Pre-flight Dry-Run Simulation Check (Agent-Native).
     * Tidak memutasi database, mengembalikan analisis kelayakan dan remediasi.
     */
    public function check(Request $request): JsonResponse
    {
        $result = $this->service->check($request->all());

        return response()->json([
            'success' => $result['eligible'],
            'data' => $result,
        ], $result['eligible'] ? 200 : 422);
    }

    /**
     * Buat penugasan baru (Tunggal atau Tim) secara atomik.
     * Mendukung Idempotency-Key header untuk mencegah duplikasi pengajuan.
     */
    public function store(Request $request): JsonResponse
    {
        // 1. Idempotency Key Handling
        $idempotencyKey = $request->header('Idempotency-Key') ?? $request->header('X-Idempotency-Key');
        if ($idempotencyKey) {
            $cacheKey = "idempotency_penugasan_{$idempotencyKey}";
            $cached = Cache::get($cacheKey);
            if ($cached) {
                return response()->json($cached, 200);
            }
        }

        try {
            $created = $this->service->create($request->all(), auth()?->user()?->pegawai?->nip);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi pembuatan surat tugas gagal.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat penugasan: ' . $e->getMessage(),
            ], 500);
        }

        $createdIds = collect($created)->pluck('id')->toArray();
        $grupId = !empty($created) ? $created[0]->grup_id : null;

        // Catat Audit Log Reversibel
        $auditLog = ApiAuditService::record(
            $request,
            'CREATE_PENUGASAN',
            Penugasan::class,
            $createdIds[0] ?? null,
            null,
            [
                'created_ids' => $createdIds,
                'grup_id' => $grupId,
                'count' => count($createdIds),
            ],
            201,
            true
        );

        $responsePayload = [
            'success' => true,
            'message' => 'Surat tugas dan riwayat pengajuan berhasil diterbitkan.',
            'grup_id' => $grupId,
            'total_created' => count($created),
            'audit_log_id' => $auditLog->id,
            'data' => PenugasanApiResource::collection(collect($created)),
        ];

        if ($idempotencyKey) {
            Cache::put($cacheKey, $responsePayload, now()->addMinutes(15));
        }

        return response()->json($responsePayload, 201);
    }

    /**
     * Perbarui data penugasan (hanya untuk pengajuan yang belum disetujui / perlu perbaikan).
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $penugasan = Penugasan::with(['riwayatPengajuan', 'tujuanSuratTugas'])->find($id);
        if (!$penugasan) {
            return response()->json([
                'success' => false,
                'message' => "Penugasan dengan ID #{$id} tidak ditemukan.",
            ], 404);
        }

        $stateBefore = $penugasan->toArray();

        try {
            $updated = $this->service->update($penugasan, $request->all());
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi pembaruan penugasan gagal.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui penugasan: ' . $e->getMessage(),
            ], 400);
        }

        $auditLog = ApiAuditService::record(
            $request,
            'UPDATE_PENUGASAN',
            Penugasan::class,
            $penugasan->id,
            $stateBefore,
            $updated->toArray(),
            200,
            true
        );

        return response()->json([
            'success' => true,
            'message' => "Penugasan #{$penugasan->id} berhasil diperbarui.",
            'audit_log_id' => $auditLog->id,
            'data' => new PenugasanApiResource($updated),
        ]);
    }

    /**
     * Batalkan pengajuan penugasan.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $penugasan = Penugasan::with(['riwayatPengajuan', 'tujuanSuratTugas'])->find($id);
        if (!$penugasan) {
            return response()->json([
                'success' => false,
                'message' => "Penugasan dengan ID #{$id} tidak ditemukan.",
            ], 404);
        }

        $stateBefore = [
            'penugasan' => $penugasan->getAttributes(),
            'riwayat' => $penugasan->riwayatPengajuan?->getAttributes(),
            'tujuan' => $penugasan->tujuanSuratTugas->map(fn($t) => $t->getAttributes())->toArray(),
        ];

        try {
            $this->service->batalkan($penugasan, false);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan penugasan: ' . $e->getMessage(),
            ], 400);
        }

        $auditLog = ApiAuditService::record(
            $request,
            'BATALKAN_PENUGASAN',
            Penugasan::class,
            $id,
            $stateBefore,
            null,
            200,
            true
        );

        return response()->json([
            'success' => true,
            'message' => "Penugasan #{$id} berhasil dibatalkan.",
            'audit_log_id' => $auditLog->id,
        ]);
    }

    /**
     * Transisi Status Penugasan (setujui, tolak, revisi, ajukan_revisi, cetak, kumpulkan, batalkan_pengumpulan, cairkan).
     */
    public function action(Request $request, int $id): JsonResponse
    {
        $penugasan = Penugasan::with(['riwayatPengajuan'])->find($id);
        if (!$penugasan) {
            return response()->json([
                'success' => false,
                'message' => "Penugasan dengan ID #{$id} tidak ditemukan.",
            ], 404);
        }

        $action = $request->input('action');
        if (empty($action)) {
            return response()->json([
                'success' => false,
                'message' => "Parameter 'action' wajib disertakan. Pilihan valid: setujui, tolak, revisi, ajukan_revisi, cetak, kumpulkan, batalkan_pengumpulan, cairkan.",
            ], 422);
        }

        $stateBefore = [
            'status' => $penugasan->riwayatPengajuan?->status,
            'last_status_timestamp' => $penugasan->riwayatPengajuan?->last_status_timestamp,
            'surat_tugas_id' => $penugasan->surat_tugas_id,
            'surat_perjadin_id' => $penugasan->surat_perjadin_id,
        ];

        try {
            $transitioned = $this->service->transition($penugasan, $action, $request->all(), false);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => "Aksi transisi '{$action}' gagal dieksekusi: " . $e->getMessage(),
            ], 400);
        }

        $auditLog = ApiAuditService::record(
            $request,
            'TRANSITION_PENUGASAN',
            Penugasan::class,
            $penugasan->id,
            $stateBefore,
            [
                'action' => $action,
                'status' => $transitioned->riwayatPengajuan?->status,
                'surat_tugas_id' => $transitioned->surat_tugas_id,
                'surat_perjadin_id' => $transitioned->surat_perjadin_id,
            ],
            200,
            true
        );

        return response()->json([
            'success' => true,
            'message' => "Aksi '{$action}' berhasil diterapkan pada penugasan #{$penugasan->id}.",
            'audit_log_id' => $auditLog->id,
            'data' => new PenugasanApiResource($transitioned),
        ]);
    }
}
