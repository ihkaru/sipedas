<?php

namespace App\Services\Sipancong;

use App\Models\Pegawai;
use App\Models\Sipancong\Pengajuan;
use App\Models\User;
use App\Services\WhatsappNotifier;
use App\Supports\SipancongConstants as Constants; // Ganti nama alias jika perlu
use Filament\Notifications\Notification;
use Throwable;

class PengajuanServices
{
    public static function toRupiah($angka)
    {
        return "Rp " . number_format($angka, 0, ',', '.');
    }

    // =========================================================================
    // PROSES PENGAJUAN AWAL
    // =========================================================================

    public static function ajukan(array $data)
    {
        try {
            $last_pengajuan = Pengajuan::whereBetween("tanggal_pengajuan", [now()->startOfYear(), now()->endOfYear()])
                ->orderBy('tanggal_pengajuan', "desc")->first();

            $data["nomor_pengajuan"] = ($last_pengajuan) ? $last_pengajuan->nomor_pengajuan + 1 : 1;
            $data["posisi_dokumen_id"] = Constants::POSISI_PPK; // Langsung ke PPK
            $data["nip_pengaju"] = auth()->user()->pegawai->nip;
            $pengajuan = Pengajuan::create($data);

            // Kirim Notifikasi
            self::ajukanNotifier($pengajuan);

            Notification::make()->success()->title("Pengajuan berhasil dikirim")->send();
        } catch (Throwable $th) {
            Notification::make()->danger()->title("Pengajuan gagal: " . $th->getMessage())->send();
        }
    }

    private static function ajukanNotifier(Pengajuan $record)
    {
        $userPpk = User::getPpk()->first();
        // $userPpk = User::getPpk()->first();
        if (!$userPpk) return;

        $namaPanggilanPpk = $userPpk->pegawai?->panggilan ?? 'Bapak/Ibu';
        $namaPengaju = $record->pengaju?->panggilan ?? 'Pengaju';
        $linkKeAksi = config("app.url") . "/a/sipancong/pengajuans?activeTab=PPK";

        $message = "*Pengajuan Baru | DOKTER-V* \n \nHalo, $namaPanggilanPpk \nAda pengajuan pembayaran baru dari *$namaPengaju* nih! \n\nUraian: {$record->uraian_pengajuan} \nNominal: " . self::toRupiah($record->nominal_pengajuan) . " \n\nBuka link ini untuk memeriksa dan melakukan *Aksi PPK* ya:\n\n$linkKeAksi\n\nSemangat!!";
        WhatsappNotifier::send($userPpk->pegawai?->nomor_wa, $message);
    }

    // =========================================================================
    // PROSES TANGGAPAN DARI PENGAJU (SETELAH DITOLAK)
    // =========================================================================

    public static function tanggapi(array $data, Pengajuan $record)
    {
        try {
            // Tentukan siapa yang menolak sebelumnya untuk dikembalikan kesana
            if ($record->status_pengajuan_ppk_id == Constants::STATUS_DITOLAK) {
                $data["posisi_dokumen_id"] = Constants::POSISI_PPK;
            } else if ($record->status_pengajuan_ppspm_id == Constants::STATUS_DITOLAK) {
                $data["posisi_dokumen_id"] = Constants::POSISI_PPSPM;
            } else if ($record->status_pengajuan_bendahara_id == Constants::STATUS_DITOLAK) {
                $data["posisi_dokumen_id"] = Constants::POSISI_BENDAHARA;
            }

            $record->update($data);
            self::tanggapiNotifier($record);

            Notification::make()->success()->title("Berhasil memberi tanggapan")->send();
        } catch (Throwable $th) {
            Notification::make()->danger()->title("Tanggapan gagal: " . $th->getMessage())->send();
        }
    }

