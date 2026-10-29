<?php

namespace App\Services\SuratTugas;

use App\Models\Pegawai;
use App\Models\Pengaturan;
use App\Models\Penugasan;
use App\Models\Setting;
use App\Models\User;
use App\Services\WhatsappNotifier;
use App\Supports\Constants;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Throwable;

class SuratTugasNotificationService
{
    /**
     * Kirim notifikasi pengajuan Surat Tugas baru kepada Penyetuju (Kepala Satker dan/atau PPSPM/Kasubbag TU).
     *
     * @param  array|Collection|Penugasan  $penugasans  Kumpulan atau record penugasan dalam satu pengajuan.
     */
    public static function notifyPengajuanBaru($penugasans): void
    {
        try {
            $list = is_array($penugasans) ? collect($penugasans) : (
                $penugasans instanceof Collection ? $penugasans : collect([$penugasans])
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

            $kasubbagNip = Pengaturan::getValue('NIP_KASUBBAG_TU', config('satker.pejabat.kasubbag_tu_nip', '198008112005021004'));
            $kepalaNip = Pengaturan::getValue('ID_PLH_DEFAULT', config('satker.pejabat.kepala_nip', '196908031992111001'));

            if ($mode === 'delegasi_ppspm' || $mode === 'ppspm_only') {
                $ppspmUsers = User::getPpspm();
                $foundPpspm = false;
                foreach ($ppspmUsers as $u) {
                    $peg = $u->pegawai;
                    $noWa = $peg?->nomor_wa;
                    if (! empty($noWa)) {
                        $foundPpspm = true;
                        $recipients->put($noWa, [
                            'nama' => $peg->panggilan ?? $peg->nama,
                            'nip' => $peg->nip,
                            'role' => 'Kasubbag TU / PPSPM',
                        ]);
                    }
                }

                // Fallback: Jika tidak ditemukan via Spatie role, gunakan NIP Kasubbag TU definitif
                if (! $foundPpspm && ! empty($kasubbagNip)) {
                    $peg = Pegawai::where('nip', $kasubbagNip)->first();
                    if ($peg && ! empty($peg->nomor_wa)) {
                        $recipients->put($peg->nomor_wa, [
                            'nama' => $peg->panggilan ?? $peg->nama,
                            'nip' => $peg->nip,
                            'role' => 'Kasubbag TU / PPSPM',
                        ]);
                    }
                }
            }

            $tembusanKepala = (bool) (Setting::where('key', 'whatsapp_notify_st_tembusan_kepala')->value('value') ?? false);
            if ($mode === 'kepala_only' || ($mode === 'delegasi_ppspm' && $tembusanKepala)) {
                // Cari PLH jika terdefinisi di penugasan
                if (! empty($first->plh_id)) {
                    $plhPegawai = Pegawai::where('nip', $first->plh_id)->first();
                    if ($plhPegawai && ! empty($plhPegawai->nomor_wa)) {
                        $recipients->put($plhPegawai->nomor_wa, [
                            'nama' => $plhPegawai->panggilan ?? $plhPegawai->nama,
                            'nip' => $plhPegawai->nip,
                            'role' => 'PLH Kepala',
                        ]);
                    }
                }

                // Kepala Satker
                $kepalaUsers = User::getKepalaSatker();
                $foundKepala = false;
                foreach ($kepalaUsers as $u) {
                    $peg = $u->pegawai;
                    $noWa = $peg?->nomor_wa;
                    if (! empty($noWa)) {
                        $foundKepala = true;
                        $recipients->put($noWa, [
                            'nama' => $peg->panggilan ?? $peg->nama,
                            'nip' => $peg->nip,
                            'role' => 'Kepala Satker',
                        ]);
                    }
                }

                // Fallback: Jika belum ditemukan via Spatie role, gunakan NIP Kepala Satker definitif
                if (! $foundKepala && ! empty($kepalaNip)) {
                    $peg = Pegawai::where('nip', $kepalaNip)->first();
                    if ($peg && ! empty($peg->nomor_wa)) {
                        $recipients->put($peg->nomor_wa, [
                            'nama' => $peg->panggilan ?? $peg->nama,
                            'nip' => $peg->nip,
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
                    $nama = ! empty($p->nip)
                        ? ($p->pegawai ? ($p->pegawai->nama ?? $p->nip) : $p->nip)
                        : ($p->mitra ? ($p->mitra->nama_1 ?? $p->id_sobat) : $p->id_sobat);
                    $tipe = ! empty($p->nip) ? 'PNS' : 'Mitra';
                    $personilList[] = "- {$nama} ({$tipe})";
                }
            }
            $personilStr = ! empty($personilList) ? implode("\n", array_slice($personilList, 0, 8)) : '-';
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
            $linkAksi = rtrim(config('app.url'), '/').'/a/penugasans';

            foreach ($recipients as $targetWa => $info) {
                $namaPenyetuju = $info['nama'];
                $approverNip = $info['nip'] ?? null;
                $linkQuickApprove = URL::temporarySignedRoute(
                    'surat-tugas.quick-approve',
                    now()->addDays(14),
                    [
                        'grup_id' => $first->grup_id,
                        'nip' => $approverNip,
                    ]
                );

                $message = "*Pengajuan Surat Tugas Baru | Dokter V*\n\n".
                    "Halo, Bapak/Ibu *{$namaPenyetuju}*,\n".
                    "Ada pengajuan Surat Tugas baru dari *{$namaPengaju}* yang memerlukan persetujuan:\n\n".
                    "*Kegiatan*: {$namaKegiatan}\n".
                    "*Jenis*: {$jenisSt}\n".
                    "*Tujuan*: {$lokasi}\n".
                    "*Tanggal*: {$periode}\n".
                    "*Personil Tertugas*:\n{$personilStr}\n\n".
                    "⚡ *Persetujuan Cepat (1-Klik)*:\n".
                    "Klik tautan ini untuk langsung menyetujui tanpa perlu login:\n".
                    "{$linkQuickApprove}\n\n".
                    "📋 *Buka Detail di Dokter V*:\n".
                    "{$linkAksi}\n\n".
                    'Semangat bertugas!';

                WhatsappNotifier::send($targetWa, $message, 'whatsapp_notify_st_baru');
            }
        } catch (Throwable $e) {
            Log::error('SuratTugasNotificationService: Gagal mengirim notifikasi pengajuan baru: '.$e->getMessage(), [
                'exception' => $e,
            ]);
        }
    }

    /**
     * Kirim notifikasi ringkasan Surat Tugas Non-SPPD yang telah disetujui otomatis oleh sistem kepada Kasubbag TU.
     *
     * @param  Collection|array  $createdPenugasans
     */
    public static function notifyNonSppdAutoApproved($createdPenugasans): void
    {
        try {
            $list = collect($createdPenugasans)->filter(
                fn ($p) => $p instanceof Penugasan && $p->jenis_surat_tugas === Constants::NON_SPPD
            );

            if ($list->isEmpty()) {
                return;
            }

            /** @var Penugasan $first */
            $first = $list->first();
            $first->loadMissing(['pengaju', 'kegiatan', 'pegawai', 'mitra', 'suratTugas', 'riwayatPengajuan']);

            // 1. Tentukan target penerima: HANYA Kasubbag TU / PPSPM
            $recipients = collect();
            $kasubbagNip = Pengaturan::getValue('NIP_KASUBBAG_TU', config('satker.pejabat.kasubbag_tu_nip', '198008112005021004'));

            $ppspmUsers = User::getPpspm();
            $foundPpspm = false;
            foreach ($ppspmUsers as $u) {
                $peg = $u->pegawai;
                $noWa = $peg?->nomor_wa;
                if (! empty($noWa)) {
                    $foundPpspm = true;
                    $recipients->put($noWa, [
                        'nama' => $peg->panggilan ?? $peg->nama,
                        'nip' => $peg->nip,
                        'role' => 'Kasubbag TU / PPSPM',
                    ]);
                }
            }

            // Fallback: Jika tidak ditemukan via Spatie role, gunakan NIP Kasubbag TU definitif
            if (! $foundPpspm && ! empty($kasubbagNip)) {
                $peg = Pegawai::where('nip', $kasubbagNip)->first();
                if ($peg && ! empty($peg->nomor_wa)) {
                    $recipients->put($peg->nomor_wa, [
                        'nama' => $peg->panggilan ?? $peg->nama,
                        'nip' => $peg->nip,
                        'role' => 'Kasubbag TU / PPSPM',
                    ]);
                }
            }

            if ($recipients->isEmpty()) {
                Log::info('SuratTugasNotificationService: Tidak ada target nomor WhatsApp Kasubbag TU yang ditemukan untuk info Non-SPPD.', [
                    'penugasan_id' => $first->id,
                ]);

                return;
            }

            // 2. Susun ringkasan personil tertugas
            $personilList = [];
            foreach ($list as $p) {
                if ($p instanceof Penugasan) {
                    $p->loadMissing(['pegawai', 'mitra']);
                    $nama = ! empty($p->nip)
                        ? ($p->pegawai ? ($p->pegawai->nama ?? $p->nip) : $p->nip)
                        : ($p->mitra ? ($p->mitra->nama_1 ?? $p->id_sobat) : $p->id_sobat);
                    $tipe = ! empty($p->nip) ? 'PNS' : 'Mitra';
                    $personilList[] = "- {$nama} ({$tipe})";
                }
            }
            $personilStr = ! empty($personilList) ? implode("\n", array_slice($personilList, 0, 8)) : '-';
            if (count($personilList) > 8) {
                $sisa = count($personilList) - 8;
                $personilStr .= "\n- ...dan {$sisa} orang lainnya";
            }

            $namaPengaju = $first->pengaju ? ($first->pengaju->panggilan ?? $first->pengaju->nama) : 'Pengaju';
            $namaKegiatan = $first->kegiatan ? $first->kegiatan->nama : 'Kegiatan Dinas';
            $noSt = $first->suratTugas ? ($first->suratTugas->nomor_surat_tugas ?? '-') : '-';
            $lokasi = $first->nama_tempat_tujuan ?? $first->tujuan_penugasan ?? '-';
            $tglMulai = $first->tgl_mulai_tugas ? Carbon::parse($first->tgl_mulai_tugas)->translatedFormat('d M Y') : '-';
            $tglAkhir = $first->tgl_akhir_tugas ? Carbon::parse($first->tgl_akhir_tugas)->translatedFormat('d M Y') : '-';
            $periode = ($tglMulai === $tglAkhir) ? $tglMulai : "{$tglMulai} s.d. {$tglAkhir}";
            $linkAksi = rtrim(config('app.url'), '/').'/a/penugasans';

            foreach ($recipients as $targetWa => $info) {
                $namaKasubbag = $info['nama'];

                $message = "*Pemberitahuan Surat Tugas Non-SPPD | Dokter V*\n\n".
                    "Halo, Bapak/Ibu *{$namaKasubbag}*,\n".
                    "Informasi: Terdapat Surat Tugas *Non-SPPD* baru dari *{$namaPengaju}* yang telah diterbitkan dan *disetujui otomatis* oleh sistem:\n\n".
                    "*Kegiatan*: {$namaKegiatan}\n".
                    "*Jenis*: Non SPPD\n".
                    "*No. ST*: {$noSt}\n".
                    "*Tujuan*: {$lokasi}\n".
                    "*Tanggal*: {$periode}\n".
                    "*Personil Tertugas*:\n{$personilStr}\n\n".
                    "📋 *Buka Detail di Dokter V*:\n".
                    "{$linkAksi}\n\n".
                    '_Pesan ini bersifat pemberitahuan sistem (auto-approved)._';

                WhatsappNotifier::send($targetWa, $message, 'whatsapp_notify_st_baru');
            }
        } catch (Throwable $e) {
            Log::error('SuratTugasNotificationService: Gagal mengirim notifikasi info Non-SPPD: '.$e->getMessage(), [
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

            if (! empty($approverName)) {
                $namaPenyetuju = $approverName;
            } elseif (
                Setting::where('key', 'st_approval_mode')->value('value') === 'auto_approve' ||
                ($penugasan->jenis_surat_tugas === Constants::NON_SPPD && in_array(Setting::get('st_non_sppd_mode', 'auto_approve_notify'), ['auto_approve_notify', 'auto_approve_silent']))
            ) {
                $namaPenyetuju = 'Sistem (Otomatis)';
            } elseif ($authUser && Penugasan::isUserApprover($authUser, $penugasan->plh_id)) {
                $namaPenyetuju = $authPegawai ? ($authPegawai->panggilan ?? $authPegawai->nama) : 'Penyetuju';
            } else {
                $namaPenyetuju = 'Penyetuju (Otoritas Satker)';
            }

            $namaKegiatan = $penugasan->kegiatan ? $penugasan->kegiatan->nama : 'Kegiatan Dinas';
            $namaTertugas = ! empty($penugasan->nip)
                ? ($penugasan->pegawai ? ($penugasan->pegawai->nama ?? $penugasan->nip) : $penugasan->nip)
                : ($penugasan->mitra ? ($penugasan->mitra->nama_1 ?? $penugasan->id_sobat) : $penugasan->id_sobat);

            $noSt = $penugasan->suratTugas ? ($penugasan->suratTugas->nomor_surat_tugas ?? '-') : '-';
            $noSpd = $penugasan->suratPerjadin ? $penugasan->suratPerjadin->nomor_surat_perjadin : null;
            $lineSpd = $noSpd ? "*No. SPD*: {$noSpd}\n" : '';

            $lokasi = $penugasan->nama_tempat_tujuan ?? $penugasan->tujuan_penugasan ?? '-';
            $tglMulai = $penugasan->tgl_mulai_tugas ? Carbon::parse($penugasan->tgl_mulai_tugas)->translatedFormat('d M Y') : '-';
            $tglAkhir = $penugasan->tgl_akhir_tugas ? Carbon::parse($penugasan->tgl_akhir_tugas)->translatedFormat('d M Y') : '-';
            $periode = ($tglMulai === $tglAkhir) ? $tglMulai : "{$tglMulai} s.d. {$tglAkhir}";
            $linkRiwayat = rtrim(config('app.url'), '/').'/a';

            // Kumpulkan nomor WA tujuan (Pengaju dan Personil tertugas jika berbeda)
            $targets = collect();

            if ($penugasan->pengaju && ! empty($penugasan->pengaju->nomor_wa)) {
                $targets->put($penugasan->pengaju->nomor_wa, $penugasan->pengaju->panggilan ?? $penugasan->pengaju->nama);
            }

            $personilPegawai = $penugasan->pegawai;
            $personilMitra = $penugasan->mitra;
            $personilWa = ! empty($penugasan->nip) ? $personilPegawai?->nomor_wa : $personilMitra?->nomor_wa;
            if (! empty($personilWa) && ! $targets->has($personilWa)) {
                $targets->put($personilWa, $personilPegawai ? ($personilPegawai->panggilan ?? $namaTertugas) : $namaTertugas);
            }

            foreach ($targets as $targetWa => $namaPenerima) {
                $message = "*Surat Tugas Disetujui! | Dokter V*\n\n".
                    "Halo, *{$namaPenerima}*,\n".
                    "Kabar baik! Pengajuan Surat Tugas untuk kegiatan *'{$namaKegiatan}'* telah *disetujui* oleh *{$namaPenyetuju}*.\n\n".
                    "*No. Surat Tugas*: {$noSt}\n".
                    $lineSpd.
                    "*Tertugas*: {$namaTertugas}\n".
                    "*Tujuan*: {$lokasi}\n".
                    "*Tanggal*: {$periode}\n\n".
                    "Silakan mencetak Surat Tugas secara mandiri melalui menu Riwayat di Dokter V untuk diserahkan ke TU:\n".
                    "{$linkRiwayat}\n\n".
                    'Semangat bertugas!';

                WhatsappNotifier::send($targetWa, $message, 'whatsapp_notify_st_disetujui');
            }
        } catch (Throwable $e) {
            Log::error('SuratTugasNotificationService: Gagal mengirim notifikasi disetujui: '.$e->getMessage(), [
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
            $namaTertugas = ! empty($penugasan->nip)
                ? ($penugasan->pegawai ? ($penugasan->pegawai->nama ?? $penugasan->nip) : $penugasan->nip)
                : ($penugasan->mitra ? ($penugasan->mitra->nama_1 ?? $penugasan->id_sobat) : $penugasan->id_sobat);

            $link = rtrim(config('app.url'), '/').'/a';

            $message = "*Perlu Perbaikan Surat Tugas | Dokter V*\n\n".
                "Halo, *{$namaPengaju}*,\n".
                "Pengajuan Surat Tugas untuk kegiatan *'{$namaKegiatan}'* (Tertugas: {$namaTertugas}) memerlukan perbaikan dari *{$namaPemeriksa}*.\n\n".
                "*Catatan Arahan Perbaikan*:\n".
                "\"*{$catatan}*\"\n\n".
                "Silakan buka link berikut untuk melakukan perbaikan pengajuan:\n".
                "{$link}\n\n".
                'Semangat!';

            WhatsappNotifier::send($targetWa, $message, 'whatsapp_notify_st_revisi');
        } catch (Throwable $e) {
            Log::error('SuratTugasNotificationService: Gagal mengirim notifikasi revisi: '.$e->getMessage(), [
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
            $namaTertugas = ! empty($penugasan->nip)
                ? ($penugasan->pegawai ? ($penugasan->pegawai->nama ?? $penugasan->nip) : $penugasan->nip)
                : ($penugasan->mitra ? ($penugasan->mitra->nama_1 ?? $penugasan->id_sobat) : $penugasan->id_sobat);

            $lokasi = $penugasan->nama_tempat_tujuan ?? $penugasan->tujuan_penugasan ?? '-';
            $link = rtrim(config('app.url'), '/').'/a';

            $message = "*Surat Tugas Ditolak | Dokter V*\n\n".
                "Halo, *{$namaPengaju}*,\n".
                "Mohon maaf, pengajuan Surat Tugas untuk kegiatan *'{$namaKegiatan}'* (Tertugas: {$namaTertugas}, Tujuan: {$lokasi}) telah *ditolak* oleh *{$namaPemeriksa}*.\n\n".
                "Silakan periksa detailnya melalui tautan berikut:\n".
                "{$link}\n\n".
                'Terima kasih.';

            WhatsappNotifier::send($targetWa, $message, 'whatsapp_notify_st_ditolak');
        } catch (Throwable $e) {
            Log::error('SuratTugasNotificationService: Gagal mengirim notifikasi ditolak: '.$e->getMessage(), [
                'penugasan_id' => $penugasan->id,
                'exception' => $e,
            ]);
        }
    }
}
