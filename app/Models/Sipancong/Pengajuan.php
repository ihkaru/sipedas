<?php

namespace App\Models\Sipancong;

use App\Models\Pegawai;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengajuan extends Model
{
    use HasFactory;
    protected $table = 'sp_pengajuan';
    protected $guarded = [];

    public function pegawai()
    {
        return $this->hasOne(Pegawai::class, "nip", "nip_pengaju");
    }
    public function penanggungJawab()
    {
        return $this->hasOne(Pegawai::class, "nip", "nip_penanggung_jawab");
    }
    public function posisiDokumen()
    {
        return $this->hasOne(PosisiDokumen::class, "id", "posisi_dokumen_id");
    }
    public function subfungsi()
    {
        return $this->hasOne(Subfungsi::class, "id", "sub_fungsi_id");
    }
    public function statusPembayaran()
    {
        return $this->hasOne(StatusPembayaran::class, "id", "status_pembayaran_id");
    }
    public function jenisDokumen()
    {
        return $this->hasOne(JenisDokumen::class, "id", "jenis_dokumen_id");
    }
    public function statusPengajuanPpk()
    {
        return $this->hasOne(StatusPengajuan::class, "id", "status_pengajuan_ppk_id");
    }
    public function statusPengajuanPpspm()
    {
        return $this->hasOne(StatusPengajuan::class, "id", "status_pengajuan_ppspm_id");
    }
    public function statusPengajuanBendahara()
    {
        return $this->hasOne(StatusPengajuan::class, "id", "status_pengajuan_bendahara_id");
    }
}