    private static function tanggapiNotifier(Pengajuan $record)
    {
        $pengaju = $record->pengaju?->panggilan ?? 'Pengaju';
        $message = "";
        $targetWa = null;
        $link = "";
        $pemeriksa = "";
        $jabatan = "";
        $catatanSebelumnya = "";
        $tanggapanPengaju = "";

        if ($record->posisi_dokumen_id == Constants::POSISI_PPK) {
            $userPemeriksa = User::getPpk()->first();
            $pemeriksa = $userPemeriksa?->pegawai?->panggilan ?? 'PPK';
            $jabatan = "PPK";
            $targetWa = $userPemeriksa?->pegawai?->nomor_wa;
            $catatanSebelumnya = $record->catatan_ppk;
            $tanggapanPengaju = $data['tanggapan_pengaju_ke_ppk'] ?? $record->tanggapan_pengaju_ke_ppk;
            $link = config("app.url") . "/a/sipancong/pengajuans?activeTab=PPK";
        } else if ($record->posisi_dokumen_id == Constants::POSISI_PPSPM) {
            $userPemeriksa = User::getPpspm()->first();
            $pemeriksa = $userPemeriksa?->pegawai?->panggilan ?? 'PPSPM';
            $jabatan = "PPSPM";
            $targetWa = $userPemeriksa?->pegawai?->nomor_wa;
            $catatanSebelumnya = $record->catatan_ppspm;
            $tanggapanPengaju = $data['tanggapan_pengaju_ke_ppspm'] ?? $record->tanggapan_pengaju_ke_ppspm;
            $link = config("app.url") . "/a/sipancong/pengajuans?activeTab=PPSPM";
        } else if ($record->posisi_dokumen_id == Constants::POSISI_BENDAHARA) {
            $userPemeriksa = User::getBendahara()->first();
            $pemeriksa = $userPemeriksa?->pegawai?->panggilan ?? 'Bendahara';
            $jabatan = "Bendahara";
            $targetWa = $userPemeriksa?->pegawai?->nomor_wa;
            $catatanSebelumnya = $record->catatan_bendahara;
            $tanggapanPengaju = $data['tanggapan_pengaju_ke_bendahara'] ?? $record->tanggapan_pengaju_ke_bendahara;
            $link = config("app.url") . "/a/sipancong/pengajuans?activeTab=Bendahara";
        }

        if ($targetWa) {
            $message = "*Tanggapan Pengaju | DOKTER-V* \n\nHalo, *$pemeriksa*,\nPengajuan dengan uraian '{$record->uraian_pengajuan}' sudah ditanggapi oleh *$pengaju*.\n\nHasil pemeriksaanmu sebelumnya:\n\"*$catatanSebelumnya*\"\n\nTanggapan dari *$pengaju*:\n\"*$tanggapanPengaju*\"\n\nSilakan periksa kembali dan lanjutkan proses dengan *Aksi $jabatan* di link berikut:\n\n$link\n\nSemangat!";
            WhatsappNotifier::send($targetWa, $message);
        }
    }

    // =========================================================================
    // PROSES PEMERIKSAAN (PPK, PPSPM, BENDAHARA)
    // =========================================================================

    private static function handlePemeriksaan(string $role, array $data, Pengajuan $record)
    {
        try {
            $statusField = "status_pengajuan_{$role}_id";
            $statusId = $data[$statusField] ?? null;

            if (Constants::isDisetujui($statusId)) {
                $nextPosition = ($role == 'ppk') ? Constants::POSISI_PPSPM : (($role == 'ppspm') ? Constants::POSISI_BENDAHARA : Constants::POSISI_BENDAHARA);
                $data['posisi_dokumen_id'] = $nextPosition;
            } elseif ($statusId == Constants::STATUS_DITOLAK) {
                $data['posisi_dokumen_id'] = Constants::POSISI_PENGAJU;
            }
            // Jika status DITUNDA, posisi dokumen tidak berubah (tetap di pemeriksa).

            $record->update($data);
            self::pemeriksaanNotifier($role, $record);

            Notification::make()->success()->title("Hasil pemeriksaan berhasil disimpan")->send();
        } catch (Throwable $th) {
            Notification::make()->danger()->title("Gagal menyimpan hasil pemeriksaan: " . $th->getMessage())->send();
        }
    }

    public static function pemeriksaanPpk(array $data, Pengajuan $record)
    {
        self::handlePemeriksaan('ppk', $data, $record);
    }

    public static function pemeriksaanPpspm(array $data, Pengajuan $record)
    {
        self::handlePemeriksaan('ppspm', $data, $record);
    }

