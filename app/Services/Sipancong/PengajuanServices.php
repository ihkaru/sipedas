<?php

namespace App\Services\Sipancong;

use App\Models\Pegawai;
use App\Models\Sipancong\Pengajuan;
use App\Models\User;
use App\Services\WhatsappNotifier;
use App\Supports\Constants;
use Filament\Notifications\Notification;

class PengajuanServices
{
    public static function getPosisiPengajuan(array $data)
    {
        // Di PPK
        // Di PPSPM
        // Di Bendahara
    }
    public static function toRupiah($angka)
    {
        return "Rp " . number_format($angka, 0, ',', '.');
    }
    public static function ajukanNotifier(array $data)
    {
        // $userPpk = User::getPpk()->first();
        $userPpk = User::getTestPegawai()->first();
        $pengaju = Pegawai::where("nip", $data["nip_pengaju"])->first();
        $namaPengaju = $pengaju->panggilan;
        $namaPanggilanPpk = $userPpk?->pegawai?->panggilan;
        $uraianPengajuan = $data["uraian_pengajuan"];
        $nominalPengajuan = self::toRupiah($data["nominal_pengajuan"] * 1);
        $targetWa = $userPpk?->pegawai?->nomor_wa;

        // Notifikasi Pengajuan Baru ke PPK
        $message = "*Pengajuan Baru | DOKTER-V* \n \nHalo, $namaPanggilanPpk \nAda pengajuan pembayaran baru dari *$namaPengaju* nih! \n\nUraian: $uraianPengajuan \n\nNominal: $nominalPengajuan \n\nBuka link ini untuk  ngeceknya ya:\n\nhttps://admin.dvlp.asia/a\n\n Semangat!!";
        WhatsappNotifier::send($targetWa, $message);
    }
    public static function ajukan(array $data)
    {
        $last_pengajuan = Pengajuan::whereBetween("tanggal_pengajuan", [now()->startOfYear(), now()->endOfYear()])
            ->orderBy('tanggal_pengajuan', "desc")->first();
        $data["nomor_pengajuan"] = ($last_pengajuan) ? $last_pengajuan->nomor_pengajuan + 1 : 1;
        $data["posisi_dokumen_id"] = 2;
        $data["nip_pengaju"] = auth()->user()->pegawai->nip;
        Pengajuan::create($data);
        self::ajukanNotifier($data);
        Notification::make()
            ->success()
            ->title("Pengajuan berhasil")
            ->send();
        try {
        } catch (\Throwable $th) {
            Notification::make()
                ->danger()
                ->title("Pengajuan gagal: " . $th->getMessage())
                ->send();
        }
    }
    public static function tanggapiNotifier(array $data, Pengajuan $record)
    {
        $pengaju = Pegawai::where("nip", $record->nip_pengaju)->first()?->panggilan;
        $uraianPengajuan = $record->uraian_pengajuan;
        $nominalPengajuan = self::toRupiah($record->nominal_pengajuan * 1);
        // Tanggapan ke PPK
        if ($data["posisi_dokumen_id"] == 2) {
            $userPpk = User::getTestPegawai()->first();
            $namaPanggilanPpk = $userPpk?->pegawai?->panggilan;
            $hasilPemeriksaanSebelumnya = $record->catatan_ppk;
            $tanggapanPengaju = $record->tanggapan_pengaju_ke_ppk;
            $targetWa = $userPpk?->pegawai?->nomor_wa;
            $linkKeAksi = config("app.url") . "/a/sipancong/pengajuans?activeTab=PPK";
            $message = "*Tanggapan Pengaju | DOKTER-V* \n\nHalo, *$namaPanggilanPpk*\nPengajuan dengan uraian\n$uraianPengajuan\n\nNominal: $nominalPengajuan\n\nSudah ditanggapi oleh *$pengaju*\n\nSebelumnya hasil pemeriksaanmu:\n *$hasilPemeriksaanSebelumnya*\n\nIni tanggapan dari *$pengaju*:\n*$tanggapanPengaju*\n\nBuka link berikut buat melanjutkan proses pembayaran ya!\n\n$linkKeAksi\n\nSemangatt!!";
        }
        // Tanggapan ke PPSPM
        else if ($data["posisi_dokumen_id"] == 3) {
            $tanggapanPengaju = $record->tanggapan_pengaju_ke_ppspm;
            $hasilPemeriksaanSebelumnya = $record->catatan_ppspm;
            $linkKeAksi = config("app.url") . "/a/sipancong/pengajuans?activeTab=PPSPM";
            $pegawaiPenerima = User::getTestPegawai()->first()->pegawai;
            $namaPanggilanPenerima = $pegawaiPenerima->panggilan;
            $targetWa = $pegawaiPenerima->nomor_wa;
            $message = "*Tanggapan Pengaju | DOKTER-V* \n\nHalo, *$namaPanggilanPenerima*\nPengajuan dengan uraian\n$uraianPengajuan\nNominal: $nominalPengajuan\n\nSudah ditanggapi oleh *$pengaju*\n\nSebelumnya hasil pemeriksaanmu:\n *$hasilPemeriksaanSebelumnya*\n\nIni tanggapan dari *$pengaju*:\n*$tanggapanPengaju*\n\nBuka link berikut buat melanjutkan proses pembayaran ya!\n\n$linkKeAksi\n\nSemangatt!!";
        }
        // Tanggapan ke Bendahara
        else if ($data["posisi_dokumen_id"] == 4) {
            $hasilPemeriksaanSebelumnya = $record->catatan_bendahara;
            $pegawaiPenerima = User::getTestPegawai()->first()->pegawai;
            $targetWa = $pegawaiPenerima->nomor_wa;
            $tanggapanPengaju = $record->tanggapan_pengaju_ke_bendahara;
            $linkKeAksi = config("app.url") . "/a/sipancong/pengajuans?activeTab=Bendahara";
            $namaPanggilanPenerima = User::getTestPegawai()->first()->pegawai->panggilan;
            $message = "*Tanggapan Pengaju | DOKTER-V* \n\nHalo, *$namaPanggilanPenerima*\nPengajuan dengan uraian\n$uraianPengajuan\nNominal: $nominalPengajuan\n\nSudah ditanggapi oleh *$pengaju*\n\nSebelumnya hasil pemeriksaanmu:\n *$hasilPemeriksaanSebelumnya*\n\nIni tanggapan dari *$pengaju*:\n*$tanggapanPengaju*\n\nBuka link berikut buat melanjutkan proses pembayaran ya!\n\n$linkKeAksi\n\nSemangatt!!";
        }
        WhatsappNotifier::send($targetWa, $message);
    }
    public static function tanggapi(array $data, Pengajuan $record)
    {
        try {
            // Tentukan siapa yang menolak pengajuan
            if (($record->status_pengajuan_ppk_id == 3) || ($record->status_pengajuan_ppk_id == 4)) {
                $data["posisi_dokumen_id"] = 2;
            } else if (($record->status_pengajuan_ppspm_id == 3) || ($record->status_pengajuan_ppspm_id  == 4)) {
                $data["posisi_dokumen_id"] = 3;
            } else if (($record->status_pengajuan_bendahara_id == 3) || ($record->status_pengajuan_bendahara_id == 4)) {
                $data["posisi_dokumen_id"] = 4;
            }
            $record->update($data);
            // Notifikasikan lewat whatsapp
            self::tanggapiNotifier($data, $record);
            Notification::make()
                ->success()
                ->title("Berhasil memberi tanggapan")
                ->send();
        } catch (\Throwable $th) {
            Notification::make()
                ->danger()
                ->title("Tanggapan gagal: " . $th->getMessage())
                ->send();
        }
    }
    public static function pemeriksaanPpkNotifier(array $data, Pengajuan $record)
    {
        $pengaju = Pegawai::where("nip", $record->nip_pengaju)->first()?->panggilan;
        $uraianPengajuan = $record->uraian_pengajuan;
        $nominalPengajuan = self::toRupiah($record->nominal_pengajuan * 1);

        // Notifikasi Tidak Disetujui ke Pengaju
        if ($data["posisi_dokumen_id"] == 1) {
            $hasilPemeriksaan = $record->catatan_ppk;
            $linkKeAksi = config("app.url") . "/a/sipancong/pengajuans?activeTab=Pengaju";
            $pegawaiPenerima = User::getTestPegawai()->first()->pegawai;
            $userPpk = User::getTestPegawai()->first();
            $namaPanggilanPpk = $userPpk?->pegawai?->panggilan;
            // $namaPanggilanPenerima = $pegawaiPenerima->panggilan;
            $targetWa = $pegawaiPenerima->nomor_wa;
            $message = "*Perlu Perbaikan | DOKTER-V* \n\nHalo, *$pengaju*\nPengajuanmu dengan uraian\n$uraianPengajuan\nNominal: $nominalPengajuan\n\nSudah diperiksa oleh *$namaPanggilanPpk* sebagai PPK\n\nCatatan dari beliau gini nih:\n *$hasilPemeriksaan*\n\nBuka link berikut buat melanjutkan proses perbaikannya ya!\n\n$linkKeAksi\n\nSemangatt!!";
        }

        // Notifikasi Disetujui ke PPSPM
        else if ($data["posisi_dokumen_id"] == 3) {
            // $userPpspm = User::getPpspm()->first();
            $userPpspm = User::getTestPegawai()->first();
            $pengaju = Pegawai::where("nip", $record->nip_pengaju)->first();
            $namaPengaju = $pengaju->panggilan;
            $namaPanggilanPpspm = $userPpspm?->pegawai?->panggilan;
            $targetWa = $userPpspm?->pegawai?->nomor_wa;
            $linkKeAksi = config("app.url") . "/a/sipancong/pengajuans?activeTab=PPSPM";


            // Notifikasi Pengajuan Baru ke PPK
            $message = "*Pengajuan Baru | DOKTER-V* \n \nHalo, $namaPanggilanPpspm \nAda pengajuan pembayaran baru dari *$namaPengaju* nih! \n\nUraian: $uraianPengajuan \n\nNominal: $nominalPengajuan \n\nBuka link ini untuk  ngeceknya ya:\n\n$linkKeAksi\n\n Semangat!!";
        };
        WhatsappNotifier::send($targetWa, $message);
    }
    public static function pemeriksaanPpk(array $data, Pengajuan $record)
    {
        // Dokumen Dikembalikan
        if (($data["status_pengajuan_ppk_id"] == 3) || ($data["status_pengajuan_ppk_id"] == 4)) {
            $data["posisi_dokumen_id"] = 1;
        }

        // Dokumen Dimajukan Ke PPSPM
        if (($data["status_pengajuan_ppk_id"] == 2) || ($data["status_pengajuan_ppk_id"] == 5)) {
            $data["posisi_dokumen_id"] = 3;
        }
        $record->update($data);
        if (!$data["status_pengajuan_ppk_id"]) dd($record, $data);

        // Notifikasikan lewat whatsapp
        self::pemeriksaanPpkNotifier($data, $record);
        Notification::make()
            ->success()
            ->title("Berhasil menyimpan hasil pemeriksaan")
            ->send();
        try {
        } catch (\Throwable $th) {
            Notification::make()
                ->danger()
                ->title("Gagal menyimpan hasil pemeriksaan " . $th->getMessage())
                ->send();
        }
    }
    public static function pemeriksaanPpspmNotifier(array $data, Pengajuan $record)
    {
        $pengaju = Pegawai::where("nip", $record->nip_pengaju)->first()?->panggilan;
        $uraianPengajuan = $record->uraian_pengajuan;
        $nominalPengajuan = self::toRupiah($record->nominal_pengajuan * 1);

        // Notifikasi Tidak Disetujui ke Pengaju
        if ($data["posisi_dokumen_id"] == 1) {
            $hasilPemeriksaan = $record->catatan_ppspm;
            $linkKeAksi = config("app.url") . "/a/sipancong/pengajuans?activeTab=Pengaju";
            $pegawaiPenerima = User::getTestPegawai()->first()->pegawai;
            $userPpspm = User::getTestPegawai()->first();
            $namaPanggilanPpspm = $userPpspm?->pegawai?->panggilan;
            // $namaPanggilanPenerima = $pegawaiPenerima->panggilan;
            $targetWa = $pegawaiPenerima->nomor_wa;
            $message = "*Perlu Perbaikan | DOKTER-V* \n\nHalo, *$pengaju*\nPengajuanmu dengan uraian\n$uraianPengajuan\nNominal: $nominalPengajuan\n\nSudah diperiksa oleh *$namaPanggilanPpspm* sebagai PPSPM\n\nCatatan dari beliau gini nih:\n *$hasilPemeriksaan*\n\nBuka link berikut buat melanjutkan proses perbaikannya ya!\n\n$linkKeAksi\n\nSemangatt!!";
        }

        // Notifikasi Disetujui ke Bendahara
        else if ($data["posisi_dokumen_id"] == 4) {
            // $userBendahara = User::getBendahara()->first();
            $userBendahara = User::getTestPegawai()->first();
            $pengaju = Pegawai::where("nip", $record->nip_pengaju)->first();
            $namaPengaju = $pengaju->panggilan;
            $namaPanggilanBendahara = $userBendahara?->pegawai?->panggilan;
            $targetWa = $userBendahara?->pegawai?->nomor_wa;
            $linkKeAksi = config("app.url") . "/a/sipancong/pengajuans?activeTab=Bendahara";

            // Notifikasi Pengajuan Baru ke Bendahara
            $message = "*Pengajuan Baru | DOKTER-V* \n \nHalo, $namaPanggilanBendahara \nAda pengajuan pembayaran baru dari *$namaPengaju* nih! \n\nUraian: $uraianPengajuan \n\nNominal: $nominalPengajuan \n\nBuka link ini untuk  ngeceknya ya:\n\n$linkKeAksi\n\n Semangat!!";
        };
        WhatsappNotifier::send($targetWa, $message);
    }
    public static function pemeriksaanPpspm(array $data, Pengajuan $record)
    {
        // Dokumen Di PPSPM
        if (($data["status_pengajuan_ppspm_id"] == 3) || ($data["status_pengajuan_ppspm_id"] == 4)) {
            $data["posisi_dokumen_id"] = 1;
        }

        // Dokumen Di Bendahara
        if (($data["status_pengajuan_ppspm_id"] == 2) || ($data["status_pengajuan_ppspm_id"] == 5)) {
            $data["posisi_dokumen_id"] = 4;
        }
        $record->update($data);
        // Notifikasikan lewat whatsapp
        self::pemeriksaanPpspmNotifier($data, $record);
        Notification::make()
            ->success()
            ->title("Berhasil menyimpan hasil pemeriksaan")
            ->send();
        try {
        } catch (\Throwable $th) {
            Notification::make()
                ->danger()
                ->title("Gagal menyimpan hasil pemeriksaan " . $th->getMessage())
                ->send();
        }
    }
    public static function pemeriksaanBendaharaNotifier(array $data, Pengajuan $record)
    {
        $pengaju = Pegawai::where("nip", $record->nip_pengaju)->first()?->panggilan;
        $uraianPengajuan = $record->uraian_pengajuan;
        $nominalPengajuan = self::toRupiah($record->nominal_pengajuan * 1);

        // Notifikasi Tidak Disetujui ke Pengaju
        if ($data["posisi_dokumen_id"] == 1) {
            $hasilPemeriksaan = $record->catatan_bendahara;
            $linkKeAksi = config("app.url") . "/a/sipancong/pengajuans?activeTab=Pengaju";
            $pegawaiPenerima = User::getTestPegawai()->first()->pegawai;
            $userBendahara = User::getTestPegawai()->first();
            $namaPanggilanBendahara = $userBendahara?->pegawai?->panggilan;
            // $namaPanggilanPenerima = $pegawaiPenerima->panggilan;
            $targetWa = $pegawaiPenerima->nomor_wa;
            $message = "*Perlu Perbaikan | DOKTER-V* \n\nHalo, *$pengaju*\nPengajuanmu dengan uraian\n$namaPanggilanBendahara\nNominal: $nominalPengajuan\n\nSudah diperiksa oleh *$namaPanggilanBendahara* sebagai Bendahara nih\n\nCatatan dari beliau gini nih:\n *$hasilPemeriksaan*\n\nBuka link berikut buat melanjutkan proses perbaikannya ya!\n\n$linkKeAksi\n\nSemangatt!!";
        }

        // Notifikasi Disetujui ke Pengaju
        else if ($data["posisi_dokumen_id"] == 4) {
            // $userBendahara = User::getBendahara()->first();
            $userBendahara = User::getTestPegawai()->first();
            $pengaju = Pegawai::where("nip", $record->nip_pengaju)->first();
            $namaPengaju = $pengaju->panggilan;
            $namaPanggilanBendahara = $userBendahara?->pegawai?->panggilan;
            $targetWa = $userBendahara?->pegawai?->nomor_wa;
            $linkKeAksi = config("app.url") . "/a/sipancong/pengajuans?activeTab=Bendahara";

            $message = "*Pengajuan Selesai! | DOKTER-V* \n\nHalo, $namaPengaju \nPengajuanmu dengan uraian: \n\n$uraianPengajuan \nNominal: $nominalPengajuan \n\n*Udah disetujui sama Bendahara!*\nNanti aku kabarin lagi kalau udah dicairin\n\nSemangat!!";
        };
        WhatsappNotifier::send($targetWa, $message);
    }
    public static function pemeriksaanBendahara(array $data, Pengajuan $record)
    {
        try {
            // Dokumen Dikembalikan
            if (($data["status_pengajuan_bendahara_id"] == 3) || ($data["status_pengajuan_bendahara_id"] == 4)) {
                $data["posisi_dokumen_id"] = 1;
            }

            // Dokumen Di Bendahara
            if (($data["status_pengajuan_bendahara_id"] == 2) || ($data["status_pengajuan_bendahara_id"] == 5)) {
                $data["posisi_dokumen_id"] = 4;
            }
            $record->update($data);
            // Notifikasikan lewat whatsapp
            self::pemeriksaanBendaharaNotifier($data, $record);
            if (!$data["status_pengajuan_bendahara_id"]) dd($record, $data);
            Notification::make()
                ->success()
                ->title("Berhasil menyimpan hasil pemeriksaan")
                ->send();
        } catch (\Throwable $th) {
            Notification::make()
                ->danger()
                ->title("Gagal menyimpan hasil pemeriksaan " . $th->getMessage())
                ->send();
        }
    }

