<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kemitraan;
use App\Models\Mitra;
use App\Services\ApiAuditService;
use App\Services\HonorService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MitraApiController extends Controller
{
    /**
     * Tampilkan daftar mitra dengan filter, pencarian, dan pagination.
     * Mengarahkan ke implementasi terpusat pada KegiatanManmitApiController.
     */
    public function index(Request $request): JsonResponse
    {
        return app(KegiatanManmitApiController::class)->mitras($request);
    }

    /**
     * Tampilkan profil lengkap satu mitra berdasarkan ID, ID Sobat, atau NIK.
     */
    public function show(string $id, Request $request): JsonResponse
    {
        $mitra = $this->findMitra($id);

        if (!$mitra) {
            return response()->json([
                'status' => 'error',
                'message' => "Mitra dengan identifier '{$id}' tidak ditemukan.",
            ], 404);
        }

        $tahun = (int)$request->input('tahun', now()->year);
        $mitra->load(['kemitraans', 'alokasiHonors.honor.kegiatanManmit']);

        $currentKemitraan = $mitra->kemitraans->firstWhere('tahun', $tahun);

        $data = [
            'id' => $mitra->id,
            'id_sobat' => $mitra->id_sobat,
            'nik' => $mitra->nik,
            'nama' => $mitra->nama_1,
            'nama_1' => $mitra->nama_1,
            'nama_2' => $mitra->nama_2,
            'nomor_wa' => $mitra->nomor_wa,
            'whatsapp_target' => $mitra->whatsapp_target,
            'whatsapp_url' => $mitra->whatsapp_url,
            'no_telp' => $mitra->no_telp,
            'email' => $mitra->email,
            'posisi' => $mitra->posisi ?? $mitra->posisi_daftar,
            'npwp' => $mitra->npwp,
            'jenis_kelamin' => $mitra->jenis_kelamin,
            'agama' => $mitra->agama,
            'status_perkawinan' => $mitra->status_perkawinan,
            'pendidikan' => $mitra->pendidikan,
            'pekerjaan' => $mitra->pekerjaan,
            'deskripsi_pekerjaan_lain' => $mitra->deskripsi_pekerjaan_lain,
            'catatan' => $mitra->catatan,
            'domisili' => [
                'alamat_detail' => $mitra->alamat_detail,
                'kecamatan' => $mitra->kecamatan_domisili,
                'desa' => $mitra->desa_domisili,
                'kabupaten' => $mitra->kabupaten_domisili,
                'alamat_prov' => $mitra->alamat_prov,
                'alamat_kab' => $mitra->alamat_kab,
                'alamat_kec' => $mitra->alamat_kec,
                'alamat_desa' => $mitra->alamat_desa,
            ],
            'kemitraan_terkini' => [
                'tahun' => $tahun,
                'status' => $currentKemitraan?->status ?? 'BELUM_TERDAFTAR',
            ],
            'riwayat_kemitraan' => $mitra->kemitraans->map(fn($k) => [
                'tahun' => $k->tahun,
                'status' => $k->status,
            ]),
            'created_at' => $mitra->created_at,
            'updated_at' => $mitra->updated_at,
        ];

        // Sisa pagu SBML jika diminta
        $targetBulan = $request->filled('bulan') ? (int)$request->input('bulan') : null;
        if ($targetBulan) {
            $start = Carbon::create($tahun, $targetBulan, 1)->startOfMonth();
            $end = Carbon::create($tahun, $targetBulan, 1)->endOfMonth();
            $remaining = HonorService::getMitraRemainingBudget($mitra->id, $start, $end);
            $data['sisa_sbml'] = [
                'bulan' => $targetBulan,
                'tahun' => $tahun,
                'sisa_survei' => $remaining['min_survei'],
                'sisa_sensus' => $remaining['min_sensus'],
            ];
        }

        // Histori alokasi penugasan jika diminta
        if ($request->boolean('with_allocations', false)) {
            $data['alokasi_honors'] = $mitra->alokasiHonors->map(fn($a) => [
                'id' => $a->id,
                'honor_id' => $a->honor_id,
                'kegiatan_id' => $a->honor?->kegiatan_manmit_id,
                'kegiatan_nama' => $a->honor?->kegiatanManmit?->nama,
                'jabatan' => $a->honor?->jabatan,
                'jenis_honor' => $a->honor?->jenis_honor,
                'volume' => (float)$a->alokasi_volume,
                'total_honor' => (float)$a->total_honor,
                'tanggal_mulai' => $a->tanggal_mulai_perjanjian?->format('Y-m-d'),
                'tanggal_akhir' => $a->tanggal_akhir_perjanjian?->format('Y-m-d'),
                'spk_id' => $a->surat_perjanjian_kerja_id,
                'bast_id' => $a->surat_bast_id,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    /**
     * Update informasi terkait mitra (nomor WhatsApp terbaru, no telp, email, alamat, catatan, status kemitraan).
     * Dapat dipanggil via PATCH, PUT, atau POST /api/v1/mitras/{id}.
     * Tercatat di ApiAuditLog dan 100% reversible (dapat di-rollback).
     */
    public function update(string $id, Request $request): JsonResponse
    {
        $mitra = $this->findMitra($id);

        if (!$mitra) {
            return response()->json([
                'status' => 'error',
                'message' => "Mitra dengan identifier '{$id}' tidak ditemukan.",
            ], 404);
        }

        $validated = $request->validate([
            'nomor_wa' => ['nullable', 'string', 'max:30'],
            'no_wa' => ['nullable', 'string', 'max:30'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'no_telp' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'nama' => ['nullable', 'string', 'max:255'],
            'nama_1' => ['nullable', 'string', 'max:255'],
            'alamat_detail' => ['nullable', 'string', 'max:500'],
            'alamat' => ['nullable', 'string', 'max:500'],
            'catatan' => ['nullable', 'string', 'max:1000'],
            'posisi' => ['nullable', 'string', 'max:255'],
            'npwp' => ['nullable', 'string', 'max:50'],
            'status_kemitraan' => ['nullable', 'string', Rule::in(['AKTIF', 'TIDAK_AKTIF', 'BLACKLISTED'])],
            'status' => ['nullable', 'string', Rule::in(['AKTIF', 'TIDAK_AKTIF', 'BLACKLISTED'])],
            'tahun' => ['nullable', 'integer', 'min:2020', 'max:2035'],
        ], [
            'status_kemitraan.in' => 'Status kemitraan harus berupa: AKTIF, TIDAK_AKTIF, atau BLACKLISTED.',
            'status.in' => 'Status kemitraan harus berupa: AKTIF, TIDAK_AKTIF, atau BLACKLISTED.',
            'email.email' => 'Format email yang dimasukkan tidak valid.',
        ]);

        $tahunKemitraan = (int)($request->input('tahun', now()->year));
        $statusKemitraanInput = $request->input('status_kemitraan') ?? $request->input('status');

        // Normalisasi nomor WhatsApp
        $rawWa = $request->input('nomor_wa') ?? $request->input('no_wa') ?? $request->input('whatsapp');
        $normalizedWa = $rawWa !== null ? $this->normalizeWhatsappNumber($rawWa) : null;

        // Siapkan atribut yang akan diupdate pada tabel mitras
        $mitraUpdates = [];
        $stateBefore = [];
        $stateAfter = [];

        // 1. Nomor WA Terbaru
        if ($rawWa !== null) {
            $stateBefore['nomor_wa'] = $mitra->nomor_wa;
            $mitraUpdates['nomor_wa'] = $normalizedWa;
            $stateAfter['nomor_wa'] = $normalizedWa;
        }

        // 2. Nomor Telepon
        if ($request->has('no_telp')) {
            $stateBefore['no_telp'] = $mitra->no_telp;
            $mitraUpdates['no_telp'] = $request->input('no_telp');
            $stateAfter['no_telp'] = $request->input('no_telp');
        }

        // 3. Email
        if ($request->has('email')) {
            $stateBefore['email'] = $mitra->email;
            $mitraUpdates['email'] = $request->input('email');
            $stateAfter['email'] = $request->input('email');
        }

        // 4. Nama
        $namaInput = $request->input('nama') ?? $request->input('nama_1');
        if ($namaInput !== null) {
            $stateBefore['nama_1'] = $mitra->nama_1;
            $mitraUpdates['nama_1'] = $namaInput;
            $stateAfter['nama_1'] = $namaInput;
        }

        // 5. Alamat Detail
        $alamatInput = $request->input('alamat_detail') ?? $request->input('alamat');
        if ($alamatInput !== null) {
            $stateBefore['alamat_detail'] = $mitra->alamat_detail;
            $mitraUpdates['alamat_detail'] = $alamatInput;
            $stateAfter['alamat_detail'] = $alamatInput;
        }

        // 6. Catatan
        if ($request->has('catatan')) {
            $stateBefore['catatan'] = $mitra->catatan;
            $mitraUpdates['catatan'] = $request->input('catatan');
            $stateAfter['catatan'] = $request->input('catatan');
        }

        // 7. Posisi
        if ($request->has('posisi')) {
            $stateBefore['posisi'] = $mitra->posisi;
            $mitraUpdates['posisi'] = $request->input('posisi');
            $stateAfter['posisi'] = $request->input('posisi');
        }

        // 8. NPWP
        if ($request->has('npwp')) {
            $stateBefore['npwp'] = $mitra->npwp;
            $mitraUpdates['npwp'] = $request->input('npwp');
            $stateAfter['npwp'] = $request->input('npwp');
        }

        // 9. Status Kemitraan (pada tabel kemitraans)
        $kemitraanBefore = null;
        if ($statusKemitraanInput !== null) {
            $currentKemitraan = Kemitraan::where('mitra_id', $mitra->id)
                ->where('tahun', $tahunKemitraan)
                ->first();

            $kemitraanBefore = $currentKemitraan?->status ?? 'BELUM_TERDAFTAR';
            $stateBefore['kemitraan_status'] = $kemitraanBefore;
            $stateBefore['kemitraan_tahun'] = $tahunKemitraan;

            $stateAfter['kemitraan_status'] = strtoupper($statusKemitraanInput);
            $stateAfter['kemitraan_tahun'] = $tahunKemitraan;
        }

        // Jika tidak ada data yang diubah
        if (empty($mitraUpdates) && $statusKemitraanInput === null) {
            return response()->json([
                'status' => 'warning',
                'message' => 'Tidak ada field yang diberikan untuk diperbarui.',
                'data' => [
                    'id' => $mitra->id,
                    'nama' => $mitra->nama_1,
                    'nomor_wa' => $mitra->nomor_wa,
                ],
            ]);
        }

        DB::beginTransaction();
        try {
            // Update tabel mitras
            if (!empty($mitraUpdates)) {
                $mitra->update($mitraUpdates);
            }

            // Update atau buat kemitraan
            if ($statusKemitraanInput !== null) {
                Kemitraan::updateOrCreate(
                    [
                        'mitra_id' => $mitra->id,
                        'tahun' => $tahunKemitraan,
                    ],
                    [
                        'status' => strtoupper($statusKemitraanInput),
                    ]
                );
            }

            // Rekam audit log
            ApiAuditService::record(
                request: $request,
                action: 'UPDATE_MITRA',
                targetModel: Mitra::class,
                targetId: $mitra->id,
                stateBefore: $stateBefore,
                stateAfter: $stateAfter,
                statusCode: 200,
                isReversible: true,
            );

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        $freshMitra = $mitra->fresh(['kemitraans']);
        $currentKemitraanFresh = $freshMitra->kemitraans->firstWhere('tahun', $tahunKemitraan);

        return response()->json([
            'status' => 'success',
            'message' => "Informasi mitra '{$freshMitra->nama_1}' berhasil diperbarui.",
            'data' => [
                'id' => $freshMitra->id,
                'id_sobat' => $freshMitra->id_sobat,
                'nik' => $freshMitra->nik,
                'nama' => $freshMitra->nama_1,
                'nomor_wa' => $freshMitra->nomor_wa,
                'whatsapp_target' => $freshMitra->whatsapp_target,
                'whatsapp_url' => $freshMitra->whatsapp_url,
                'no_telp' => $freshMitra->no_telp,
                'email' => $freshMitra->email,
                'posisi' => $freshMitra->posisi,
                'alamat_detail' => $freshMitra->alamat_detail,
                'catatan' => $freshMitra->catatan,
                'kemitraan' => [
                    'tahun' => $tahunKemitraan,
                    'status' => $currentKemitraanFresh?->status ?? 'BELUM_TERDAFTAR',
                ],
                'updated_at' => $freshMitra->updated_at?->toISOString(),
            ],
            'meta' => [
                'updated_fields' => array_keys($stateAfter),
            ],
        ]);
    }

    /**
     * Cari mitra berdasarkan ID (numerik), ID Sobat, atau NIK.
     */
    protected function findMitra(string $identifier): ?Mitra
    {
        $clean = trim($identifier);

        if (is_numeric($clean)) {
            $byPk = Mitra::find((int)$clean);
            if ($byPk) return $byPk;
        }

        return Mitra::where('id_sobat', $clean)
            ->orWhere('nik', $clean)
            ->first();
    }

    /**
     * Normalisasi nomor WhatsApp menjadi format bersih tanpa spasi/simbol,
     * serta mengonversi awalan '08' menjadi '628'.
     */
    protected function normalizeWhatsappNumber(?string $raw): ?string
    {
        if ($raw === null) return null;

        $digits = preg_replace('/[^0-9]/', '', trim($raw));
        if (empty($digits)) return null;

        if (str_starts_with($digits, '08')) {
            $digits = '62' . substr($digits, 1);
        }

        return $digits;
    }

    /**
     * Daftarkan Mitra baru beserta status kemitraan tahunan.
     * Terintegrasi dengan ApiAuditLog dan 100% reversible (dapat di-rollback).
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nama' => ['required_without:nama_1', 'string', 'max:255'],
            'nama_1' => ['required_without:nama', 'string', 'max:255'],
            'nik' => ['required', 'string', 'max:20', 'unique:mitras,nik'],
            'id_sobat' => ['nullable', 'string', 'max:50', 'unique:mitras,id_sobat'],
            'nomor_wa' => ['nullable', 'string', 'max:30'],
            'no_telp' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'posisi' => ['nullable', 'string', 'max:100'],
            'alamat_detail' => ['nullable', 'string'],
            'kecamatan' => ['nullable', 'string', 'max:100'],
            'desa' => ['nullable', 'string', 'max:100'],
            'jenis_kelamin' => ['nullable', 'string', 'max:20'],
            'status_kemitraan' => ['nullable', 'string', 'max:50'],
            'tahun' => ['nullable', 'integer'],
        ]);

        $nama = $request->input('nama_1') ?? $request->input('nama');
        $nomorWa = $this->normalizeWhatsappNumber($request->input('nomor_wa'));
        $tahun = (int)$request->input('tahun', now()->year);
        $statusKemitraan = strtoupper($request->input('status_kemitraan', 'AKTIF'));

        DB::beginTransaction();
        try {
            $mitra = Mitra::create([
                'nama_1' => $nama,
                'nama_2' => $nama,
                'nik' => trim($request->input('nik')),
                'id_sobat' => $request->input('id_sobat') ? trim($request->input('id_sobat')) : null,
                'nomor_wa' => $nomorWa,
                'no_telp' => $request->input('no_telp') ?? $nomorWa,
                'email' => $request->input('email'),
                'posisi' => $request->input('posisi', 'Mitra Pendataan'),
                'alamat_detail' => $request->input('alamat_detail'),
                'kecamatan_domisili' => $request->input('kecamatan'),
                'desa_domisili' => $request->input('desa'),
                'jenis_kelamin' => $request->input('jenis_kelamin'),
            ]);

            Kemitraan::create([
                'mitra_id' => $mitra->id,
                'tahun' => $tahun,
                'status' => $statusKemitraan,
            ]);

            ApiAuditService::record(
                request: $request,
                action: 'CREATE_MITRA',
                targetModel: Mitra::class,
                targetId: $mitra->id,
                stateBefore: null,
                stateAfter: [
                    'id' => $mitra->id,
                    'nama_1' => $mitra->nama_1,
                    'nik' => $mitra->nik,
                    'id_sobat' => $mitra->id_sobat,
                    'nomor_wa' => $mitra->nomor_wa,
                    'status_kemitraan' => $statusKemitraan,
                    'tahun' => $tahun,
                ],
                statusCode: 201,
                isReversible: true,
            );

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return response()->json([
            'status' => 'success',
            'message' => "Mitra '{$mitra->nama_1}' (NIK: {$mitra->nik}) berhasil didaftarkan.",
            'data' => [
                'id' => $mitra->id,
                'nama' => $mitra->nama_1,
                'nik' => $mitra->nik,
                'id_sobat' => $mitra->id_sobat,
                'nomor_wa' => $mitra->nomor_wa,
                'status_kemitraan' => $statusKemitraan,
                'tahun_kemitraan' => $tahun,
            ],
        ], 201);
    }

    /**
     * Hapus Mitra (hanya jika belum memiliki riwayat alokasi honor).
     * Terintegrasi dengan ApiAuditLog dan 100% reversible (dapat di-rollback).
     */
    public function destroy(string $id, Request $request): JsonResponse
    {
        $mitra = $this->findMitra($id);

        if (!$mitra) {
            return response()->json([
                'status' => 'error',
                'message' => "Mitra dengan identifier '{$id}' tidak ditemukan.",
            ], 404);
        }

        if ($mitra->alokasiHonors()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => "Tidak dapat menghapus Mitra '{$mitra->nama_1}' karena sudah memiliki alokasi honor terkait.",
            ], 422);
        }

        DB::beginTransaction();
        try {
            $mitraAttributes = $mitra->getAttributes();
            $kemitraanData = $mitra->kemitraans->map(fn($k) => $k->getAttributes())->toArray();

            $mitra->kemitraans()->delete();
            $mitra->delete();

            ApiAuditService::record(
                request: $request,
                action: 'DELETE_MITRA',
                targetModel: Mitra::class,
                targetId: $mitraAttributes['id'],
                stateBefore: [
                    'mitra' => $mitraAttributes,
                    'kemitraans' => $kemitraanData,
                ],
                stateAfter: null,
                statusCode: 200,
                isReversible: true,
            );

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return response()->json([
            'status' => 'success',
            'message' => "Mitra '{$mitraAttributes['nama_1']}' berhasil dihapus dari sistem.",
            'data' => [
                'deleted_id' => $mitraAttributes['id'],
            ],
        ]);
    }
}
