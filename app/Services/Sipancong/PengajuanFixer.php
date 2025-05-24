<?php

namespace App\Services\Sipancong;

use App\Models\Sipancong\Pengajuan;
use Illuminate\Support\Carbon;

class PengajuanFixer
{
    public static function fix()
    {
        Pengajuan::whereRaw("status_pembayaran_id IN (1,2,5,7)")->update([
            "posisi_dokumen_id" => 6,
            "status_pengajuan_ppk_id" => 5,
            "status_pengajuan_ppspm_id" => 5,
            "status_pengajuan_bendahara_id" => 5,
        ]);
        Pengajuan::whereRaw("status_pembayaran_id IN (1,2,5,7) AND tanggal_pembayaran IS NULL")->update([
            "posisi_dokumen_id" => 6,
            "status_pengajuan_ppk_id" => 5,
            "status_pengajuan_ppspm_id" => 5,
            "status_pengajuan_bendahara_id" => 5,
            "tanggal_pembayaran" => Carbon::parse("2025-05-23")
        ]);
    }
}
