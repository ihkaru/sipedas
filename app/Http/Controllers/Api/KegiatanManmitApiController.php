<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KegiatanManmit;
use App\Models\Mitra;
use App\Services\HonorService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KegiatanManmitApiController extends Controller
{
    /**
     * Daftar Kegiatan Manmit beserta jenis honor yang tersedia.
     * Dilengkapi fitur search, multi-filter, sorting, dan mode compact untuk efisiensi token AI.
     */
    public function index(Request $request): JsonResponse
    {
        $query = KegiatanManmit::with('honors');

        // 1. Search (Keyword Search pada Nama atau ID Kegiatan)
        $search = $request->input('q') ?? $request->input('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%");
            });
        }

        // 2. Filter Tahun
        if ($request->filled('tahun')) {
            $year = (int)$request->input('tahun');
            $query->where(function ($q) use ($year) {
                $q->whereYear('tgl_mulai_pelaksanaan', $year)
                  ->orWhereYear('tgl_akhir_pelaksanaan', $year);
            });
        }

        // 3. Filter Bulan (Kegiatan yang aktif pada bulan target)
        if ($request->filled('bulan')) {
            $bulan = (int)$request->input('bulan');
            $tahun = (int)($request->input('tahun') ?? now()->year);
            $startOfMonth = Carbon::create($tahun, $bulan, 1)->startOfMonth()->toDateString();
            $endOfMonth = Carbon::create($tahun, $bulan, 1)->endOfMonth()->toDateString();

            $query->where(function ($q) use ($startOfMonth, $endOfMonth) {
                $q->where('tgl_mulai_pelaksanaan', '<=', $endOfMonth)
                  ->where('tgl_akhir_pelaksanaan', '>=', $startOfMonth);
            });
        }

        // 4. Filter Jenis Kegiatan (SURVEI / SENSUS)
        $jenis = $request->input('jenis') ?? $request->input('jenis_kegiatan');
        if ($jenis) {
            $query->where('jenis_kegiatan', strtoupper($jenis));
        }

        // 5. Filter Hanya yang Memiliki Rincian Honor
        if ($request->boolean('has_honors', false)) {
            $query->whereHas('honors');
        }

        // 6. Sorting
        $allowedSorts = ['id', 'nama', 'tgl_mulai_pelaksanaan', 'tgl_akhir_pelaksanaan', 'created_at'];
        $sortBy = in_array($request->input('sort_by'), $allowedSorts) ? $request->input('sort_by') : 'tgl_mulai_pelaksanaan';
        $sortOrder = strtolower($request->input('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortBy, $sortOrder);

        // 7. Pagination
        $perPage = min(max((int)($request->input('per_page') ?? $request->input('limit') ?? 20), 1), 100);
        $paginated = $query->paginate($perPage);

        // 8. Compact / Token-Dense Mode (Hemat Token AI ~80%)
        $isCompact = $request->boolean('compact', false) || $request->boolean('summary', false);

        if ($isCompact) {
            $items = collect($paginated->items())->map(function ($keg) {
                return [
                    'id' => $keg->id,
                    'nama' => $keg->nama,
                    'jenis' => $keg->jenis_kegiatan,
                    'tgl_mulai' => $keg->tgl_mulai_pelaksanaan,
                    'tgl_akhir' => $keg->tgl_akhir_pelaksanaan,
                    'honors' => $keg->honors->map(fn($h) => [
                        'id' => $h->id,
                        'jabatan' => $h->jabatan,
                        'harga' => (float)$h->harga_per_satuan,
                        'satuan' => $h->satuan_honor,
                    ]),
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
     * Daftar Mitra beserta status kemitraan dan indikator sisa plafon SBML.
     * Dilengkapi pencarian fleksibel, batch lookup IDs, dan mode compact.
     */
    public function mitras(Request $request): JsonResponse
    {
        $tahun = (int)$request->input('tahun', now()->year);
        $search = $request->input('q') ?? $request->input('search');

        $query = Mitra::with(['kemitraans' => fn($q) => $q->where('tahun', $tahun)]);

        // 1. Batch ID / ID Sobat lookup (Fetch-Once, Process-Locally)
        if ($request->filled('ids')) {
            $rawIds = is_array($request->input('ids')) ? $request->input('ids') : explode(',', $request->input('ids'));
            $cleanIds = array_map('trim', array_filter($rawIds));
            if (!empty($cleanIds)) {
                $query->where(function ($q) use ($cleanIds) {
                    $q->whereIn('id', $cleanIds)
                      ->orWhereIn('id_sobat', $cleanIds);
                });
            }
        }

        // 2. Keyword Search (Nama, ID Sobat, NIK, Email, atau No Telp)
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_1', 'like', "%{$search}%")
                  ->orWhere('id_sobat', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('no_telp', 'like', "%{$search}%");
            });
        }

        // 3. Status Kemitraan Filter
        if ($request->filled('status')) {
            $status = strtoupper($request->input('status'));
            $query->whereHas('kemitraans', fn($q) => $q->where('tahun', $tahun)->where('status', $status));
        } elseif ($request->boolean('aktif_only', true)) {
            $query->whereHas('kemitraans', fn($q) => $q->where('tahun', $tahun)->where('status', 'AKTIF'));
        }

        // 4. Filter Wilayah Domisili (Kecamatan & Desa)
        if ($request->filled('kecamatan')) {
            $kec = $request->input('kecamatan');
            $query->where(function ($q) use ($kec) {
                $q->where('kecamatan_domisili', 'like', "%{$kec}%")
                  ->orWhere('alamat_kec', $kec);
            });
        }
        if ($request->filled('desa')) {
            $desa = $request->input('desa');
            $query->where(function ($q) use ($desa) {
                $q->where('desa_domisili', 'like', "%{$desa}%")
                  ->orWhere('alamat_desa', $desa);
            });
        }

        // 5. Filter Demografis & Posisi (Jenis Kelamin & Posisi)
        if ($request->filled('jenis_kelamin')) {
            $jk = strtoupper(trim($request->input('jenis_kelamin')));
            if (in_array($jk, ['L', 'LAKI-LAKI', 'PRIA'])) {
                $query->where(function ($q) {
                    $q->where('jenis_kelamin', 'like', 'L%')
                      ->orWhere('jenis_kelamin', 'PRIA');
                });
            } elseif (in_array($jk, ['P', 'PEREMPUAN', 'WANITA'])) {
                $query->where(function ($q) {
                    $q->where('jenis_kelamin', 'like', 'P%')
                      ->orWhere('jenis_kelamin', 'WANITA');
                });
            } else {
                $query->where('jenis_kelamin', 'like', "%{$jk}%");
            }
        }
        if ($request->filled('posisi')) {
            $posisi = $request->input('posisi');
            $query->where(function ($q) use ($posisi) {
                $q->where('posisi', 'like', "%{$posisi}%")
                  ->orWhere('posisi_daftar', 'like', "%{$posisi}%");
            });
        }

        // 6. Filter Penugasan di Bulan Tertentu (has_allocations: 1/0)
        $targetBulan = $request->filled('bulan') ? (int)$request->input('bulan') : null;
        if ($request->filled('has_allocations') && $targetBulan) {
            $hasAlloc = $request->boolean('has_allocations');
            if ($hasAlloc) {
                $query->whereHas('alokasiHonors', function ($q) use ($tahun, $targetBulan) {
                    $q->whereMonth('tanggal_mulai_perjanjian', $targetBulan)
                      ->whereYear('tanggal_mulai_perjanjian', $tahun);
                });
            } else {
                $query->whereDoesntHave('alokasiHonors', function ($q) use ($tahun, $targetBulan) {
                    $q->whereMonth('tanggal_mulai_perjanjian', $targetBulan)
                      ->whereYear('tanggal_mulai_perjanjian', $tahun);
                });
            }
        }

        // 7. Sorting
        $sortMap = [
            'id' => 'id',
            'nama' => 'nama_1',
            'nama_1' => 'nama_1',
            'id_sobat' => 'id_sobat',
            'nik' => 'nik',
            'created_at' => 'created_at',
        ];
        $sortKey = $request->input('sort_by');
        $sortBy = $sortMap[$sortKey] ?? 'nama_1';
        $sortOrder = strtolower($request->input('sort_order', 'asc')) === 'desc' ? 'desc' : 'asc';
        $query->orderBy($sortBy, $sortOrder);

        // 8. Pagination
        $perPage = min(max((int)($request->input('per_page') ?? $request->input('limit') ?? 20), 1), 100);
        $paginated = $query->paginate($perPage);

        // 9. Indikator Sisa SBML per Bulan (Opsional untuk AI Pre-selection)
        $withSbml = $request->boolean('with_sbml', false) || $request->filled('bulan');

        $isCompact = $request->boolean('compact', false) || $request->boolean('summary', false);

        $items = collect($paginated->items())->map(function ($mitra) use ($tahun, $isCompact, $withSbml, $targetBulan) {
            $kemitraan = $mitra->kemitraans->first();
            $status = $kemitraan?->status ?? 'BELUM_TERDAFTAR';

            $row = [
                'id' => $mitra->id,
                'id_sobat' => $mitra->id_sobat,
                'nik' => $mitra->nik,
                'nama' => $mitra->nama_1,
                'status_kemitraan' => $status,
                'tahun_kemitraan' => $tahun,
            ];

            if ($withSbml && $targetBulan) {
                $start = Carbon::create($tahun, $targetBulan, 1)->startOfMonth();
                $end = Carbon::create($tahun, $targetBulan, 1)->endOfMonth();
                $remaining = HonorService::getMitraRemainingBudget($mitra->id, $start, $end);
                $row['sisa_sbml'] = [
                    'bulan' => $targetBulan,
                    'sisa_survei' => $remaining['min_survei'],
                    'sisa_sensus' => $remaining['min_sensus'],
                ];
            }

            if (!$isCompact) {
                $row['email'] = $mitra->email;
                $row['no_telp'] = $mitra->no_telp;
                $row['kecamatan'] = $mitra->kecamatan_domisili;
                $row['desa'] = $mitra->desa_domisili;
                $row['jenis_kelamin'] = $mitra->jenis_kelamin;
                $row['posisi'] = $mitra->posisi ?? $mitra->posisi_daftar;
                $row['created_at'] = $mitra->created_at;
            }

            return $row;
        });

        // 7. Filter Hanya yang Sisa SBML Masih Tersedia di Bulan Tersebut
        if ($request->boolean('available_only', false) && $targetBulan) {
            $items = $items->filter(fn($m) => ($m['sisa_sbml']['sisa_survei'] ?? 0) > 0)->values();
        }

        return response()->json([
            'status' => 'success',
            'data' => $items,
            'meta' => [
                'tahun' => $tahun,
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'has_more' => $paginated->hasMorePages(),
            ],
        ]);
    }
}
