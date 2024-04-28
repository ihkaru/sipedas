<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penugasan extends Model
{
    use HasFactory;
    protected $guarded=[];

    public function pegawai(){
        return $this->belongsTo(Pegawai::class,"nip","nip");
    }
    public function desa(){
        return $this->hasOne(MasterSls::class,"desa_kel_id");
    }
    public function kecamatan(){
        return $this->hasOne(MasterSls::class,"kec_id");
    }
    public function plh(){
        return $this->belongsTo(Pegawai::class,"plh_id","nip");
    }
    public function riwayatPengajuans(){
        return $this->hasMany(RiwayatPengajuan::class);
    }
}
