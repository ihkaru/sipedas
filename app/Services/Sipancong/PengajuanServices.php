<?php

namespace App\Services\Sipancong;

use App\Models\Sipancong\Pengajuan;
use App\Supports\Constants;
use Filament\Notifications\Notification;
use PDO;

class PengajuanServices
{
    public static function ajukan(array $data)
    {
        $last_pengajuan = Pengajuan::whereBetween("tanggal_pengajuan", [now()->startOfYear(), now()->endOfYear()])
            ->orderBy('tanggal_pengajuan', "desc")->first();
        $data["nomor_pengajuan"] = ($last_pengajuan) ? $last_pengajuan->nomor_pengajuan + 1 : 1;
        $data["posisi_dokumen_id"] = 2;
        $data["nip_pengaju"] = auth()->user()->pegawai->nip;
        Pengajuan::create($data);
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
    public static function tanggapi(array $data, Pengajuan $record)
    {
        try {
            $record->update($data);
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
    public static function pemeriksaanPpk(array $data, Pengajuan $record)
    {
        try {
            $record->update($data);
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
    public static function pemeriksaanBendahara(array $data, Pengajuan $record)
    {
        try {
            $record->update($data);
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
    public static function pemeriksaanPpspm(array $data, Pengajuan $record)
    {
        try {
            $record->update($data);
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
            $record->update($data);
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
        return "status_pengajuan_ppk_id IN (1,3,4) OR status_pengajuan_bendahara_id IN (1,3,4) OR status_pengajuan_ppspm_id IN (1,3,4) OR link_folder_dokumen IS NULL";
    }
    public static function rawPerluPemeriksaanPpk()
    {
        return "status_pengajuan_ppk_id NOT IN (2,5) OR status_pengajuan_ppk_id IS NULL";
    }
    public static function rawPerluPemeriksaanBendahara()
    {
        return "status_pengajuan_bendahara_id IS NULL OR status_pengajuan_ppk_id IN (2,5) AND status_pengajuan_bendahara_id NOT IN (2,5) OR (status_pembayaran_id = 3)";
    }
    public static function rawPerluPemeriksaanPpspm()
    {
        return "status_pengajuan_ppk_id IN (2,5) AND status_pengajuan_bendahara_id IN (2,5) AND (status_pengajuan_bendahara_id IN (3,4,NULL))";
    }
    public static function rawPerluProsesBendahara()
    {
        return "((status_pengajuan_ppk_id IN (2,5) AND status_pengajuan_bendahara_id IN (2,5) AND status_pengajuan_bendahara_id IN (2,5) AND tanggal_pembayaran IS NULL OR status_pembayaran_id IS NULL) OR (tanggal_pembayaran IS NOT NULL AND status_pembayaran_id = 7))";
    }
    public static function rawSelesaiProsesBendahara()
    {
        return "tanggal_pembayaran IS NOT NULL AND status_pembayaran_id IS NOT NULL";
    }
}
