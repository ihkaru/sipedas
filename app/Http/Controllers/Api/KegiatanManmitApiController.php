<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KegiatanManmit;
use App\Models\Mitra;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KegiatanManmitApiController extends Controller
{
    /**
     * Daftar Kegiatan Manmit beserta jenis honor yang tersedia.
     */
    public function index(Request $request): JsonResponse
    {
        $query = KegiatanManmit::with('honors');

        if ($request->filled('tahun')) {
            $year = $request->input('tahun');
            $query->whereYear('tgl_mulai_pelaksanaan', $year);
        }

        if ($request->filled('jenis_kegiatan')) {
            $query->where('jenis_kegiatan', $request->input('jenis_kegiatan'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('nama', 'like', "%{$search}%");
        }

        $kegiatans = $query->latest('id')->paginate(25);

        return response()->json([
            'status' => 'success',
            'data' => $kegiatans->items(),
            'meta' => [
                'current_page' => $kegiatans->currentPage(),
                'last_page' => $kegiatans->lastPage(),
                'total' => $kegiatans->total(),
            ],
        ]);
    }

    /**
     * Daftar Mitra beserta status kemitraan untuk tahun yang ditentukan.
     */
    public function mitras(Request $request): JsonResponse
    {
        $tahun = (int)$request->input('tahun', now()->year);
        $search = $request->input('search');

        $query = Mitra::with(['kemitraans' => fn($q) => $q->where('tahun', $tahun)]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_1', 'like', "%{$search}%")
                  ->orWhere('id_sobat', 'like', "%{$search}%");
            });
        }

        if ($request->boolean('aktif_only', true)) {
            $query->whereHas('kemitraans', function ($q) use ($tahun) {
                $q->where('tahun', $tahun)->where('status', 'AKTIF');
            });
        }

        $mitras = $query->orderBy('nama_1')->paginate(50);

        return response()->json([
            'status' => 'success',
            'data' => $mitras->items(),
            'meta' => [
                'tahun' => $tahun,
                'current_page' => $mitras->currentPage(),
                'total' => $mitras->total(),
            ],
        ]);
    }
}
