<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\GetDokumenRequest;
use App\Models\AlokasiHonor;
use App\Models\Pegawai;
use App\Supports\Constants;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class DokumenApiController extends Controller
{
    /**
     * Tampilkan daftar Kontrak SPK (Perjanjian Kerja).
     */
    public function kontrak(GetDokumenRequest $request): JsonResponse
    {
        $tahun = $request->input('tahun', now()->year);
        $bulan = $request->input('bulan');
        $idKegiatanManmit = $request->input('id_kegiatan_manmit');
        $mitraId = $request->input('mitra_id');
        $idHonor = $request->input('id_honor');
        $isFull = $request->boolean('full');

        $query = AlokasiHonor::with([
            'mitra',
            'honor.kegiatanManmit',
            'kontrak' => fn($q) => $q->where('jenis', Constants::JENIS_NOMOR_SURAT_PERJANJIAN_KERJA),
        ])
        ->whereHas('kontrak');

        if ($mitraId) {
            $query->where('mitra_id', $mitraId);
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

        // Kelompokkan per nomor kontrak unik
        $grouped = $records->groupBy('surat_perjanjian_kerja_id')->map(function ($items, $kontrakId) {
            $first = $items->first();
            $kontrak = $first->kontrak;
            $mitra = $first->mitra;
            $kegiatan = $first->honor?->kegiatanManmit;
            $bulan = $first->tanggal_mulai_perjanjian?->month;
            $tahun = $first->tanggal_mulai_perjanjian?->year;
            $tanggalDokumen = $kontrak?->tanggal_nomor ? Carbon::parse($kontrak->tanggal_nomor) : now();
            $ppk = Pegawai::getPpkByDate($tanggalDokumen);

            $printUrl = url("/cetak/kontrak?tahun={$tahun}&bulan={$bulan}" .
                ($kegiatan ? "&id_kegiatan_manmit={$kegiatan->id}" : "") .
                ($mitra ? "&mitra_id={$mitra->id}" : ""));

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
                'total_akumulasi_honor' => $items->sum('total_honor'),
                'jumlah_kegiatan' => $items->count(),
                'alokasi_ids' => $items->pluck('id')->values(),
                'url_cetak_pdf' => $printUrl,
            ];
        })->values();

        return response()->json([
            'status' => 'success',
            'data' => $grouped,
            'meta' => [
                'total_kontrak' => $grouped->count(),
                'total_alokasi' => $records->count(),
            ],
        ]);
    }

    /**
     * Tampilkan daftar dokumen BAST.
     */
    public function bast(GetDokumenRequest $request): JsonResponse
    {
        $tahun = $request->input('tahun', now()->year);
        $bulan = $request->input('bulan');
        $idKegiatanManmit = $request->input('id_kegiatan_manmit');
        $mitraId = $request->input('mitra_id');
        $idHonor = $request->input('id_honor');

        $query = AlokasiHonor::with([
            'mitra',
            'honor.kegiatanManmit',
            'bast' => fn($q) => $q->where('jenis', Constants::JENIS_NOMOR_SURAT_BAST),
        ])
        ->whereHas('bast');

        if ($mitraId) {
            $query->where('mitra_id', $mitraId);
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

        // BAST unik per surat_bast_id
        $grouped = $records->groupBy('surat_bast_id')->map(function ($items, $bastId) {
            $first = $items->first();
            $bast = $first->bast;
            $mitra = $first->mitra;
            $kegiatan = $first->honor?->kegiatanManmit;
            $bulan = $first->tanggal_akhir_perjanjian?->month;
            $tahun = $first->tanggal_akhir_perjanjian?->year;
            $tanggalDokumen = $bast?->tanggal_nomor ? Carbon::parse($bast->tanggal_nomor) : now();
            $ppk = Pegawai::getPpkByDate($tanggalDokumen);

            $printUrl = url("/cetak/bast?tahun={$tahun}&bulan={$bulan}" .
                ($kegiatan ? "&id_kegiatan_manmit={$kegiatan->id}" : "") .
                ($mitra ? "&mitra_id={$mitra->id}" : ""));

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
                'total_honor' => $items->sum('total_honor'),
                'alokasi_ids' => $items->pluck('id')->values(),
                'url_cetak_pdf' => $printUrl,
            ];
        })->values();

        return response()->json([
            'status' => 'success',
            'data' => $grouped,
            'meta' => [
                'total_bast' => $grouped->count(),
                'total_alokasi' => $records->count(),
            ],
        ]);
    }
}