    public static function pemrosesanBendaharaNotifier(array $data, Pengajuan $record)
    {
        $pengaju = Pegawai::where("nip", $record->nip_pengaju)->first()?->panggilan;
        $uraianPengajuan = $record->uraian_pengajuan;
        $nominalPengajuan = self::toRupiah($record->nominal_pengajuan * 1);
        $userBendahara = User::getTestPegawai()->first();
        $pengaju = Pegawai::where("nip", $record->nip_pengaju)->first();
        $namaPengaju = $pengaju->panggilan;
        $targetWa = $userBendahara?->pegawai?->nomor_wa;
        $message = "*Pengajuan Cair! | DOKTER-V* \n \nHalo, $namaPengaju \nPengajuanmu dengan uraian: \n\n $uraianPengajuan \nNominal: $nominalPengajuan \n\nUdah dicairin sama Bendahara!\nMakasih banget yaa udah ngelancarin proses pembayaran ini\n\n Semangat!!";
        WhatsappNotifier::send($targetWa, $message);
    }
    public static function pemrosesanBendahara(array $data, Pengajuan $record)
    {
        try {
            $data = [
                ...$data,
                "nominal_dibayarkan" => $data["nominal_dibayarkan"] ?? null,
                "nominal_dikembalikan" => $data["nominal_dikembalikan"] ?? null,
                "tanggal_pembayaran" => $data["tanggal_pembayaran"] ?? null,
                "jenis_dokumen_id" => $data["jenis_dokumen_id"] ?? null,
                "nomor_dokumen" => $data["nomor_dokumen"] ?? null,
            ];
            $data["posisi_dokumen_id"] = 6;
            $record->update($data);
            self::pemrosesanBendaharaNotifier($data, $record);
            Notification::make()
                ->success()
                ->title("Berhasil menyimpan hasil pemrosesan pembayaran")
                ->send();
        } catch (\Throwable $th) {
            Notification::make()
                ->danger()
                ->title("Gagal menyimpan hasil pemrosesan pembayaran " . $th->getMessage())
                ->send();
        }
    }
    public static function ubahPengajuan(array $data, Pengajuan $record)
    {
        try {
            $record->update($data);
            Notification::make()
                ->success()
                ->title("Berhasil menyimpan hasil perubahan")
                ->send();
        } catch (\Throwable $th) {
            Notification::make()
                ->danger()
                ->title("Gagal menyimpan hasil perubahan " . $th->getMessage())
                ->send();
        }
    }
    public static function isSiapDiperiksaPpk(Pengajuan $pengajuan)
    {
        return !(
            $pengajuan->status_pengajuan_ppk_id == 2 ||
            $pengajuan->status_pengajuan_ppk_id == 5
        ) || $pengajuan->posisi_dokumen_id == 2;
    }
    public static function isSiapDiperiksaBendahara(Pengajuan $pengajuan)
    {
        return (
            $pengajuan->status_pengajuan_ppk_id == 2 ||
            $pengajuan->status_pengajuan_ppk_id == 5
        ) &&
            !(
                $pengajuan->status_pengajuan_bendahara_id == 2 ||
                $pengajuan->status_pengajuan_bendahara_id == 5
            )
            || ($pengajuan->status_pembayaran_id == 3);
    }
    public static function isSiapDiperiksaPpspm(Pengajuan $pengajuan)
    {
        return (
            $pengajuan->status_pengajuan_ppk_id == 2 ||
            $pengajuan->status_pengajuan_ppk_id == 5
        ) &&
            (
                $pengajuan->status_pengajuan_bendahara_id == 2 ||
                $pengajuan->status_pengajuan_bendahara_id == 5
            ) &&
            !(
                $pengajuan->status_pengajuan_ppspm_id == 2 ||
                $pengajuan->status_pengajuan_ppspm_id == 5
            ) || $pengajuan->posisi_dokumen_id == 3;;
    }
    public static function isSiapDiprosesBendahara(Pengajuan $pengajuan)
    {
        return (
            $pengajuan->status_pengajuan_ppk_id == 2 ||
            $pengajuan->status_pengajuan_ppk_id == 5
        ) &&
            (
                $pengajuan->status_pengajuan_bendahara_id == 2 ||
                $pengajuan->status_pengajuan_bendahara_id == 5
            ) &&
            (
                $pengajuan->status_pengajuan_ppspm_id == 2 ||
                $pengajuan->status_pengajuan_ppspm_id == 5
            );
    }

