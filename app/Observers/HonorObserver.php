<?php

namespace App\Observers;

use App\Models\AlokasiHonor;
use App\Models\Honor;
use App\Models\NomorSurat;
use App\Supports\TanggalMerah;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * HonorObserver
 *
 * Menjaga konsistensi data turunan ketika honor.tanggal_akhir_kegiatan berubah.
 *
 * Field tanggal_akhir_kegiatan adalah "sumber kebenaran tunggal" yang menentukan:
 *   - Bulan kontrak SPK (startOfMonth s/d endOfMonth)
 *   - Tanggal penanda tanganan SPK (hari kerja sebelum start of month)
 *   - Tanggal nomor BAST (hari kerja sebelum end of month)
 *   - Batas pembayaran (+ 20 hari)
 *   - PPK yang digunakan (berdasarkan bulan kontrak)
 *
 * Jika field ini berubah, semua data turunan di alokasi_honors dan nomor_surats
 * harus diperbarui secara atomik.
 */
class HonorObserver
{
    public function updated(Honor $honor): void
    {
        // Hanya lakukan propagasi jika tanggal_akhir_kegiatan yang berubah
        if (!$honor->wasChanged('tanggal_akhir_kegiatan')) {
            return;
        }

        $tanggalBaru = Carbon::parse($honor->tanggal_akhir_kegiatan);

        $tanggalMulaiKontrak  = $tanggalBaru->copy()->startOfMonth();
        $tanggalAkhirKontrak  = $tanggalBaru->copy()->endOfMonth();
        $tanggalPengajuanSpk  = TanggalMerah::getNextWorkDay($tanggalMulaiKontrak->copy(), -1);
        $tanggalPengajuanBast = TanggalMerah::getNextWorkDay($tanggalAkhirKontrak->copy(), -1);

        DB::transaction(function () use ($honor, $tanggalMulaiKontrak, $tanggalAkhirKontrak, $tanggalPengajuanSpk, $tanggalPengajuanBast) {
            // Ambil semua alokasi honor yang terdampak
            $alokasiList = AlokasiHonor::where('honor_id', $honor->id)
                ->with(['kontrak', 'bast'])
                ->get();

            foreach ($alokasiList as $alokasi) {
                // Update tanggal di alokasi_honors
                $alokasi->update([
                    'tanggal_mulai_perjanjian'                => $tanggalMulaiKontrak,
                    'tanggal_akhir_perjanjian'                => $tanggalAkhirKontrak,
                    'tanggal_penanda_tanganan_spk_oleh_petugas' => $tanggalPengajuanSpk,
                ]);

                // Update tanggal nomor SPK di nomor_surats
                if ($alokasi->surat_perjanjian_kerja_id) {
                    NomorSurat::where('id', $alokasi->surat_perjanjian_kerja_id)
                        ->update(['tanggal_nomor' => $tanggalPengajuanSpk]);
                }

                // Update tanggal nomor BAST di nomor_surats
                if ($alokasi->surat_bast_id) {
                    NomorSurat::where('id', $alokasi->surat_bast_id)
                        ->update(['tanggal_nomor' => $tanggalPengajuanBast]);
                }
            }
        });
    }
}
