<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use App\Services\ApiAuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PegawaiApiController extends Controller
{
    /**
     * Daftar Pegawai dengan pencarian multi-kolom, filter jabatan/unit kerja, dan mode compact.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Pegawai::with('atasanLangsung');

        // 1. Keyword search (Nama, NIP, NIP9, Jabatan, Email, No WA, Unit Kerja, Panggilan)
        $search = $request->input('q') ?? $request->input('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('nip9', 'like', "%{$search}%")
                  ->orWhere('panggilan', 'like', "%{$search}%")
                  ->orWhere('jabatan', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nomor_wa', 'like', "%{$search}%")
                  ->orWhere('unit_kerja', 'like', "%{$search}%");
            });
        }

        // 2. Filter Unit Kerja
        if ($request->filled('unit_kerja')) {
            $query->where('unit_kerja', 'like', '%' . $request->input('unit_kerja') . '%');
        }

        // 3. Filter Jabatan
        if ($request->filled('jabatan')) {
            $query->where('jabatan', 'like', '%' . $request->input('jabatan') . '%');
        }

        // 4. Filter Golongan
        if ($request->filled('golongan')) {
            $query->where('golongan', $request->input('golongan'));
        }

        // 5. Filter Pegawai Magang vs Organik
        if ($request->has('is_magang')) {
            $isMagang = $request->boolean('is_magang');
            if ($isMagang) {
                $query->where(function ($q) {
                    $q->whereIn('pangkat', ['-', ''])->orWhereNull('pangkat');
                })->where(function ($q) {
                    $q->whereIn('golongan', ['-', ''])->orWhereNull('golongan');
                });
            } else {
                $query->where(function ($q) {
                    $q->whereNotIn('pangkat', ['-', ''])->whereNotNull('pangkat');
                })->where(function ($q) {
                    $q->whereNotIn('golongan', ['-', ''])->whereNotNull('golongan');
                });
            }
        }

        // 6. Sorting
        $allowedSorts = ['nama', 'nip', 'nip9', 'jabatan', 'golongan', 'unit_kerja', 'created_at'];
        $sortBy = in_array($request->input('sort_by'), $allowedSorts) ? $request->input('sort_by') : 'nama';
        $sortOrder = strtolower($request->input('sort_order', 'asc')) === 'desc' ? 'desc' : 'asc';
        $query->orderBy($sortBy, $sortOrder);

        // 7. Pagination
        $perPage = min(max((int)($request->input('per_page') ?? $request->input('limit') ?? 20), 1), 100);
        $paginated = $query->paginate($perPage);

        // 8. Compact / Token-Dense Mode (~80% hemat token)
        $isCompact = $request->boolean('compact', false) || $request->boolean('summary', false);

        if ($isCompact) {
            $items = collect($paginated->items())->map(fn($p) => [
                'nip' => $p->nip,
                'nip9' => $p->nip9,
                'nama' => $p->nama,
                'panggilan' => $p->panggilan,
                'jabatan' => $p->jabatan,
                'golongan' => $p->golongan,
                'unit_kerja' => $p->unit_kerja,
                'nomor_wa' => $p->nomor_wa,
                'email' => $p->email,
            ]);
        } else {
            $items = collect($paginated->items())->map(fn($p) => [
                'nip' => $p->nip,
                'nip9' => $p->nip9,
                'nama' => $p->nama,
                'panggilan' => $p->panggilan,
                'golongan' => $p->golongan,
                'pangkat' => $p->pangkat,
                'jabatan' => $p->jabatan,
                'email' => $p->email,
                'unit_kerja' => $p->unit_kerja,
                'nomor_wa' => $p->nomor_wa,
                'atasan_langsung_id' => $p->atasan_langsung_id,
                'atasan_langsung' => $p->atasanLangsung ? [
                    'nip' => $p->atasanLangsung->nip,
                    'nama' => $p->atasanLangsung->nama,
                    'jabatan' => $p->atasanLangsung->jabatan,
                ] : null,
                'is_magang' => $p->is_magang,
                'created_at' => $p->created_at,
                'updated_at' => $p->updated_at,
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
     * Detail satu Pegawai berdasarkan NIP atau NIP9.
     */
    public function show(string $nip): JsonResponse
    {
        $pegawai = $this->findPegawai($nip);

        if (!$pegawai) {
            return response()->json([
                'status' => 'error',
                'message' => "Pegawai dengan identifier NIP/NIP9 '{$nip}' tidak ditemukan.",
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'nip' => $pegawai->nip,
                'nip9' => $pegawai->nip9,
                'nama' => $pegawai->nama,
                'panggilan' => $pegawai->panggilan,
                'golongan' => $pegawai->golongan,
                'pangkat' => $pegawai->pangkat,
                'jabatan' => $pegawai->jabatan,
                'email' => $pegawai->email,
                'unit_kerja' => $pegawai->unit_kerja,
                'nomor_wa' => $pegawai->nomor_wa,
                'atasan_langsung_id' => $pegawai->atasan_langsung_id,
                'atasan_langsung' => $pegawai->atasanLangsung ? [
                    'nip' => $pegawai->atasanLangsung->nip,
                    'nama' => $pegawai->atasanLangsung->nama,
                    'jabatan' => $pegawai->atasanLangsung->jabatan,
                ] : null,
                'is_magang' => $pegawai->is_magang,
                'created_at' => $pegawai->created_at,
                'updated_at' => $pegawai->updated_at,
            ],
        ]);
    }

    /**
     * Tambah data Pegawai baru.
     * Tercatat di ApiAuditLog dan 100% reversible (dapat di-rollback / dihapus otomatis).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'nip' => ['required', 'string', 'max:255', 'unique:pegawais,nip'],
            'nip9' => ['required', 'string', 'max:255', 'unique:pegawais,nip9'],
            'panggilan' => ['required', 'string', 'max:255'],
            'golongan' => ['required', 'string', 'max:255'],
            'pangkat' => ['required', 'string', 'max:255'],
            'jabatan' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:pegawais,email'],
            'unit_kerja' => ['required', 'string', 'max:255'],
            'nomor_wa' => ['nullable', 'string', 'max:255'],
            'atasan_langsung_id' => ['nullable', 'string', 'exists:pegawais,nip'],
        ]);

        if (!empty($validated['nomor_wa'])) {
            $validated['nomor_wa'] = $this->normalizeWhatsappNumber($validated['nomor_wa']);
        }

        $pegawai = Pegawai::create($validated);

        // Catat Audit Log
        ApiAuditService::record(
            request: $request,
            action: 'CREATE_PEGAWAI',
            targetModel: Pegawai::class,
            targetId: null,
            stateBefore: null,
            stateAfter: $pegawai->toArray(),
            statusCode: 201,
            isReversible: true,
        );

        return response()->json([
            'status' => 'success',
            'message' => "Data Pegawai '{$pegawai->nama}' (NIP: {$pegawai->nip}) berhasil ditambahkan.",
            'data' => $pegawai,
        ], 201);
    }

    /**
     * Update data Pegawai (nama, panggilan, golongan, pangkat, jabatan, email, unit_kerja, nomor_wa, atasan_langsung_id).
     * Dapat dipanggil via PATCH, PUT, atau POST /api/v1/pegawais/{nip}.
     * Tercatat di ApiAuditLog dan 100% reversible (dapat di-rollback ke data semula).
     */
    public function update(string $nip, Request $request): JsonResponse
    {
        $pegawai = $this->findPegawai($nip);

        if (!$pegawai) {
            return response()->json([
                'status' => 'error',
                'message' => "Pegawai dengan identifier NIP/NIP9 '{$nip}' tidak ditemukan.",
            ], 404);
        }

        $validated = $request->validate([
            'nama' => ['nullable', 'string', 'max:255'],
            'panggilan' => ['nullable', 'string', 'max:255'],
            'golongan' => ['nullable', 'string', 'max:255'],
            'pangkat' => ['nullable', 'string', 'max:255'],
            'jabatan' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('pegawais', 'email')->ignore($pegawai->nip, 'nip')],
            'unit_kerja' => ['nullable', 'string', 'max:255'],
            'nomor_wa' => ['nullable', 'string', 'max:255'],
            'atasan_langsung_id' => ['nullable', 'string', 'exists:pegawais,nip'],
        ]);

        $stateBefore = [
            'nip' => $pegawai->nip,
            'nama' => $pegawai->nama,
            'panggilan' => $pegawai->panggilan,
            'golongan' => $pegawai->golongan,
            'pangkat' => $pegawai->pangkat,
            'jabatan' => $pegawai->jabatan,
            'email' => $pegawai->email,
            'unit_kerja' => $pegawai->unit_kerja,
            'nomor_wa' => $pegawai->nomor_wa,
            'atasan_langsung_id' => $pegawai->atasan_langsung_id,
        ];

        $updates = [];
        foreach (['nama', 'panggilan', 'golongan', 'pangkat', 'jabatan', 'email', 'unit_kerja', 'atasan_langsung_id'] as $field) {
            if ($request->has($field)) {
                $updates[$field] = $request->input($field);
            }
        }

        if ($request->has('nomor_wa')) {
            $rawWa = $request->input('nomor_wa');
            $updates['nomor_wa'] = $rawWa !== null ? $this->normalizeWhatsappNumber($rawWa) : null;
        }

        $pegawai->update($updates);

        $stateAfter = array_merge($stateBefore, $updates);

        // Catat Audit Log
        ApiAuditService::record(
            request: $request,
            action: 'UPDATE_PEGAWAI',
            targetModel: Pegawai::class,
            targetId: null,
            stateBefore: $stateBefore,
            stateAfter: $stateAfter,
            statusCode: 200,
            isReversible: true,
        );

        return response()->json([
            'status' => 'success',
            'message' => "Data Pegawai '{$pegawai->nama}' (NIP: {$pegawai->nip}) berhasil diperbarui.",
            'data' => [
                'nip' => $pegawai->nip,
                'nama' => $pegawai->nama,
                'panggilan' => $pegawai->panggilan,
                'golongan' => $pegawai->golongan,
                'pangkat' => $pegawai->pangkat,
                'jabatan' => $pegawai->jabatan,
                'email' => $pegawai->email,
                'unit_kerja' => $pegawai->unit_kerja,
                'nomor_wa' => $pegawai->nomor_wa,
                'atasan_langsung_id' => $pegawai->atasan_langsung_id,
                'updated_fields' => array_keys($updates),
            ],
        ]);
    }

    /**
     * Hapus Pegawai dari sistem.
     * Terintegrasi dengan ApiAuditLog dan 100% reversible (dapat di-rollback).
     */
    public function destroy(string $nip, Request $request): JsonResponse
    {
        $pegawai = $this->findPegawai($nip);

        if (!$pegawai) {
            return response()->json([
                'status' => 'error',
                'message' => "Pegawai dengan identifier '{$nip}' tidak ditemukan.",
            ], 404);
        }

        $attributes = $pegawai->getAttributes();
        $pegawai->delete();

        ApiAuditService::record(
            request: $request,
            action: 'DELETE_PEGAWAI',
            targetModel: Pegawai::class,
            targetId: null,
            stateBefore: $attributes,
            stateAfter: null,
            statusCode: 200,
            isReversible: true,
        );

        return response()->json([
            'status' => 'success',
            'message' => "Pegawai '{$attributes['nama']}' (NIP: {$attributes['nip']}) berhasil dihapus dari sistem.",
            'data' => [
                'deleted_nip' => $attributes['nip'],
            ],
        ]);
    }

    /**
     * Cari Pegawai berdasarkan NIP atau NIP9.
     */
    private function findPegawai(string $identifier): ?Pegawai
    {
        return Pegawai::with('atasanLangsung')
            ->where('nip', $identifier)
            ->orWhere('nip9', $identifier)
            ->first();
    }

    /**
     * Normalisasi nomor WhatsApp ke standar format 628xxx.
     */
    private function normalizeWhatsappNumber(string $raw): ?string
    {
        $digits = preg_replace('/[^0-9]/', '', $raw);
        if (empty($digits)) {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62' . $digits;
        }

        return $digits;
    }
}