    public static function pemeriksaanBendahara(array $data, Pengajuan $record)
    {
        self::handlePemeriksaan('bendahara', $data, $record);
    }

    private static function pemeriksaanNotifier(string $role, Pengajuan $record)
    {
        $statusField = "status_pengajuan_{$role}_id";
        $catatanField = "catatan_{$role}";
        $statusId = $record->$statusField;
        $catatan = $record->$catatanField;
        $uraian = $record->uraian_pengajuan;
        $nominal = self::toRupiah($record->nominal_pengajuan);
        $pengaju = $record->pengaju?->panggilan ?? 'Pengaju';
        $targetWa = null;
        $message = "";

        $pemeriksaUser = ($role == 'ppk') ? User::getPpk()->first() : (($role == 'ppspm') ? User::getPpspm()->first() : User::getBendahara()->first());
        $namaPemeriksa = $pemeriksaUser?->pegawai?->panggilan ?? strtoupper($role);

        // Jika Ditolak atau Perlu Perbaikan, notif ke Pengaju
        if ($statusId == Constants::STATUS_DITOLAK) {
            $targetWa = $record->pengaju?->nomor_wa;
            $link = config("app.url") . "/a/sipancong/pengajuans?activeTab=Pengaju";
            $message = "*Perlu Perbaikan | DOKTER-V* \n\nHalo, *$pengaju*,\nPengajuanmu '$uraian' (Nominal: $nominal) telah diperiksa oleh *$namaPemeriksa* sebagai " . strtoupper($role) . ".\n\nCatatan dari beliau:\n\"*$catatan*\"\n\nSilakan buka link berikut untuk melakukan *Perbaikan* atau memberi *Tanggapan*:\n\n$link\n\nSemangat!!";
        }
        // Jika Disetujui, notif ke Pemeriksa Selanjutnya
        elseif (Constants::isDisetujui($statusId)) {
            $catatanTambahan = "";
            if ($statusId == Constants::STATUS_DISETUJUI_DENGAN_CATATAN) {
                $catatanTambahan = "\n\n*Dengan Catatan*:\n\"*$catatan*\"";
            }

            if ($role == 'ppk') {
                $userPenerima = User::getPpspm()->first();
                $namaPenerima = $userPenerima?->pegawai?->panggilan ?? 'PPSPM';
                $targetWa = $userPenerima?->pegawai?->nomor_wa;
                $link = config("app.url") . "/a/sipancong/pengajuans?activeTab=PPSPM";
                $message = "*Pengajuan Baru | DOKTER-V* \n\nHalo, $namaPenerima,\nAda pengajuan dari *$pengaju* yang sudah disetujui PPK.\n\nUraian: $uraian\nNominal: $nominal" . $catatanTambahan . "\n\nSilakan lanjutkan proses dengan *Aksi PPSPM*:\n\n$link\n\nSemangat!";
            } elseif ($role == 'ppspm') {
                $userPenerima = User::getBendahara()->first();
                $namaPenerima = $userPenerima?->pegawai?->panggilan ?? 'Bendahara';
                $targetWa = $userPenerima?->pegawai?->nomor_wa;
                $link = config("app.url") . "/a/sipancong/pengajuans?activeTab=Bendahara";
                $message = "*Pengajuan Baru | DOKTER-V* \n\nHalo, $namaPenerima,\nAda pengajuan dari *$pengaju* yang sudah disetujui PPSPM.\n\nUraian: $uraian\nNominal: $nominal" . $catatanTambahan . "\n\nSilakan lanjutkan proses dengan *Aksi Bendahara*:\n\n$link\n\nSemangat!";
            } elseif ($role == 'bendahara') {
                $targetWa = $record->pengaju?->nomor_wa;

                // Cek apakah ada catatan dari pemeriksa manapun
                $pesanFinalCatatan = "";
                if (
                    $record->status_pengajuan_ppk_id == Constants::STATUS_DISETUJUI_DENGAN_CATATAN ||
                    $record->status_pengajuan_ppspm_id == Constants::STATUS_DISETUJUI_DENGAN_CATATAN ||
                    $record->status_pengajuan_bendahara_id == Constants::STATUS_DISETUJUI_DENGAN_CATATAN
                ) {
                    $pesanFinalCatatan = "\n\n*PENTING:*\nWalaupun pengajuan sudah disetujui, masih terdapat beberapa catatan yang perlu Anda penuhi. Pengajuan ini baru dianggap benar-benar selesai setelah semua catatan terpenuhi.";
                }

                $message = "*Pengajuan Selesai Diperiksa! | DOKTER-V* \n\nHalo, *$pengaju*,\nKabar baik! Pengajuanmu '$uraian' (Nominal: $nominal) *sudah disetujui Bendahara*! \n\nSelanjutnya, pengajuanmu akan diproses untuk pembayaran." . $catatanTambahan . "\n\nAku akan kabari lagi kalau dananya sudah dicairkan ya." . $pesanFinalCatatan . "\n\nSemangat!!";
            }
        }
        // Jika ditunda, tidak ada notifikasi yang dikirim.

        if ($targetWa && $message) {
            WhatsappNotifier::send($targetWa, $message);
        }
    }