    public static function jumlahPerluPemeriksaanPpk()
    {
        return Pengajuan::whereRaw(self::rawPerluPemeriksaanPpk())->count();
    }
    public static function jumlahPerluPemeriksaanBendahara()
    {
        return Pengajuan::whereRaw(self::rawPerluPemeriksaanBendahara())->count();
    }
    public static function jumlahPerluPemeriksaanPpspm()
    {
        return Pengajuan::whereRaw(self::rawPerluPemeriksaanPpspm())->count();
    }
    public static function jumlahPerluProsesBendahara()
    {
        return Pengajuan::whereRaw(self::rawPerluProsesBendahara())->count();
    }

    public static function rawPerluPemeriksaanPengaju()
    {
        // return "status_pengajuan_ppk_id IN (1,3,4) OR status_pengajuan_bendahara_id IN (1,3,4) OR status_pengajuan_ppspm_id IN (1,3,4) OR link_folder_dokumen IS NULL";
        return "posisi_dokumen_id=1 OR link_folder_dokumen IS NULL";
    }
    public static function rawPerluPemeriksaanPpk()
    {
        // return "status_pengajuan_ppk_id NOT IN (2,5) OR status_pengajuan_ppk_id IS NULL";
        return "posisi_dokumen_id=2 OR status_pengajuan_ppk_id IS NULL";
    }
    public static function rawPerluPemeriksaanBendahara()
    {
        // return "status_pengajuan_bendahara_id IS NULL OR status_pengajuan_ppk_id IN (2,5) AND status_pengajuan_bendahara_id NOT IN (2,5) OR (status_pembayaran_id = 3)";
        return "posisi_dokumen_id=4";
    }
    public static function rawPerluPemeriksaanPpspm()
    {
        // return "(status_pengajuan_ppk_id IN (2,5)) AND (status_pengajuan_bendahara_id IN (2,5)) AND (status_pengajuan_ppspm_id IN (3,4,NULL) or status_pengajuan_ppspm_id IS NULL)";
        return "posisi_dokumen_id=3";
    }
    public static function rawPerluProsesBendahara()
    {
        // return "((status_pengajuan_ppk_id IN (2,5) AND status_pengajuan_bendahara_id IN (2,5) AND (tanggal_pembayaran IS NULL OR status_pembayaran_id IS NULL)) OR (tanggal_pembayaran IS NOT NULL AND status_pembayaran_id = 7))";
        return "((status_pengajuan_ppk_id IN (2,5) AND status_pengajuan_bendahara_id IN (2,5) AND (tanggal_pembayaran IS NULL OR status_pembayaran_id IS NULL)))";
    }
    public static function rawSelesaiProsesBendahara()
    {
        return "status_pembayaran_id IN (1,2,5,7)";
    }
}
