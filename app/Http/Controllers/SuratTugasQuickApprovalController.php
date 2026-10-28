<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\Pengaturan;
use App\Models\Penugasan;
use App\Models\Setting;
use App\Models\User;
use App\Services\SuratTugas\SuratTugasNotificationService;
use App\Supports\Constants;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SuratTugasQuickApprovalController extends Controller
{
    /**
     * Menangani aksi persetujuan cepat (1-klik) Surat Tugas melalui tautan bertanda tangan aman (Signed URL).
     */
    public function handleQuickApproval(Request $request, string $grup_id)
    {
        // 1. Tangkal crawler atau bot pratinjau tautan (WhatsApp / Facebook / Telegram crawler)
        // Mencegah link preview otomatis memicu aksi persetujuan tanpa interaksi manusia riil
        $userAgent = $request->userAgent() ?? '';
        if ($this->isCrawlerBot($userAgent)) {
            return response()->make(
                '<!DOCTYPE html><html><head>'.
                '<title>Persetujuan Cepat Surat Tugas — Dokter V</title>'.
                '<meta property="og:title" content="Persetujuan Cepat Surat Tugas — Dokter V" />'.
                '<meta property="og:description" content="Tautan persetujuan resmi Surat Tugas Dokter V." />'.
                '</head><body>Persetujuan Cepat Surat Tugas</body></html>',
                200,
                ['Content-Type' => 'text/html']
            );
        }

        // 2. Verifikasi tanda tangan kriptografis URL (HMAC SHA-256)
        if (! $request->hasValidSignature()) {
            return view('surat_tugas.quick_approval_status', [
                'status' => 'invalid',
                'title' => 'Tautan Tidak Valid atau Kedaluwarsa',
                'message' => 'Tautan persetujuan ini tidak valid, telah dimodifikasi, atau masa berlakunya telah habis. Silakan login ke aplikasi untuk meninjau secara manual.',
            ]);
        }

        // 3. Ambil seluruh record penugasan dalam grup pengajuan ini
        $penugasans = Penugasan::where('grup_id', $grup_id)
            ->with(['riwayatPengajuan', 'pegawai', 'mitra', 'kegiatan', 'suratTugas', 'suratPerjadin', 'pengaju'])
            ->get();

        if ($penugasans->isEmpty()) {
            return view('surat_tugas.quick_approval_status', [
                'status' => 'invalid',
                'title' => 'Pengajuan Tidak Ditemukan',
                'message' => "Data pengajuan Surat Tugas dengan ID grup '{$grup_id}' tidak ditemukan di sistem.",
            ]);
        }

        /** @var Penugasan $first */
        $first = $penugasans->first();
        $currentStatus = $first->riwayatPengajuan?->status;

        // 4. Periksa apakah sudah pernah disetujui sebelumnya
        if (in_array($currentStatus, [
            Constants::STATUS_PENGAJUAN_DISETUJUI,
            Constants::STATUS_PENGAJUAN_DICETAK,
            Constants::STATUS_PENGAJUAN_DIKUMPULKAN,
            Constants::STATUS_PENGAJUAN_DICAIRKAN,
        ])) {
            return view('surat_tugas.quick_approval_status', [
                'status' => 'already_approved',
                'title' => 'Surat Tugas Sudah Disetujui',
                'message' => 'Pengajuan Surat Tugas ini telah disetujui sebelumnya.',
                'first' => $first,
                'penugasans' => $penugasans,
                'nomorSurat' => $first->suratTugas ? $first->suratTugas->nomor_surat_tugas : '-',
                'nomorSpd' => $first->suratPerjadin?->nomor_surat_perjadin,
            ]);
        }

        if ($currentStatus === Constants::STATUS_PENGAJUAN_DITOLAK) {
            return view('surat_tugas.quick_approval_status', [
                'status' => 'rejected',
                'title' => 'Surat Tugas Telah Ditolak',
                'message' => 'Pengajuan Surat Tugas ini berstatus ditolak.',
                'first' => $first,
                'penugasans' => $penugasans,
            ]);
        }

        // 5. Validasi wewenang approver berdasarkan parameter NIP tertanda
        $approverNip = (string) $request->query('nip', '');
        $approverPegawai = ! empty($approverNip) ? Pegawai::where('nip', $approverNip)->first() : null;
        $approverUser = $approverPegawai ? User::where('email', $approverPegawai->email)->first() : null;

        $isAuthorized = false;
        if ($approverUser && Penugasan::isUserApprover($approverUser, $first->plh_id)) {
            $isAuthorized = true;
        } elseif (! empty($approverNip)) {
            $kasubbagNip = Pengaturan::getValue('NIP_KASUBBAG_TU', config('satker.pejabat.kasubbag_tu_nip', '198008112005021004'));
            $kepalaNip = Pengaturan::getValue('ID_PLH_DEFAULT', config('satker.pejabat.kepala_nip', '196908031992111001'));
            $mode = Setting::where('key', 'st_approval_mode')->value('value') ?? 'delegasi_ppspm';

            if (($mode === 'ppspm_only' || $mode === 'delegasi_ppspm') && $approverNip === $kasubbagNip) {
                $isAuthorized = true;
            }
            if (($mode === 'kepala_only' || $mode === 'delegasi_ppspm') && ($approverNip === $kepalaNip || $approverNip === $first->plh_id)) {
                $isAuthorized = true;
            }
        }

        if (! $isAuthorized) {
            return view('surat_tugas.quick_approval_status', [
                'status' => 'unauthorized',
                'title' => 'Wewenang Tidak Sesuai',
                'message' => 'Identitas Anda tidak terdaftar sebagai pemegang wewenang persetujuan untuk pengajuan Surat Tugas ini.',
            ]);
        }

        // 6. Eksekusi persetujuan untuk seluruh penugasan dalam grup ini
        $approverLabel = $approverPegawai ? ($approverPegawai->panggilan ?? $approverPegawai->nama) : 'Pimpinan Satker';

        try {
            DB::transaction(function () use ($penugasans, $approverLabel) {
                foreach ($penugasans as $p) {
                    if ($p->riwayatPengajuan?->status === Constants::STATUS_PENGAJUAN_DIKIRIM) {
                        if (! $p->surat_tugas_id) {
                            $p->assignNomorSuratTugas();
                        }
                        if ($p->jenis_surat_tugas !== Constants::NON_SPPD && ! $p->surat_perjadin_id) {
                            $p->assignNomorSuratPerjadin();
                        }
                        $p->riwayatPengajuan->updateStatus(Constants::STATUS_PENGAJUAN_DISETUJUI, 'tgl_diterima', now());
                        SuratTugasNotificationService::notifyDisetujui($p, $approverLabel);
                    }
                }
            });

            $first->refresh();
            $first->load(['suratTugas', 'suratPerjadin']);

            Log::info("SuratTugasQuickApproval: Berhasil menyetujui grup penugasan #{$grup_id} via 1-klik.", [
                'grup_id' => $grup_id,
                'approver_nip' => $approverNip,
                'approver_label' => $approverLabel,
                'nomor_st' => $first->suratTugas?->nomor_surat_tugas,
            ]);

            return view('surat_tugas.quick_approval_status', [
                'status' => 'success',
                'title' => 'Surat Tugas Berhasil Disetujui!',
                'message' => 'Pengajuan telah resmi disetujui dan nomor naskah dinas resmi telah diterbitkan oleh sistem.',
                'first' => $first,
                'penugasans' => $penugasans,
                'nomorSurat' => $first->suratTugas ? $first->suratTugas->nomor_surat_tugas : '-',
                'nomorSpd' => $first->suratPerjadin?->nomor_surat_perjadin,
                'approverLabel' => $approverLabel,
            ]);
        } catch (\Throwable $e) {
            Log::error('SuratTugasQuickApproval: Gagal memproses persetujuan cepat: '.$e->getMessage(), [
                'exception' => $e,
                'grup_id' => $grup_id,
            ]);

            return view('surat_tugas.quick_approval_status', [
                'status' => 'error',
                'title' => 'Terjadi Kesalahan',
                'message' => 'Terjadi kesalahan sistem saat memproses persetujuan: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Memeriksa apakah User-Agent adalah crawler pratinjau tautan bot.
     */
    protected function isCrawlerBot(string $userAgent): bool
    {
        return (bool) preg_match('/(facebookexternalhit|WhatsApp|Twitterbot|TelegramBot|Slackbot|LinkedInBot|Googlebot|bingbot|applebot)/i', $userAgent);
    }
}