    // =========================================================================
    // PROSES PEMBAYARAN OLEH BENDAHARA
    // =========================================================================

    public static function pemrosesanBendahara(array $data, Pengajuan $record)
    {
        try {
            $statusPembayaranId = $data['status_pembayaran_id'] ?? null;

            // Tentukan posisi akhir dokumen berdasarkan status pembayaran
            if (Constants::isSelesaiDibayar($statusPembayaranId)) {
                $data['posisi_dokumen_id'] = Constants::POSISI_SELESAI;
            } else {
                // Untuk status seperti "Belum Tersedia Dok Fisik", "Proses Catat", dll.
                // Dokumen masih dianggap aktif dan berada di Bendahara.
                $data['posisi_dokumen_id'] = Constants::POSISI_BENDAHARA;
            }

            $record->update($data);

            // Kirim notifikasi HANYA jika pembayaran sudah selesai (cair)
            if (Constants::isSelesaiDibayar($statusPembayaranId)) {
                self::pemrosesanBendaharaNotifier($record);
            }

            Notification::make()->success()->title("Status pembayaran berhasil diperbarui")->send();
        } catch (Throwable $th) {
            Notification::make()->danger()->title("Gagal menyimpan status pembayaran: " . $th->getMessage())->send();
        }
    }

    private static function pemrosesanBendaharaNotifier(Pengajuan $record)
    {
        $namaPengaju = $record->pengaju?->panggilan ?? 'Pengaju';
        $targetWa = $record->pengaju?->nomor_wa;
        $uraian = $record->uraian_pengajuan;
        $nominal = self::toRupiah($record->nominal_dibayarkan ?? $record->nominal_pengajuan);

        if ($targetWa) {
            $message = "*Pengajuan Cair! | DOKTER-V* \n \nHalo, *$namaPengaju*,\nPengajuanmu dengan uraian '$uraian' sebesar *$nominal* sudah dicairkan oleh Bendahara!\n\nTerima kasih sudah menggunakan Dokter-V untuk memperlancar proses pembayaran ini.\n\nSemangat!!";
            WhatsappNotifier::send($targetWa, $message);
        }
    }

    // =========================================================================
    // FUNGSI LAIN-LAIN (UBAH PENGAJUAN, HELPER QUERY)
    // =========================================================================

    public static function ubahPengajuan(array $data, Pengajuan $record)
    {
        try {
            $record->update($data);
            Notification::make()->success()->title("Berhasil menyimpan perubahan")->send();
        } catch (Throwable $th) {
            Notification::make()->danger()->title("Gagal menyimpan perubahan: " . $th->getMessage())->send();
        }
    }

    // =========================================================================
    // HELPER UNTUK LOGIKA TAMPILAN DI FILAMENT RESOURCE
    // =========================================================================

    public static function canShowPengajuActions(Pengajuan $record): bool
    {
        $user = auth()->user();
        return ($user->pegawai?->nip == $record->nip_pengaju) && $record->posisi_dokumen_id == Constants::POSISI_PENGAJU;
    }

