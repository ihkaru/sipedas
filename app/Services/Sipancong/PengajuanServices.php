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
}
