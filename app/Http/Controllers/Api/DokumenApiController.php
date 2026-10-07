<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\GetDokumenRequest;
use App\Models\AlokasiHonor;
use App\Models\Pegawai;
use App\Supports\Constants;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class DokumenApiController extends Controller
{
    /**
     * Tampilkan daftar Kontrak SPK (Perjanjian Kerja).
     * Dilengkapi search, multi-filter, pagination, dan mode compact token-dense.
     */
    public function kontrak(GetDokumenRequest $request): JsonResponse
    {
        $tahun = $request->input('tahun', now()->year);
        $bulan = $request->input('bulan');
        $idKegiatanManmit = $request->input('id_kegiatan_manmit') ?? $request->input('kegiatan_id');
        $mitraId = $request->input('mitra_id');
        $idSobat = $request->input('id_sobat');
        $idHonor = $request->input('id_honor');
        $search = $request->input('q') ?? $request->input('search');
        $isFull = $request->boolean('full');

        $query = AlokasiHonor::with([
            'mitra',
            'honor.kegiatanManmit',
            'kontrak' => fn($q) => $q->where('jenis', Constants::JENIS_NOMOR_SURAT_PERJANJIAN_KERJA),
        ])
        ->whereHas('kontrak');

        // 1. Search keyword
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('mitra', function ($m) use ($search) {
                    $m->where('nama_1', 'like', "%{$search}%")
                      ->orWhere('id_sobat', 'like', "%{$search}%")
                      ->orWhere('nik', 'like', "%{$search}%");
                })
                ->orWhereHas('kontrak', function ($ns) use ($search) {
                    $ns->searchNomor($search);
                })
                ->orWhereHas('honor.kegiatanManmit', function ($k) use ($search) {
                    $k->where('nama', 'like', "%{$search}%");
                });
            });
        }

        if ($mitraId) {
            $query->where('mitra_id', $mitraId);
        } elseif ($idSobat) {
            $query->whereHas('mitra', fn($m) => $m->where('id_sobat', $idSobat));
        }

        if ($idHonor) {
            $query->where('honor_id', $idHonor);
        }

        if ($idKegiatanManmit) {
            $query->whereHas('honor', fn($q) => $q->where('kegiatan_manmit_id', $idKegiatanManmit));
        }

        if (!$isFull && $tahun) {
            $query->whereYear('tanggal_mulai_perjanjian', $tahun);
        }

        if (!$isFull && $bulan) {
            $query->whereMonth('tanggal_mulai_perjanjian', $bulan);
        }

        $records = $query->get();

        $isCompact = $request->boolean('compact', false) || $request->boolean('summary', false);

        // Kelompokkan per nomor kontrak unik
        $grouped = $records->groupBy('surat_perjanjian_kerja_id')->map(function ($items, $kontrakId) use ($isCompact) {
            $first = $items->first();
            $kontrak = $first->kontrak;
            $mitra = $first->mitra;
            $kegiatan = $first->honor?->kegiatanManmit;
            $bulan = $first->tanggal_mulai_perjanjian?->month;
            $tahun = $first->tanggal_mulai_perjanjian?->year;
            $tanggalDokumen = $kontrak?->tanggal_nomor ? Carbon::parse($kontrak->tanggal_nomor) : now();

            $printUrl = url("/cetak/kontrak?tahun={$tahun}&bulan={$bulan}" .
                ($kegiatan ? "&id_kegiatan_manmit={$kegiatan->id}" : "") .
                ($mitra ? "&mitra_id={$mitra->id}" : ""));

            if ($isCompact) {
                return [
                    'id' => $kontrakId,
                    'nomor_spk' => $kontrak?->nomor_surat_perjanjian_kerja,
                    'tanggal' => $kontrak?->tanggal_nomor,
                    'mitra' => $mitra?->nama_1,
                    'id_sobat' => $mitra?->id_sobat,
                    'total_honor' => (float)$items->sum('total_honor'),
                    'jumlah_kegiatan' => $items->count(),
                    'url_cetak_pdf' => $printUrl,
                ];
            }

            $ppk = Pegawai::getPpkByDate($tanggalDokumen);

            return [
                'surat_perjanjian_kerja_id' => $kontrakId,
                'nomor_surat' => $kontrak?->nomor_surat_perjanjian_kerja,
                'tanggal_nomor' => $kontrak?->tanggal_nomor,
                'periode' => [
                    'tahun' => $tahun,
                    'bulan' => $bulan,
                ],
                'mitra' => [
                    'id' => $mitra?->id,
                    'id_sobat' => $mitra?->id_sobat,
                    'nama' => $mitra?->nama_1,
                ],
                'ppk' => $ppk ? [
                    'nip' => $ppk->nip,
                    'nama' => $ppk->nama,
                    'jabatan' => $ppk->jabatan,
                ] : null,
                'total_akumulasi_honor' => (float)$items->sum('total_honor'),
                'jumlah_kegiatan' => $items->count(),
                'alokasi_ids' => $items->pluck('id')->values(),
                'url_cetak_pdf' => $printUrl,
            ];
        })->values();

        // Paginasi hasil grouped collection
        $page = (int)$request->input('page', 1);
        $perPage = min(max((int)($request->input('per_page') ?? $request->input('limit') ?? 20), 1), 100);
        $total = $grouped->count();
        $slice = $grouped->slice(($page - 1) * $perPage, $perPage)->values();

        return response()->json([
            'status' => 'success',
            'data' => $slice,
            'meta' => [
                'current_page' => $page,
                'last_page' => (int)ceil($total / $perPage),
                'per_page' => $perPage,
                'total' => $total,
                'total_kontrak' => $total,
                'total_alokasi' => $records->count(),
                'has_more' => ($page * $perPage) < $total,
            ],
        ]);
    }

    /**
     * Tampilkan daftar dokumen BAST.
     * Dilengkapi search, multi-filter, pagination, dan mode compact token-dense.
     */
    public function bast(GetDokumenRequest $request): JsonResponse
    {
        $tahun = $request->input('tahun', now()->year);
        $bulan = $request->input('bulan');
        $idKegiatanManmit = $request->input('id_kegiatan_manmit') ?? $request->input('kegiatan_id');
        $mitraId = $request->input('mitra_id');
        $idSobat = $request->input('id_sobat');
        $idHonor = $request->input('id_honor');
        $search = $request->input('q') ?? $request->input('search');

        $query = AlokasiHonor::with([
            'mitra',
            'honor.kegiatanManmit',
            'bast' => fn($q) => $q->where('jenis', Constants::JENIS_NOMOR_SURAT_BAST),
        ])
        ->whereHas('bast');

        // 1. Search keyword
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('mitra', function ($m) use ($search) {
                    $m->where('nama_1', 'like', "%{$search}%")
                      ->orWhere('id_sobat', 'like', "%{$search}%")
                      ->orWhere('nik', 'like', "%{$search}%");
                })
                ->orWhereHas('bast', function ($ns) use ($search) {
                    $ns->searchNomor($search);
                })
                ->orWhereHas('honor.kegiatanManmit', function ($k) use ($search) {
                    $k->where('nama', 'like', "%{$search}%");
                });
            });
        }

        if ($mitraId) {
            $query->where('mitra_id', $mitraId);
        } elseif ($idSobat) {
            $query->whereHas('mitra', fn($m) => $m->where('id_sobat', $idSobat));
        }

        if ($idHonor) {
            $query->where('honor_id', $idHonor);
        }

        if ($idKegiatanManmit) {
            $query->whereHas('honor', fn($q) => $q->where('kegiatan_manmit_id', $idKegiatanManmit));
        }

        if ($tahun) {
            $query->whereYear('tanggal_akhir_perjanjian', $tahun);
        }

        if ($bulan) {
            $query->whereMonth('tanggal_akhir_perjanjian', $bulan);
        }

        $records = $query->get();

        $isCompact = $request->boolean('compact', false) || $request->boolean('summary', false);

        // BAST unik per surat_bast_id
        $grouped = $records->groupBy('surat_bast_id')->map(function ($items, $bastId) use ($isCompact) {
            $first = $items->first();
            $bast = $first->bast;
            $mitra = $first->mitra;
            $kegiatan = $first->honor?->kegiatanManmit;
            $bulan = $first->tanggal_akhir_perjanjian?->month;
            $tahun = $first->tanggal_akhir_perjanjian?->year;
            $tanggalDokumen = $bast?->tanggal_nomor ? Carbon::parse($bast->tanggal_nomor) : now();

            $printUrl = url("/cetak/bast?tahun={$tahun}&bulan={$bulan}" .
                ($kegiatan ? "&id_kegiatan_manmit={$kegiatan->id}" : "") .
                ($mitra ? "&mitra_id={$mitra->id}" : ""));

            if ($isCompact) {
                return [
                    'id' => $bastId,
                    'nomor_bast' => $bast?->nomor_surat_bast,
                    'tanggal' => $bast?->tanggal_nomor,
                    'mitra' => $mitra?->nama_1,
                    'id_sobat' => $mitra?->id_sobat,
                    'kegiatan' => $kegiatan?->nama,
                    'total_honor' => (float)$items->sum('total_honor'),
                    'url_cetak_pdf' => $printUrl,
                ];
            }

            $ppk = Pegawai::getPpkByDate($tanggalDokumen);

            return [
                'surat_bast_id' => $bastId,
                'nomor_surat' => $bast?->nomor_surat_bast,
                'tanggal_nomor' => $bast?->tanggal_nomor,
                'periode' => [
                    'tahun' => $tahun,
                    'bulan' => $bulan,
                ],
                'mitra' => [
                    'id' => $mitra?->id,
                    'id_sobat' => $mitra?->id_sobat,
                    'nama' => $mitra?->nama_1,
                ],
                'honor' => [
                    'id' => $first->honor?->id,
                    'jabatan' => $first->honor?->jabatan,
                    'jenis_honor' => $first->honor?->jenis_honor,
                    'kegiatan' => $kegiatan?->nama,
                ],
                'ppk' => $ppk ? [
                    'nip' => $ppk->nip,
                    'nama' => $ppk->nama,
                    'jabatan' => $ppk->jabatan,
                ] : null,
                'total_honor' => (float)$items->sum('total_honor'),
                'alokasi_ids' => $items->pluck('id')->values(),
                'url_cetak_pdf' => $printUrl,
            ];
        })->values();

        // Paginasi hasil grouped collection
        $page = (int)$request->input('page', 1);
        $perPage = min(max((int)($request->input('per_page') ?? $request->input('limit') ?? 20), 1), 100);
        $total = $grouped->count();
        $slice = $grouped->slice(($page - 1) * $perPage, $perPage)->values();

        return response()->json([
            'status' => 'success',
            'data' => $slice,
            'meta' => [
                'current_page' => $page,
                'last_page' => (int)ceil($total / $perPage),
                'per_page' => $perPage,
                'total' => $total,
                'total_bast' => $total,
                'total_alokasi' => $records->count(),
                'has_more' => ($page * $perPage) < $total,
            ],
        ]);
    }
}
