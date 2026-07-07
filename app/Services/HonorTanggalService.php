<?php

namespace App\Services;

use App\Models\AlokasiHonor;
use App\Models\Honor;
use App\Models\NomorSurat;
use App\Supports\TanggalMerah;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * HonorTanggalService
 *
 * Memusatkan logika propagasi tanggal_akhir_kegiatan ke semua data turunan.
 * Digunakan oleh HonorObserver (real-time) dan FixHonorTanggalPerjanjian command (bulk fix).
 */
class HonorTanggalService
{
    /**
     * Propagasi tanggal dari satu Honor ke seluruh alokasi dan nomor surat terkait.
     * Mengembalikan jumlah alokasi yang diperbarui.
     */
    public static function propagate(Honor $honor): int
    {
        $tanggalAkhirKegiatan = Carbon::parse($honor->tanggal_akhir_kegiatan);

        $tanggalMulaiKontrak  = $tanggalAkhirKegiatan->copy()->startOfMonth();
        $tanggalAkhirKontrak  = $tanggalAkhirKegiatan->copy()->endOfMonth();
        $tanggalPengajuanSpk  = TanggalMerah::getNextWorkDay($tanggalMulaiKontrak->copy(), -1);
        // BAST: tanggal_nomor maksimal = tanggal_akhir_kegiatan (jika hari kerja),
        // atau hari kerja sebelumnya jika tanggal_akhir_kegiatan jatuh di hari libur.
        $tanggalPengajuanBast = TanggalMerah::getNextWorkDay($tanggalAkhirKegiatan->copy(), -1);

        $count = 0;

        DB::transaction(function () use ($honor, $tanggalMulaiKontrak, $tanggalAkhirKontrak, $tanggalPengajuanSpk, $tanggalPengajuanBast, &$count) {
            $alokasiList = AlokasiHonor::where('honor_id', $honor->id)->get();

            foreach ($alokasiList as $alokasi) {
                // Gunakan DB::table() langsung untuk bypass event 'saving' di AlokasiHonor
                // yang memvalidasi jadwal bentrok — tidak relevan untuk koreksi data ini.
                DB::table('alokasi_honors')->where('id', $alokasi->id)->update([
                    'tanggal_mulai_perjanjian'                  => $tanggalMulaiKontrak,
                    'tanggal_akhir_perjanjian'                  => $tanggalAkhirKontrak,
                    'tanggal_penanda_tanganan_spk_oleh_petugas' => $tanggalPengajuanSpk,
                    'updated_at'                                => now(),
                ]);

                if ($alokasi->surat_perjanjian_kerja_id) {
                    NomorSurat::where('id', $alokasi->surat_perjanjian_kerja_id)
                        ->update(['tanggal_nomor' => $tanggalPengajuanSpk]);
                }

                if ($alokasi->surat_bast_id) {
                    NomorSurat::where('id', $alokasi->surat_bast_id)
                        ->update(['tanggal_nomor' => $tanggalPengajuanBast]);
                }

                $count++;
            }
        });

        return $count;
    }

    /**
     * Cek apakah alokasi honor dari sebuah honor sudah konsisten.
     */
    public static function isKonsisten(Honor $honor): bool
    {
        return !AlokasiHonor::where('honor_id', $honor->id)
            ->where(function ($q) use ($honor) {
                $q->whereRaw('YEAR(tanggal_akhir_perjanjian) != YEAR(?)', [$honor->tanggal_akhir_kegiatan])
                  ->orWhereRaw('MONTH(tanggal_akhir_perjanjian) != MONTH(?)', [$honor->tanggal_akhir_kegiatan]);
            })
            ->exists();
    }
}
