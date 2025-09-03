<?php

namespace App\Services\Sipancong;

use App\Models\Sipancong\Pengajuan;
use App\Models\User;
use App\Services\WhatsappNotifier;
use App\Supports\SipancongConstants as Constants; // Ganti nama alias jika perlu
use Filament\Notifications\Notification;
use Throwable;

class PengajuanServices
{
    // =========================================================================
    // HELPER UNTUK MODE TESTING WHATSAPP
    // =========================================================================

    /**
     * Menentukan nomor WA tujuan berdasarkan mode testing.
     * Jika mode testing aktif (dari .env), akan mengembalikan nomor WA user tes.
     * Jika tidak, akan mengembalikan nomor WA asli.
     *
     * @param string|null $originalTargetWa Nomor WA asli tujuan.
     * @return string|null Nomor WA final yang akan dikirimi pesan.
     */
    private static function getWhatsappTargetNumber(?string $originalTargetWa): ?string
    {
        // Cek toggle dari config yang membaca file .env.
        if (config('app.whatsapp_test_mode')) {
            $testUser = User::getTestUser(); // Menggunakan metode baru di model User
            // Jika user tes ditemukan, gunakan nomor WA-nya.
            if ($testUser && $testUser->pegawai?->nomor_wa) {
                return $testUser->pegawai->nomor_wa;
            }
            // Jika mode tes aktif tapi user tes tidak ditemukan, jangan kirim ke mana pun.
            return null;
        }

        // Jika mode tes tidak aktif, kembalikan nomor asli.
        return $originalTargetWa;
    }


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
        if (!$userPpk) return;

        $namaPanggilanPpk = $userPpk->pegawai?->panggilan ?? 'Bapak/Ibu';
        $namaPengaju = $record->pengaju?->panggilan ?? 'Pengaju';
        $linkKeAksi = config("app.url") . "/a/sipancong/pengajuans?activeTab=PPK";

        $message = "*Pengajuan Baru | DOKTER-V* \n \nHalo, $namaPanggilanPpk \nAda pengajuan pembayaran baru dari *$namaPengaju* nih! \n\nUraian: {$record->uraian_pengajuan} \nNominal: " . self::toRupiah($record->nominal_pengajuan) . " \n\nBuka link ini untuk memeriksa dan melakukan *Aksi PPK* ya:\n\n$linkKeAksi\n\nSemangat!!";

        // MODIFIKASI: Gunakan helper untuk menentukan target WA
        $targetWa = self::getWhatsappTargetNumber($userPpk->pegawai?->nomor_wa);
        if ($targetWa) {
            WhatsappNotifier::send($targetWa, $message);
        }
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
            self::tanggapiNotifier($record, $data); // $data dibutuhkan untuk tanggapan terbaru

