<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kegiatan extends Model
{
    use HasFactory;
    protected $guarded=[];
    protected $primaryKey = "id";
    public $incrementing = false;
    // In Laravel 6.0+ make sure to also set $keyType
    protected $keyType = 'string';

    public function pj(){
        return $this->belongsTo(Pegawai::class,"pj_kegiatan_id","nip");
    }
    public function penugasans(){
        return $this->hasMany(Penugasan::class,"kegiatan_id","id");
    }

    public function kegiatanManmit(){
        return $this->belongsTo(KegiatanManmit::class);
    }

}