    public static function canShowPpkActions(Pengajuan $record): bool
    {
        return $record->posisi_dokumen_id == Constants::POSISI_PPK;
    }

    public static function canShowPpspmActions(Pengajuan $record): bool
    {
        return $record->posisi_dokumen_id == Constants::POSISI_PPSPM;
    }

    private static function areAllVerificationsApproved(Pengajuan $record): bool
    {
        return Constants::isDisetujui($record->status_pengajuan_ppk_id) &&
            Constants::isDisetujui($record->status_pengajuan_ppspm_id) &&
            Constants::isDisetujui($record->status_pengajuan_bendahara_id);
    }

    public static function canShowBendaharaVerificationAction(Pengajuan $record): bool
    {
        // Tampilkan aksi verifikasi jika dokumen ada di Bendahara DAN belum semua persetujuan verifikasi didapat
        return
            $record->posisi_dokumen_id == Constants::POSISI_BENDAHARA &&
            !self::areAllVerificationsApproved($record);
    }

    public static function canShowBendaharaPaymentAction(Pengajuan $record): bool
    {
        // Tampilkan aksi pembayaran jika dokumen ada di Bendahara DAN SEMUA persetujuan verifikasi sudah didapat
        return
            $record->posisi_dokumen_id == Constants::POSISI_BENDAHARA &&
            self::areAllVerificationsApproved($record);
    }

    // Helper untuk badge di navigasi Filament (disarankan berbasis posisi dokumen)
    public static function jumlahPerluPerbaikanPengaju()
    {
        return Pengajuan::where('posisi_dokumen_id', Constants::POSISI_PENGAJU)->count();
    }
    public static function jumlahPerluPemeriksaanPpk()
    {
        return Pengajuan::where('posisi_dokumen_id', Constants::POSISI_PPK)->count();
    }
    public static function jumlahPerluPemeriksaanPpspm()
    {
        return Pengajuan::where('posisi_dokumen_id', Constants::POSISI_PPSPM)->count();
    }
    public static function jumlahPerluPemeriksaanAtauProsesBendahara()
    {
        return Pengajuan::where('posisi_dokumen_id', Constants::POSISI_BENDAHARA)->count();
    }

    // RAW Queries untuk tab di resource (jika masih diperlukan)
    public static function rawPerluPerbaikanPengaju(): string
    {
        return "posisi_dokumen_id = " . Constants::POSISI_PENGAJU;
    }

    public static function rawPerluPemeriksaanPpk(): string
    {
        return "posisi_dokumen_id = " . Constants::POSISI_PPK;
    }

    public static function rawPerluPemeriksaanPpspm(): string
    {
        return "posisi_dokumen_id = " . Constants::POSISI_PPSPM;
    }

    /**
     * Raw query untuk semua item yang memerlukan aksi dari Bendahara
     * (baik verifikasi maupun pembayaran).
     */
    public static function rawPerluAksiBendahara(): string
    {
        return "posisi_dokumen_id = " . Constants::POSISI_BENDAHARA;
    }

    /**
     * Raw query untuk item yang perlu DIVERIFIKASI Bendahara.
     * Logika ini lebih kompleks dan sebaiknya dihindari jika bisa,
     * gunakan rawPerluAksiBendahara() yang lebih sederhana.
     */
    public static function rawPerluPemeriksaanBendahara(): string
    {
        // Query ini akan mencari dokumen di posisi Bendahara yang belum disetujui oleh semua pihak sebelumnya.
        // Ini adalah implementasi yang lebih 'aman' untuk memastikan hanya verifikasi yang muncul.
        return "posisi_dokumen_id = " . Constants::POSISI_BENDAHARA . " AND ( " .
            "status_pengajuan_ppk_id NOT IN (" . Constants::STATUS_DISETUJUI_DENGAN_CATATAN . "," . Constants::STATUS_DISETUJUI_TANPA_CATATAN . ") OR " .
            "status_pengajuan_ppspm_id NOT IN (" . Constants::STATUS_DISETUJUI_DENGAN_CATATAN . "," . Constants::STATUS_DISETUJUI_TANPA_CATATAN . ") OR " .
            "status_pengajuan_bendahara_id NOT IN (" . Constants::STATUS_DISETUJUI_DENGAN_CATATAN . "," . Constants::STATUS_DISETUJUI_TANPA_CATATAN . ") )";
    }

