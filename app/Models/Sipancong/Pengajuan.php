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
    public function posisiDokumen()
    {
        return $this->hasOne(PosisiDokumen::class, "id", "posisi_dokumen_id");
    }
}
