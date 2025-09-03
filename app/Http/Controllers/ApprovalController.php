<?php

namespace App\Http\Controllers;

use App\Models\ActionToken;
use App\Services\Sipancong\PengajuanServices;
use App\Supports\SipancongConstants as Constants;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    public function handleApproval(string $token)
    {
        $actionToken = ActionToken::where('token', $token)->first();

        // Cek 1: Token tidak ditemukan
        if (!$actionToken) {
            return view('approvals.invalid', ['message' => 'Link persetujuan ini tidak valid atau tidak ditemukan.']);
        }

        // Cek 2: Token sudah pernah digunakan
        if ($actionToken->used_at) {
            return view('approvals.invalid', ['message' => 'Link ini sudah pernah digunakan pada ' . $actionToken->used_at->format('d M Y H:i') . '.']);
        }

        // Cek 3: Token sudah kedaluwarsa
        if ($actionToken->expires_at->isPast()) {
            return view('approvals.invalid', ['message' => 'Link persetujuan ini sudah kedaluwarsa. Silakan proses melalui aplikasi.']);
        }

        try {
            $pengajuan = $actionToken->load('pengajuan')->pengajuan;

            $dataForApproval = [
                'status_pengajuan_ppk_id' => Constants::STATUS_DISETUJUI_TANPA_CATATAN,
                'catatan_ppk' => 'Disetujui secara otomatis melalui link persetujuan cepat.',
            ];

            // Lakukan aksi berdasarkan jenis token
            switch ($actionToken->action) {
                case 'ppk_approve':
                    // Pastikan dokumen masih di PPK sebelum dieksekusi
                    if ($pengajuan->posisi_dokumen_id !== Constants::POSISI_PPK) {
                        return view('approvals.invalid', ['message' => 'Aksi tidak dapat dilanjutkan karena status pengajuan sudah berubah.']);
                    }
                    PengajuanServices::pemeriksaanPpk($dataForApproval, $pengajuan);
                    break;

                case 'ppspm_approve':
                    if ($pengajuan->posisi_dokumen_id !== Constants::POSISI_PPSPM) {
                        return view('approvals.invalid', ['message' => 'Aksi tidak dapat dilanjutkan karena status pengajuan sudah berubah.']);
                    }
                    // Sesuaikan data untuk PPSPM
                    $dataForApproval['status_pengajuan_ppspm_id'] = Constants::STATUS_DISETUJUI_TANPA_CATATAN;
                    $dataForApproval['catatan_ppspm'] = 'Disetujui secara otomatis melalui link persetujuan cepat.';
                    unset($dataForApproval['status_pengajuan_ppk_id'], $dataForApproval['catatan_ppk']); // Hapus key ppk
                    PengajuanServices::pemeriksaanPpspm($dataForApproval, $pengajuan);
                    break;
                // --- PENAMBAHAN DIMULAI ---
                case 'bendahara_approve':
                    // Pastikan dokumen masih di Bendahara
                    if ($pengajuan->posisi_dokumen_id !== Constants::POSISI_BENDAHARA) {
                        return view('approvals.invalid', ['message' => 'Aksi tidak dapat dilanjutkan karena status pengajuan sudah berubah.']);
                    }
                    // Sesuaikan data untuk Bendahara
                    $dataForApproval['status_pengajuan_bendahara_id'] = Constants::STATUS_DISETUJUI_TANPA_CATATAN;
                    $dataForApproval['catatan_bendahara'] = 'Disetujui otomatis melalui link persetujuan cepat.';
                    unset($dataForApproval['status_pengajuan_ppk_id'], $dataForApproval['catatan_ppk']); // Hapus key ppk
                    PengajuanServices::pemeriksaanBendahara($dataForApproval, $pengajuan);
                    break;
                // --- PENAMBAHAN SELESAI ---

                // Anda bisa menambahkan case lain di sini nanti
                default:
                    return view('approvals.invalid', ['message' => 'Aksi tidak dikenal.']);
            }

            // Tandai token sebagai sudah digunakan
            $actionToken->update(['used_at' => now()]);

            return view('approvals.success', ['pengajuan' => $pengajuan]);
        } catch (\Throwable $th) {
            // Tangani jika ada error saat proses
            return view('approvals.invalid', ['message' => 'Terjadi kesalahan saat memproses permintaan: ' . $th->getMessage()]);
        }
    }
}