    /**
     * Raw query untuk item yang perlu DIPROSES PEMBAYARANNYA oleh Bendahara.
     */
    public static function rawPerluProsesBendahara(): string
    {
        return "posisi_dokumen_id = " . Constants::POSISI_BENDAHARA . " AND " .
            "status_pengajuan_ppk_id IN (" . Constants::STATUS_DISETUJUI_DENGAN_CATATAN . "," . Constants::STATUS_DISETUJUI_TANPA_CATATAN . ") AND " .
            "status_pengajuan_ppspm_id IN (" . Constants::STATUS_DISETUJUI_DENGAN_CATATAN . "," . Constants::STATUS_DISETUJUI_TANPA_CATATAN . ") AND " .
            "status_pengajuan_bendahara_id IN (" . Constants::STATUS_DISETUJUI_DENGAN_CATATAN . "," . Constants::STATUS_DISETUJUI_TANPA_CATATAN . ")";
    }

    /**
     * Raw query untuk semua item yang sudah selesai.
     */
    public static function rawSelesai(): string
    {
        return "posisi_dokumen_id = " . Constants::POSISI_SELESAI;
    }



    // --- METODE BARU YANG DIBUTUHKAN WIDGET ---

    /**
     * Menghitung jumlah pengajuan yang perlu diverifikasi oleh Bendahara.
     * Yaitu, yang posisinya di Bendahara TAPI belum semua verifikasi disetujui.
     */
    public static function jumlahPerluPemeriksaanBendahara(): int
    {
        return Pengajuan::where('posisi_dokumen_id', Constants::POSISI_BENDAHARA)
            ->where(function ($query) {
                $query->where('status_pengajuan_ppk_id', '!=', Constants::STATUS_DISETUJUI_TANPA_CATATAN)
                    ->where('status_pengajuan_ppk_id', '!=', Constants::STATUS_DISETUJUI_DENGAN_CATATAN)
                    ->orWhere('status_pengajuan_ppspm_id', '!=', Constants::STATUS_DISETUJUI_TANPA_CATATAN)
                    ->orWhere('status_pengajuan_ppspm_id', '!=', Constants::STATUS_DISETUJUI_DENGAN_CATATAN)
                    ->orWhereNull('status_pengajuan_bendahara_id'); // Belum diperiksa bendahara
            })
            ->count();
    }

    /**
     * Menghitung jumlah pengajuan yang siap diproses pembayarannya oleh Bendahara.
     * Yaitu, yang posisinya di Bendahara DAN SEMUA verifikasi sudah disetujui.
     */
    public static function jumlahPerluProsesBendahara(): int
    {
        return Pengajuan::where('posisi_dokumen_id', Constants::POSISI_BENDAHARA)
            ->whereIn('status_pengajuan_ppk_id', [Constants::STATUS_DISETUJUI_TANPA_CATATAN, Constants::STATUS_DISETUJUI_DENGAN_CATATAN])
            ->whereIn('status_pengajuan_ppspm_id', [Constants::STATUS_DISETUJUI_TANPA_CATATAN, Constants::STATUS_DISETUJUI_DENGAN_CATATAN])
            ->whereIn('status_pengajuan_bendahara_id', [Constants::STATUS_DISETUJUI_TANPA_CATATAN, Constants::STATUS_DISETUJUI_DENGAN_CATATAN])
            ->count();
    }

    /**
     * Menghitung persentase penyelesaian pengajuan per subfungsi.
     */
    public static function jumlahSelesaiSubfungsi(string $namaSubfungsi): int
    {
        $query = Pengajuan::whereHas("subfungsi", function ($q) use ($namaSubfungsi) {
            $q->where("nama", $namaSubfungsi);
        });

        $total = (clone $query)->count();
        if ($total === 0) {
            return 0;
        }

        $selesai = (clone $query)->where('posisi_dokumen_id', Constants::POSISI_SELESAI)->count();

        return round(($selesai / $total) * 100);
    }
}
