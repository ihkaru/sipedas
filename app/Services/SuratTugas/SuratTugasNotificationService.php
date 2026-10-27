<?php

namespace App\Services\SuratTugas;

use App\Models\Pegawai;
use App\Models\Penugasan;
use App\Models\Setting;
use App\Models\User;
use App\Services\WhatsappNotifier;
use App\Supports\Constants;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

class SuratTugasNotificationService
{
    /**
     * Kirim notifikasi pengajuan Surat Tugas baru kepada Penyetuju (Kepala Satker dan/atau PPSPM/Kasubbag TU).
     *
     * @param array|\Illuminate\Support\Collection|Penugasan $penugasans Kumpulan atau record penugasan dalam satu pengajuan.
     */
    public static function notifyPengajuanBaru($penugasans): void
    {
        try {
            $list = is_array($penugasans) ? collect($penugasans) : (
                $penugasans instanceof \Illuminate\Support\Collection ? $penugasans : collect([$penugasans])
            );

            if ($list->isEmpty()) {
                return;
            }

            /** @var Penugasan $first */
            $first = $list->first();
            $first->loadMissing(['pengaju', 'kegiatan', 'pegawai', 'mitra', 'suratTugas', 'riwayatPengajuan']);

            // Jika status sudah disetujui (misal Non-SPPD atau auto-approve), tidak perlu kirim pesan butuh approval ke pejabat
            if ($first->riwayatPengajuan?->status === Constants::STATUS_PENGAJUAN_DISETUJUI) {
                return;
            }

            $mode = Setting::where('key', 'st_approval_mode')->value('value') ?? 'delegasi_ppspm';

            // 1. Tentukan daftar target penerima notifikasi approver
            $recipients = collect();

            if ($mode === 'delegasi_ppspm' || $mode === 'ppspm_only') {
                $ppspmUsers = User::getPpspm();
                foreach ($ppspmUsers as $u) {
                    $peg = $u->pegawai;
                    $noWa = $peg?->nomor_wa;
                    if (!empty($noWa)) {
                        $recipients->put($noWa, [
                            'nama' => $peg->panggilan ?? $peg->nama,
                            'role' => 'Kasubbag TU / PPSPM',
                        ]);
                    }
                }
            }

            $tembusanKepala = (bool) (Setting::where('key', 'whatsapp_notify_st_tembusan_kepala')->value('value') ?? false);
            if ($mode === 'kepala_only' || ($mode === 'delegasi_ppspm' && $tembusanKepala)) {
                // Cari PLH jika terdefinisi di penugasan
                if (!empty($first->plh_id)) {
                    $plhPegawai = Pegawai::where('nip', $first->plh_id)->first();
                    if ($plhPegawai && !empty($plhPegawai->nomor_wa)) {
                        $recipients->put($plhPegawai->nomor_wa, [
                            'nama' => $plhPegawai->panggilan ?? $plhPegawai->nama,
                            'role' => 'PLH Kepala',
                        ]);
                    }
                }

                // Kepala Satker
                $kepalaUsers = User::getKepalaSatker();
                foreach ($kepalaUsers as $u) {
                    $peg = $u->pegawai;
                    $noWa = $peg?->nomor_wa;
                    if (!empty($noWa)) {
                        $recipients->put($noWa, [
                            'nama' => $peg->panggilan ?? $peg->nama,
                            'role' => 'Kepala Satker',
                        ]);
                    }
                }
            }

            if ($recipients->isEmpty()) {
                Log::info('SuratTugasNotificationService: Tidak ada target nomor WhatsApp peninjau yang ditemukan.', [
                    'mode' => $mode,
                    'penugasan_id' => $first->id,
                ]);
                return;
            }

            // 2. Susun ringkasan personil tertugas
            $personilList = [];
            foreach ($list as $p) {
                if ($p instanceof Penugasan) {
                    $p->loadMissing(['pegawai', 'mitra']);
                    $nama = !empty($p->nip) 
                        ? ($p->pegawai ? ($p->pegawai->nama ?? $p->nip) : $p->nip) 
                        : ($p->mitra ? ($p->mitra->nama_1 ?? $p->id_sobat) : $p->id_sobat);
                    $tipe = !empty($p->nip) ? 'PNS' : 'Mitra';
                    $personilList[] = "- {$nama} ({$tipe})";
                }
            }
            $personilStr = !empty($personilList) ? implode("\n", array_slice($personilList, 0, 8)) : '-';
            if (count($personilList) > 8) {
                $sisa = count($personilList) - 8;
                $personilStr .= "\n- ...dan {$sisa} orang lainnya";
            }

            $namaPengaju = $first->pengaju ? ($first->pengaju->panggilan ?? $first->pengaju->nama) : 'Pengaju';
            $namaKegiatan = $first->kegiatan ? $first->kegiatan->nama : 'Kegiatan Dinas';
            $jenisSt = Constants::JENIS_SURAT_TUGAS_OPTIONS[$first->jenis_surat_tugas] ?? $first->jenis_surat_tugas;
            $lokasi = $first->nama_tempat_tujuan ?? $first->tujuan_penugasan ?? '-';
            $tglMulai = $first->tgl_mulai_tugas ? Carbon::parse($first->tgl_mulai_tugas)->translatedFormat('d M Y') : '-';
            $tglAkhir = $first->tgl_akhir_tugas ? Carbon::parse($first->tgl_akhir_tugas)->translatedFormat('d M Y') : '-';
            $periode = ($tglMulai === $tglAkhir) ? $tglMulai : "{$tglMulai} s.d. {$tglAkhir}";
            $linkAksi = rtrim(config('app.url'), '/') . '/a/penugasans';

            foreach ($recipients as $targetWa => $info) {
                $namaPenyetuju = $info['nama'];
                $message = "*Pengajuan Surat Tugas Baru | SiPEDAS*\n\n" .
                    "Halo, Bapak/Ibu *{$namaPenyetuju}*,\n" .
                    "Ada pengajuan Surat Tugas baru dari *{$namaPengaju}* yang memerlukan persetujuan:\n\n" .
                    "*Kegiatan*: {$namaKegiatan}\n" .
                    "*Jenis*: {$jenisSt}\n" .
                    "*Tujuan*: {$lokasi}\n" .
                    "*Tanggal*: {$periode}\n" .
                    "*Personil Tertugas*:\n{$personilStr}\n\n" .
                    "Silakan buka link berikut untuk memeriksa dan melakukan *Aksi Persetujuan*:\n" .
                    "{$linkAksi}\n\n" .
                    "Semangat bertugas!";

                WhatsappNotifier::send($targetWa, $message, 'whatsapp_notify_st_baru');
            }
        } catch (Throwable $e) {
            Log::error('SuratTugasNotificationService: Gagal mengirim notifikasi pengajuan baru: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
        }
    }

    /**
     * Kirim notifikasi bahwa Surat Tugas telah disetujui kepada Pengaju dan Personil.
     */
    public static function notifyDisetujui(Penugasan $penugasan, ?string $approverName = null): void
    {
        try {
            $penugasan->loadMissing(['pengaju', 'kegiatan', 'pegawai', 'mitra', 'suratTugas', 'suratPerjadin']);

            $authUser = auth()->user();
            $authPegawai = $authUser?->pegawai;
            $namaPenyetuju = $approverName 
                ?? ($authPegawai ? ($authPegawai->panggilan ?? $authPegawai->nama) : 'Penyetuju');

            $namaKegiatan = $penugasan->kegiatan ? $penugasan->kegiatan->nama : 'Kegiatan Dinas';
            $namaTertugas = !empty($penugasan->nip) 
                ? ($penugasan->pegawai ? ($penugasan->pegawai->nama ?? $penugasan->nip) : $penugasan->nip) 
                : ($penugasan->mitra ? ($penugasan->mitra->nama_1 ?? $penugasan->id_sobat) : $penugasan->id_sobat);

            $noSt = $penugasan->suratTugas ? ($penugasan->suratTugas->nomor_surat_tugas ?? '-') : '-';
            $noSpd = $penugasan->suratPerjadin ? $penugasan->suratPerjadin->nomor_surat_perjadin : null;
            $lineSpd = $noSpd ? "*No. SPD*: {$noSpd}\n" : '';

            $lokasi = $penugasan->nama_tempat_tujuan ?? $penugasan->tujuan_penugasan ?? '-';
            $tglMulai = $penugasan->tgl_mulai_tugas ? Carbon::parse($penugasan->tgl_mulai_tugas)->translatedFormat('d M Y') : '-';
            $tglAkhir = $penugasan->tgl_akhir_tugas ? Carbon::parse($penugasan->tgl_akhir_tugas)->translatedFormat('d M Y') : '-';
            $periode = ($tglMulai === $tglAkhir) ? $tglMulai : "{$tglMulai} s.d. {$tglAkhir}";
            $linkRiwayat = rtrim(config('app.url'), '/') . '/a';

            // Kumpulkan nomor WA tujuan (Pengaju dan Personil tertugas jika berbeda)
            $targets = collect();

            if ($penugasan->pengaju && !empty($penugasan->pengaju->nomor_wa)) {
                $targets->put($penugasan->pengaju->nomor_wa, $penugasan->pengaju->panggilan ?? $penugasan->pengaju->nama);
            }

            $personilPegawai = $penugasan->pegawai;
            $personilMitra = $penugasan->mitra;
            $personilWa = !empty($penugasan->nip) ? $personilPegawai?->nomor_wa : $personilMitra?->nomor_wa;
            if (!empty($personilWa) && !$targets->has($personilWa)) {
                $targets->put($personilWa, $personilPegawai ? ($personilPegawai->panggilan ?? $namaTertugas) : $namaTertugas);
            }

            foreach ($targets as $targetWa => $namaPenerima) {
                $message = "*Surat Tugas Disetujui! | SiPEDAS*\n\n" .
                    "Halo, *{$namaPenerima}*,\n" .
                    "Kabar baik! Pengajuan Surat Tugas untuk kegiatan *'{$namaKegiatan}'* telah *disetujui* oleh *{$namaPenyetuju}*.\n\n" .
                    "*No. Surat Tugas*: {$noSt}\n" .
                    $lineSpd .
                    "*Tertugas*: {$namaTertugas}\n" .
                    "*Tujuan*: {$lokasi}\n" .
                    "*Tanggal*: {$periode}\n\n" .
                    "Silakan mencetak Surat Tugas secara mandiri melalui menu Riwayat di SiPEDAS untuk diserahkan ke TU:\n" .
                    "{$linkRiwayat}\n\n" .
                    "Semangat bertugas!";

                WhatsappNotifier::send($targetWa, $message, 'whatsapp_notify_st_disetujui');
            }
        } catch (Throwable $e) {
            Log::error('SuratTugasNotificationService: Gagal mengirim notifikasi disetujui: ' . $e->getMessage(), [
                'penugasan_id' => $penugasan->id,
                'exception' => $e,
            ]);
        }
    }

    /**
     * Kirim notifikasi arahan perbaikan (revisi) kepada Pengaju.
     */
    public static function notifyPerluRevisi(Penugasan $penugasan, string $catatan, ?string $approverName = null): void
    {
        try {
            $penugasan->loadMissing(['pengaju', 'kegiatan', 'pegawai', 'mitra']);

            $authUser = auth()->user();
            $authPegawai = $authUser?->pegawai;
            $namaPemeriksa = $approverName 
                ?? ($authPegawai ? ($authPegawai->panggilan ?? $authPegawai->nama) : 'Pemeriksa');

            $namaPengaju = $penugasan->pengaju ? ($penugasan->pengaju->panggilan ?? $penugasan->pengaju->nama) : 'Pengaju';
            $targetWa = $penugasan->pengaju?->nomor_wa;

            if (empty($targetWa)) {
                return;
            }

            $namaKegiatan = $penugasan->kegiatan ? $penugasan->kegiatan->nama : 'Kegiatan Dinas';
            $namaTertugas = !empty($penugasan->nip) 
                ? ($penugasan->pegawai ? ($penugasan->pegawai->nama ?? $penugasan->nip) : $penugasan->nip) 
                : ($penugasan->mitra ? ($penugasan->mitra->nama_1 ?? $penugasan->id_sobat) : $penugasan->id_sobat);

            $link = rtrim(config('app.url'), '/') . '/a';

            $message = "*Perlu Perbaikan Surat Tugas | SiPEDAS*\n\n" .
                "Halo, *{$namaPengaju}*,\n" .
                "Pengajuan Surat Tugas untuk kegiatan *'{$namaKegiatan}'* (Tertugas: {$namaTertugas}) memerlukan perbaikan dari *{$namaPemeriksa}*.\n\n" .
                "*Catatan Arahan Perbaikan*:\n" .
                "\"*{$catatan}*\"\n\n" .
                "Silakan buka link berikut untuk melakukan perbaikan pengajuan:\n" .
                "{$link}\n\n" .
                "Semangat!";

            WhatsappNotifier::send($targetWa, $message, 'whatsapp_notify_st_revisi');
        } catch (Throwable $e) {
            Log::error('SuratTugasNotificationService: Gagal mengirim notifikasi revisi: ' . $e->getMessage(), [
                'penugasan_id' => $penugasan->id,
                'exception' => $e,
            ]);
        }
    }

    /**
     * Kirim notifikasi penolakan kepada Pengaju.
     */
    public static function notifyDitolak(Penugasan $penugasan, ?string $approverName = null): void
    {
        try {
            $penugasan->loadMissing(['pengaju', 'kegiatan', 'pegawai', 'mitra']);

            $authUser = auth()->user();
            $authPegawai = $authUser?->pegawai;
            $namaPemeriksa = $approverName 
                ?? ($authPegawai ? ($authPegawai->panggilan ?? $authPegawai->nama) : 'Penyetuju');

            $namaPengaju = $penugasan->pengaju ? ($penugasan->pengaju->panggilan ?? $penugasan->pengaju->nama) : 'Pengaju';
            $targetWa = $penugasan->pengaju?->nomor_wa;

            if (empty($targetWa)) {
                return;
            }

            $namaKegiatan = $penugasan->kegiatan ? $penugasan->kegiatan->nama : 'Kegiatan Dinas';
            $namaTertugas = !empty($penugasan->nip) 
                ? ($penugasan->pegawai ? ($penugasan->pegawai->nama ?? $penugasan->nip) : $penugasan->nip) 
                : ($penugasan->mitra ? ($penugasan->mitra->nama_1 ?? $penugasan->id_sobat) : $penugasan->id_sobat);

            $lokasi = $penugasan->nama_tempat_tujuan ?? $penugasan->tujuan_penugasan ?? '-';
            $link = rtrim(config('app.url'), '/') . '/a';

            $message = "*Surat Tugas Ditolak | SiPEDAS*\n\n" .
                "Halo, *{$namaPengaju}*,\n" .
                "Mohon maaf, pengajuan Surat Tugas untuk kegiatan *'{$namaKegiatan}'* (Tertugas: {$namaTertugas}, Tujuan: {$lokasi}) telah *ditolak* oleh *{$namaPemeriksa}*.\n\n" .
                "Silakan periksa detailnya melalui tautan berikut:\n" .
                "{$link}\n\n" .
                "Terima kasih.";

            WhatsappNotifier::send($targetWa, $message, 'whatsapp_notify_st_ditolak');
        } catch (Throwable $e) {
            Log::error('SuratTugasNotificationService: Gagal mengirim notifikasi ditolak: ' . $e->getMessage(), [
                'penugasan_id' => $penugasan->id,
                'exception' => $e,
            ]);
        }
    }
}
