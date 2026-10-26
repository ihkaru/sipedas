<?php

namespace App\Models\Sipancong;

use App\Models\Pegawai;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Pengajuan extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    protected $table = 'sp_pengajuan';
    protected $guarded = [];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, "nip_pengaju", "nip");
    }
    public function pengaju()
    {
        return $this->belongsTo(Pegawai::class, "nip_pengaju", "nip");
    }
    public function penanggungJawab()
    {
        return $this->belongsTo(Pegawai::class, "nip_penanggung_jawab", "nip");
    }
    public function posisiDokumen()
    {
        return $this->belongsTo(PosisiDokumen::class, "posisi_dokumen_id", "id");
    }
    public function subfungsi()
    {
        return $this->belongsTo(Subfungsi::class, "sub_fungsi_id", "id");
    }
    public function statusPembayaran()
    {
        return $this->belongsTo(StatusPembayaran::class, "status_pembayaran_id", "id");
    }
    public function jenisDokumen()
    {
        return $this->belongsTo(JenisDokumen::class, "jenis_dokumen_id", "id");
    }
    public function statusPengajuanPpk()
    {
        return $this->belongsTo(StatusPengajuan::class, "status_pengajuan_ppk_id", "id");
    }
    public function statusPengajuanPpspm()
    {
        return $this->belongsTo(StatusPengajuan::class, "status_pengajuan_ppspm_id", "id");
    }
    public function statusPengajuanBendahara()
    {
        return $this->belongsTo(StatusPengajuan::class, "status_pengajuan_bendahara_id", "id");
    }
    public function getRowClassAttribute()
    {
        if ($this->posisiDokumen?->nama == "Selesai") {
            return "bg-green-100";
        } else if ($this->posisiDokumen?->nama == "Di Pengaju Pembayaran" && auth()->user()->pegawai?->nip == $this->nip_pengaju) {
            return "bg-blue-100";
        }
        return "bg-blue-100";
    }
}