            Notification::make()->success()->title("Berhasil memberi tanggapan")->send();
        } catch (Throwable $th) {
            Notification::make()->danger()->title("Tanggapan gagal: " . $th->getMessage())->send();
        }
    }

    private static function tanggapiNotifier(Pengajuan $record, array $data)
    {
        $pengaju = $record->pengaju?->panggilan ?? 'Pengaju';
        $message = "";
        $originalTargetWa = null;
        $link = "";
        $pemeriksa = "";
        $jabatan = "";
        $catatanSebelumnya = "";
        $tanggapanPengaju = "";

        if ($record->posisi_dokumen_id == Constants::POSISI_PPK) {
            $userPemeriksa = User::getPpk()->first();
            $pemeriksa = $userPemeriksa?->pegawai?->panggilan ?? 'PPK';
            $jabatan = "PPK";
            $originalTargetWa = $userPemeriksa?->pegawai?->nomor_wa;
            $catatanSebelumnya = $record->catatan_ppk;
            $tanggapanPengaju = $data['tanggapan_pengaju_ke_ppk'] ?? $record->tanggapan_pengaju_ke_ppk;
            $link = config("app.url") . "/a/sipancong/pengajuans?activeTab=PPK";
        } else if ($record->posisi_dokumen_id == Constants::POSISI_PPSPM) {
            $userPemeriksa = User::getPpspm()->first();
            $pemeriksa = $userPemeriksa?->pegawai?->panggilan ?? 'PPSPM';
            $jabatan = "PPSPM";
            $originalTargetWa = $userPemeriksa?->pegawai?->nomor_wa;
            $catatanSebelumnya = $record->catatan_ppspm;
            $tanggapanPengaju = $data['tanggapan_pengaju_ke_ppspm'] ?? $record->tanggapan_pengaju_ke_ppspm;
            $link = config("app.url") . "/a/sipancong/pengajuans?activeTab=PPSPM";
        } else if ($record->posisi_dokumen_id == Constants::POSISI_BENDAHARA) {
            $userPemeriksa = User::getBendahara()->first();
            $pemeriksa = $userPemeriksa?->pegawai?->panggilan ?? 'Bendahara';
            $jabatan = "Bendahara";
            $originalTargetWa = $userPemeriksa?->pegawai?->nomor_wa;
            $catatanSebelumnya = $record->catatan_bendahara;
            $tanggapanPengaju = $data['tanggapan_pengaju_ke_bendahara'] ?? $record->tanggapan_pengaju_ke_bendahara;
            $link = config("app.url") . "/a/sipancong/pengajuans?activeTab=Bendahara";
        }

        // MODIFIKASI: Gunakan helper untuk menentukan target WA
        $targetWa = self::getWhatsappTargetNumber($originalTargetWa);
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
        $originalTargetWa = null;
        $message = "";

        $pemeriksaUser = ($role == 'ppk') ? User::getPpk()->first() : (($role == 'ppspm') ? User::getPpspm()->first() : User::getBendahara()->first());
        $namaPemeriksa = $pemeriksaUser?->pegawai?->panggilan ?? strtoupper($role);

        if ($statusId == Constants::STATUS_DITOLAK) {
            $originalTargetWa = $record->pengaju?->nomor_wa;
            $link = config("app.url") . "/a/sipancong/pengajuans?activeTab=Pengaju";
            $message = "*Perlu Perbaikan | DOKTER-V* \n\nHalo, *$pengaju*,\nPengajuanmu '$uraian' (Nominal: $nominal) telah diperiksa oleh *$namaPemeriksa* sebagai " . strtoupper($role) . ".\n\nCatatan dari beliau:\n\"*$catatan*\"\n\nSilakan buka link berikut untuk melakukan *Perbaikan* atau memberi *Tanggapan*:\n\n$link\n\nSemangat!!";
        } elseif (Constants::isDisetujui($statusId)) {
            $catatanTambahan = "";
            if ($statusId == Constants::STATUS_DISETUJUI_DENGAN_CATATAN) {
                $catatanTambahan = "\n\n*Dengan Catatan*:\n\"*$catatan*\"";
            }

            if ($role == 'ppk') {
                $userPenerima = User::getPpspm()->first();
                $namaPenerima = $userPenerima?->pegawai?->panggilan ?? 'PPSPM';
                $originalTargetWa = $userPenerima?->pegawai?->nomor_wa;
                $link = config("app.url") . "/a/sipancong/pengajuans?activeTab=PPSPM";
                $message = "*Pengajuan Baru | DOKTER-V* \n\nHalo, $namaPenerima,\nAda pengajuan dari *$pengaju* yang sudah disetujui PPK.\n\nUraian: $uraian\nNominal: $nominal" . $catatanTambahan . "\n\nSilakan lanjutkan proses dengan *Aksi PPSPM*:\n\n$link\n\nSemangat!";
            } elseif ($role == 'ppspm') {
                $userPenerima = User::getBendahara()->first();
                $namaPenerima = $userPenerima?->pegawai?->panggilan ?? 'Bendahara';
                $originalTargetWa = $userPenerima?->pegawai?->nomor_wa;
                $link = config("app.url") . "/a/sipancong/pengajuans?activeTab=Bendahara";
                $message = "*Pengajuan Baru | DOKTER-V* \n\nHalo, $namaPenerima,\nAda pengajuan dari *$pengaju* yang sudah disetujui PPSPM.\n\nUraian: $uraian\nNominal: $nominal" . $catatanTambahan . "\n\nSilakan lanjutkan proses dengan *Aksi Bendahara*:\n\n$link\n\nSemangat!";
            } elseif ($role == 'bendahara') {
                $originalTargetWa = $record->pengaju?->nomor_wa;
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

        // MODIFIKASI: Gunakan helper untuk menentukan target WA
        $targetWa = self::getWhatsappTargetNumber($originalTargetWa);
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
            if (Constants::isSelesaiDibayar($statusPembayaranId)) {
                $data['posisi_dokumen_id'] = Constants::POSISI_SELESAI;
            } else {
                $data['posisi_dokumen_id'] = Constants::POSISI_BENDAHARA;
            }

            $record->update($data);

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
        $originalTargetWa = $record->pengaju?->nomor_wa;
        $uraian = $record->uraian_pengajuan;
        $nominal = self::toRupiah($record->nominal_dibayarkan ?? $record->nominal_pengajuan);

        // MODIFIKASI: Gunakan helper untuk menentukan target WA
        $targetWa = self::getWhatsappTargetNumber($originalTargetWa);
        if ($targetWa) {
            $message = "*Pengajuan Cair! | DOKTER-V* \n \nHalo, *$namaPengaju*,\nPengajuanmu dengan uraian '$uraian' sebesar *$nominal* sudah dicairkan oleh Bendahara!\n\nTerima kasih sudah menggunakan Dokter-V untuk memperlancar proses pembayaran ini.\n\nSemangat!!";
            WhatsappNotifier::send($targetWa, $message);
        }
    }

    // =========================================================================
    // FUNGSI LAIN-LAIN (TIDAK ADA PERUBAHAN DI SINI)
    // =========================================================================

    // ... (Sisa kode dari ubahPengajuan sampai akhir tidak perlu diubah) ...
    public static function ubahPengajuan(array $data, Pengajuan $record)
    {
        try {
            $record->update($data);
            Notification::make()->success()->title("Berhasil menyimpan perubahan")->send();
        } catch (Throwable $th) {
            Notification::make()->danger()->title("Gagal menyimpan perubahan: " . $th->getMessage())->send();
        }
    }

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
        return
            $record->posisi_dokumen_id == Constants::POSISI_BENDAHARA &&
            !self::areAllVerificationsApproved($record);
    }

    public static function canShowBendaharaPaymentAction(Pengajuan $record): bool
    {
        return
            $record->posisi_dokumen_id == Constants::POSISI_BENDAHARA &&
            self::areAllVerificationsApproved($record);
    }

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

    public static function rawPerluAksiBendahara(): string
    {
        return "posisi_dokumen_id = " . Constants::POSISI_BENDAHARA;
    }

    public static function rawPerluPemeriksaanBendahara(): string
    {
        return "posisi_dokumen_id = " . Constants::POSISI_BENDAHARA . " AND ( " .
            "status_pengajuan_ppk_id NOT IN (" . Constants::STATUS_DISETUJUI_DENGAN_CATATAN . "," . Constants::STATUS_DISETUJUI_TANPA_CATATAN . ") OR " .
            "status_pengajuan_ppspm_id NOT IN (" . Constants::STATUS_DISETUJUI_DENGAN_CATATAN . "," . Constants::STATUS_DISETUJUI_TANPA_CATATAN . ") OR " .
            "status_pengajuan_bendahara_id NOT IN (" . Constants::STATUS_DISETUJUI_DENGAN_CATATAN . "," . Constants::STATUS_DISETUJUI_TANPA_CATATAN . ") )";
    }

    public static function rawPerluProsesBendahara(): string
    {
        return "posisi_dokumen_id = " . Constants::POSISI_BENDAHARA . " AND " .
            "status_pengajuan_ppk_id IN (" . Constants::STATUS_DISETUJUI_DENGAN_CATATAN . "," . Constants::STATUS_DISETUJUI_TANPA_CATATAN . ") AND " .
            "status_pengajuan_ppspm_id IN (" . Constants::STATUS_DISETUJUI_DENGAN_CATATAN . "," . Constants::STATUS_DISETUJUI_TANPA_CATATAN . ") AND " .
            "status_pengajuan_bendahara_id IN (" . Constants::STATUS_DISETUJUI_DENGAN_CATATAN . "," . Constants::STATUS_DISETUJUI_TANPA_CATATAN . ")";
    }

    public static function rawSelesai(): string
    {
        return "posisi_dokumen_id = " . Constants::POSISI_SELESAI;
    }

    public static function jumlahPerluPemeriksaanBendahara(): int
    {
        return Pengajuan::where('posisi_dokumen_id', Constants::POSISI_BENDAHARA)
            ->where(function ($query) {
                $query->where('status_pengajuan_ppk_id', '!=', Constants::STATUS_DISETUJUI_TANPA_CATATAN)
                    ->where('status_pengajuan_ppk_id', '!=', Constants::STATUS_DISETUJUI_DENGAN_CATATAN)
                    ->orWhere('status_pengajuan_ppspm_id', '!=', Constants::STATUS_DISETUJUI_TANPA_CATATAN)
                    ->orWhere('status_pengajuan_ppspm_id', '!=', Constants::STATUS_DISETUJUI_DENGAN_CATATAN)
                    ->orWhereNull('status_pengajuan_bendahara_id');
            })
            ->count();
    }

    public static function jumlahPerluProsesBendahara(): int
    {
        return Pengajuan::where('posisi_dokumen_id', Constants::POSISI_BENDAHARA)
            ->whereIn('status_pengajuan_ppk_id', [Constants::STATUS_DISETUJUI_TANPA_CATATAN, Constants::STATUS_DISETUJUI_DENGAN_CATATAN])
            ->whereIn('status_pengajuan_ppspm_id', [Constants::STATUS_DISETUJUI_TANPA_CATATAN, Constants::STATUS_DISETUJUI_DENGAN_CATATAN])
            ->whereIn('status_pengajuan_bendahara_id', [Constants::STATUS_DISETUJUI_TANPA_CATATAN, Constants::STATUS_DISETUJUI_DENGAN_CATATAN])
            ->count();
    }

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
