<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Honor;
use App\Models\KegiatanManmit;
use App\Services\ApiAuditService;
use App\Services\HonorTanggalService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HonorApiController extends Controller
{
    /**
     * Daftar Master Honor dengan search, filter kegiatan, tahun/bulan, dan mode compact.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Honor::with('kegiatanManmit');

        // 1. Keyword search (ID, jabatan, jenis honor, nama kegiatan, atau ID kegiatan)
        $search = $request->input('q') ?? $request->input('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('jabatan', 'like', "%{$search}%")
                  ->orWhere('jenis_honor', 'like', "%{$search}%")
                  ->orWhere('kegiatan_manmit_id', 'like', "%{$search}%")
                  ->orWhereHas('kegiatanManmit', function ($km) use ($search) {
                      $km->where('nama', 'like', "%{$search}%");
                  });
            });
        }

        // 2. Filter Kegiatan ID
        $kegiatanId = $request->input('kegiatan_id') ?? $request->input('kegiatan_manmit_id');
        if ($kegiatanId) {
            $query->where('kegiatan_manmit_id', $kegiatanId);
        }

        // 3. Filter Jabatan
        if ($request->filled('jabatan')) {
            $query->where('jabatan', $request->input('jabatan'));
        }

        // 4. Filter Tahun (dari tanggal_akhir_kegiatan)
        if ($request->filled('tahun')) {
            $tahun = (int)$request->input('tahun');
            $query->whereYear('tanggal_akhir_kegiatan', $tahun);
        }

        // 5. Filter Bulan (dari tanggal_akhir_kegiatan)
        if ($request->filled('bulan')) {
            $bulan = (int)$request->input('bulan');
            $query->whereMonth('tanggal_akhir_kegiatan', $bulan);
        }

        // 6. Sorting
        $allowedSorts = ['id', 'tanggal_akhir_kegiatan', 'harga_per_satuan', 'jabatan', 'created_at'];
        $sortBy = in_array($request->input('sort_by'), $allowedSorts) ? $request->input('sort_by') : 'tanggal_akhir_kegiatan';
        $sortOrder = strtolower($request->input('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortBy, $sortOrder);

        // 7. Pagination
        $perPage = min(max((int)($request->input('per_page') ?? $request->input('limit') ?? 20), 1), 100);
        $paginated = $query->paginate($perPage);

        // 8. Compact / Token-Dense Mode (~80% hemat token)
        $isCompact = $request->boolean('compact', false) || $request->boolean('summary', false);

        if ($isCompact) {
            $items = collect($paginated->items())->map(fn($h) => [
                'id' => $h->id,
                'kegiatan_id' => $h->kegiatan_manmit_id,
                'kegiatan_nama' => $h->kegiatanManmit?->nama,
                'jabatan' => $h->jabatan,
                'jenis_honor' => $h->jenis_honor,
                'harga' => (float)$h->harga_per_satuan,
                'satuan' => $h->satuan_honor,
                'tgl_akhir' => $h->tanggal_akhir_kegiatan?->toDateString(),
                'tgl_bayar_maks' => $h->tanggal_pembayaran_maksimal?->toDateString(),
            ]);
        } else {
            $items = collect($paginated->items())->map(fn($h) => [
                'id' => $h->id,
                'kegiatan_manmit_id' => $h->kegiatan_manmit_id,
                'kegiatan_manmit' => $h->kegiatanManmit ? [
                    'id' => $h->kegiatanManmit->id,
                    'nama' => $h->kegiatanManmit->nama,
                    'jenis_kegiatan' => $h->kegiatanManmit->jenis_kegiatan,
                    'tgl_mulai_pelaksanaan' => $h->kegiatanManmit->tgl_mulai_pelaksanaan,
                    'tgl_akhir_pelaksanaan' => $h->kegiatanManmit->tgl_akhir_pelaksanaan,
                ] : null,
                'jabatan' => $h->jabatan,
                'jenis_honor' => $h->jenis_honor,
                'satuan_honor' => $h->satuan_honor,
                'harga_per_satuan' => (float)$h->harga_per_satuan,
                'tanggal_akhir_kegiatan' => $h->tanggal_akhir_kegiatan?->toDateString(),
                'tanggal_pembayaran_maksimal' => $h->tanggal_pembayaran_maksimal?->toDateString(),
                'created_at' => $h->created_at,
                'updated_at' => $h->updated_at,
            ]);
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
     * Detail satu entitas Honor.
     */
    public function show(string $id): JsonResponse
    {
        $honor = Honor::with('kegiatanManmit')->withCount('alokasiHonors')->find($id);

        if (!$honor) {
            return response()->json([
                'status' => 'error',
                'message' => "Honor dengan ID '{$id}' tidak ditemukan.",
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $honor->id,
                'kegiatan_manmit_id' => $honor->kegiatan_manmit_id,
                'kegiatan_manmit' => $honor->kegiatanManmit ? [
                    'id' => $honor->kegiatanManmit->id,
                    'nama' => $honor->kegiatanManmit->nama,
                    'jenis_kegiatan' => $honor->kegiatanManmit->jenis_kegiatan,
                    'tgl_mulai_pelaksanaan' => $honor->kegiatanManmit->tgl_mulai_pelaksanaan,
                    'tgl_akhir_pelaksanaan' => $honor->kegiatanManmit->tgl_akhir_pelaksanaan,
                ] : null,
                'jabatan' => $honor->jabatan,
                'jenis_honor' => $honor->jenis_honor,
                'satuan_honor' => $honor->satuan_honor,
                'harga_per_satuan' => (float)$honor->harga_per_satuan,
                'tanggal_akhir_kegiatan' => $honor->tanggal_akhir_kegiatan?->toDateString(),
                'tanggal_pembayaran_maksimal' => $honor->tanggal_pembayaran_maksimal?->toDateString(),
                'alokasi_honors_count' => $honor->alokasi_honors_count,
                'created_at' => $honor->created_at,
                'updated_at' => $honor->updated_at,
            ],
        ]);
    }

    /**
     * Update master Honor (tanggal_akhir_kegiatan, harga_per_satuan, satuan_honor, dll).
     * Memvalidasi rentang tanggal terhadap kegiatan induk dan memicu propagasi otomatis ke SPK/BAST.
     * Tercatat di ApiAuditLog dan 100% reversible (dapat di-rollback).
     */
    public function update(string $id, Request $request): JsonResponse
    {
        $honor = Honor::with('kegiatanManmit')->find($id);

        if (!$honor) {
            return response()->json([
                'status' => 'error',
                'message' => "Honor dengan ID '{$id}' tidak ditemukan.",
            ], 404);
        }

        $request->validate([
            'tanggal_akhir_kegiatan' => ['nullable', 'date'],
            'harga_per_satuan' => ['nullable', 'numeric', 'min:0'],
            'satuan_honor' => ['nullable', 'string', 'max:100'],
            'jabatan' => ['nullable', 'string', 'max:100'],
            'jenis_honor' => ['nullable', 'string', 'max:100'],
        ]);

        // Validasi kesesuaian tanggal_akhir_kegiatan terhadap rentang KegiatanManmit induk
        if ($request->filled('tanggal_akhir_kegiatan')) {
            $newDate = Carbon::parse($request->input('tanggal_akhir_kegiatan'));
            $kegiatan = $honor->kegiatanManmit;

            if ($kegiatan && $kegiatan->tgl_mulai_pelaksanaan && $kegiatan->tgl_akhir_pelaksanaan) {
                $start = Carbon::parse($kegiatan->tgl_mulai_pelaksanaan);
                $end = Carbon::parse($kegiatan->tgl_akhir_pelaksanaan);

                if ($newDate->lt($start) || $newDate->gt($end)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "Tanggal akhir kegiatan ({$newDate->format('d M Y')}) harus berada dalam rentang pelaksanaan kegiatan utama '{$kegiatan->nama}' ({$start->format('d M Y')} s/d {$end->format('d M Y')}). Silakan perpanjang rentang kegiatan utama terlebih dahulu via PATCH /api/v1/kegiatan-manmit/{$kegiatan->id}.",
                        'data' => [
                            'honor_id' => $honor->id,
                            'kegiatan_id' => $kegiatan->id,
                            'kegiatan_tgl_mulai' => $kegiatan->tgl_mulai_pelaksanaan,
                            'kegiatan_tgl_akhir' => $kegiatan->tgl_akhir_pelaksanaan,
                            'requested_tanggal_akhir_kegiatan' => $newDate->toDateString(),
                        ],
                    ], 422);
                }
            }
        }

        $stateBefore = [
            'id' => $honor->id,
            'kegiatan_manmit_id' => $honor->kegiatan_manmit_id,
            'jabatan' => $honor->jabatan,
            'jenis_honor' => $honor->jenis_honor,
            'satuan_honor' => $honor->satuan_honor,
            'harga_per_satuan' => (float)$honor->harga_per_satuan,
            'tanggal_akhir_kegiatan' => $honor->tanggal_akhir_kegiatan?->toDateString(),
            'tanggal_pembayaran_maksimal' => $honor->tanggal_pembayaran_maksimal?->toDateString(),
        ];

        $updates = [];
        if ($request->has('tanggal_akhir_kegiatan')) {
            $updates['tanggal_akhir_kegiatan'] = $request->input('tanggal_akhir_kegiatan');
        }
        if ($request->has('harga_per_satuan')) {
            $updates['harga_per_satuan'] = $request->input('harga_per_satuan');
        }
        if ($request->has('satuan_honor')) {
            $updates['satuan_honor'] = $request->input('satuan_honor');
        }
        if ($request->has('jabatan')) {
            $updates['jabatan'] = $request->input('jabatan');
        }
        if ($request->has('jenis_honor')) {
            $updates['jenis_honor'] = strtoupper($request->input('jenis_honor'));
        }

        // Lakukan update (model event & observer akan otomatis trigger propagasi tanggal jika tanggal_akhir_kegiatan berubah)
        $honor->update($updates);

        $fresh = $honor->fresh();

        $stateAfter = [
            'id' => $fresh->id,
            'kegiatan_manmit_id' => $fresh->kegiatan_manmit_id,
            'jabatan' => $fresh->jabatan,
            'jenis_honor' => $fresh->jenis_honor,
            'satuan_honor' => $fresh->satuan_honor,
            'harga_per_satuan' => (float)$fresh->harga_per_satuan,
            'tanggal_akhir_kegiatan' => $fresh->tanggal_akhir_kegiatan?->toDateString(),
            'tanggal_pembayaran_maksimal' => $fresh->tanggal_pembayaran_maksimal?->toDateString(),
        ];

        // Catat Audit Log
        ApiAuditService::record(
            request: $request,
            action: 'UPDATE_HONOR',
            targetModel: Honor::class,
            targetId: null,
            stateBefore: $stateBefore,
            stateAfter: $stateAfter,
            statusCode: 200,
            isReversible: true,
        );

        $propagatedCount = $fresh->alokasiHonors()->count();

        return response()->json([
            'status' => 'success',
            'message' => "Master Honor '{$id}' berhasil diperbarui.",
            'data' => [
                'id' => $fresh->id,
                'kegiatan_manmit_id' => $fresh->kegiatan_manmit_id,
                'jabatan' => $fresh->jabatan,
                'jenis_honor' => $fresh->jenis_honor,
                'harga_per_satuan' => (float)$fresh->harga_per_satuan,
                'satuan_honor' => $fresh->satuan_honor,
                'tanggal_akhir_kegiatan' => $fresh->tanggal_akhir_kegiatan?->toDateString(),
                'tanggal_pembayaran_maksimal' => $fresh->tanggal_pembayaran_maksimal?->toDateString(),
                'updated_fields' => array_keys($updates),
                'propagated_alokasi_count' => $propagatedCount,
                'note' => 'Perubahan tanggal_akhir_kegiatan telah otomatis dipropagasikan ke seluruh alokasi honor dan nomor surat SPK/BAST terkait.',
            ],
        ]);
    }

    /**
     * Daftarkan Master Pos Honor baru di bawah suatu Kegiatan Manmit.
     * Terintegrasi dengan ApiAuditLog dan 100% reversible (dapat di-rollback).
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'kegiatan_manmit_id' => ['required', 'string', 'exists:kegiatan_manmits,id'],
            'jabatan' => ['required', 'string', 'max:100'],
            'jenis_honor' => ['required', 'string', 'max:100'],
            'satuan_honor' => ['required', 'string', 'max:100'],
            'harga_per_satuan' => ['required', 'numeric', 'min:0'],
            'tanggal_akhir_kegiatan' => ['required', 'date'],
            'id' => ['nullable', 'string', 'max:255', 'unique:honors,id'],
        ], [
            'kegiatan_manmit_id.required' => 'Parameter kegiatan_manmit_id wajib disertakan.',
            'kegiatan_manmit_id.exists' => 'Kegiatan Manmit dengan ID tersebut tidak ditemukan.',
            'id.unique' => 'Honor dengan ID tersebut sudah terdaftar di sistem.',
        ]);

        $kegiatan = KegiatanManmit::find($request->input('kegiatan_manmit_id'));
        $targetDate = Carbon::parse($request->input('tanggal_akhir_kegiatan'));

        // Validasi rentang tanggal terhadap kegiatan induk
        if ($kegiatan->tgl_mulai_pelaksanaan && $kegiatan->tgl_akhir_pelaksanaan) {
            $start = Carbon::parse($kegiatan->tgl_mulai_pelaksanaan);
            $end = Carbon::parse($kegiatan->tgl_akhir_pelaksanaan);

            if ($targetDate->lt($start) || $targetDate->gt($end)) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Tanggal akhir kegiatan ({$targetDate->format('d M Y')}) harus berada dalam rentang pelaksanaan kegiatan utama '{$kegiatan->nama}' ({$start->format('d M Y')} s/d {$end->format('d M Y')}). Silakan perpanjang rentang kegiatan utama terlebih dahulu via PATCH /api/v1/kegiatan-manmit/{$kegiatan->id}.",
                    'data' => [
                        'kegiatan_id' => $kegiatan->id,
                        'kegiatan_tgl_mulai' => $kegiatan->tgl_mulai_pelaksanaan,
                        'kegiatan_tgl_akhir' => $kegiatan->tgl_akhir_pelaksanaan,
                        'requested_tanggal_akhir_kegiatan' => $targetDate->toDateString(),
                    ],
                ], 422);
            }
        }

        // Tentukan ID honor
        $customId = $request->input('id');
        $jabatan = trim($request->input('jabatan'));
        $jenisHonor = Str::upper(trim($request->input('jenis_honor')));

        $generatedId = $customId ?: Str::upper($kegiatan->id . '-' . $jabatan . '-' . $jenisHonor);

        if (Honor::where('id', $generatedId)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => "Honor dengan ID '{$generatedId}' sudah terdaftar di sistem. Gunakan kombinasi jabatan/jenis honor lain atau tentukan custom 'id'.",
            ], 422);
        }

        $honor = Honor::create([
            'id' => $generatedId,
            'kegiatan_manmit_id' => $kegiatan->id,
            'jabatan' => $jabatan,
            'jenis_honor' => $jenisHonor,
            'satuan_honor' => $request->input('satuan_honor'),
            'harga_per_satuan' => $request->input('harga_per_satuan'),
            'tanggal_akhir_kegiatan' => $targetDate->toDateString(),
        ]);

        $fresh = $honor->fresh(['kegiatanManmit']);

        ApiAuditService::record(
            request: $request,
            action: 'CREATE_HONOR',
            targetModel: Honor::class,
            targetId: null,
            stateBefore: null,
            stateAfter: [
                'id' => $fresh->id,
                'kegiatan_manmit_id' => $fresh->kegiatan_manmit_id,
                'jabatan' => $fresh->jabatan,
                'jenis_honor' => $fresh->jenis_honor,
                'satuan_honor' => $fresh->satuan_honor,
                'harga_per_satuan' => (float)$fresh->harga_per_satuan,
                'tanggal_akhir_kegiatan' => $fresh->tanggal_akhir_kegiatan?->toDateString(),
                'tanggal_pembayaran_maksimal' => $fresh->tanggal_pembayaran_maksimal?->toDateString(),
            ],
            statusCode: 201,
            isReversible: true,
        );

        return response()->json([
            'status' => 'success',
            'message' => "Master Pos Honor '{$fresh->id}' berhasil didaftarkan di bawah kegiatan '{$kegiatan->nama}'.",
            'data' => [
                'id' => $fresh->id,
                'kegiatan_manmit_id' => $fresh->kegiatan_manmit_id,
                'kegiatan_nama' => $fresh->kegiatanManmit?->nama,
                'jabatan' => $fresh->jabatan,
                'jenis_honor' => $fresh->jenis_honor,
                'satuan_honor' => $fresh->satuan_honor,
                'harga_per_satuan' => (float)$fresh->harga_per_satuan,
                'tanggal_akhir_kegiatan' => $fresh->tanggal_akhir_kegiatan?->toDateString(),
                'tanggal_pembayaran_maksimal' => $fresh->tanggal_pembayaran_maksimal?->toDateString(),
                'created_at' => $fresh->created_at,
            ],
        ], 201);
    }

    /**
     * Hapus Master Pos Honor (hanya jika belum memiliki alokasi mitra).
     * Terintegrasi dengan ApiAuditLog dan 100% reversible (dapat di-rollback).
     */
    public function destroy(string $id, Request $request): JsonResponse
    {
        $honor = Honor::withCount('alokasiHonors')->find($id);

        if (!$honor) {
            return response()->json([
                'status' => 'error',
                'message' => "Honor dengan ID '{$id}' tidak ditemukan.",
            ], 404);
        }

        if ($honor->alokasi_honors_count > 0) {
            return response()->json([
                'status' => 'error',
                'message' => "Tidak dapat menghapus Pos Honor '{$id}' karena sudah memiliki {$honor->alokasi_honors_count} alokasi mitra terkait. Hapus atau batalkan alokasi terlebih dahulu.",
            ], 422);
        }

        $honorAttributes = $honor->getAttributes();
        $honor->delete();

        ApiAuditService::record(
            request: $request,
            action: 'DELETE_HONOR',
            targetModel: Honor::class,
            targetId: null,
            stateBefore: $honorAttributes,
            stateAfter: null,
            statusCode: 200,
            isReversible: true,
        );

        return response()->json([
            'status' => 'success',
            'message' => "Pos Honor '{$id}' berhasil dihapus dari sistem.",
            'data' => [
                'deleted_id' => $id,
            ],
        ]);
    }
}
