<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KegiatanManmit extends Model
{
    use HasFactory;

    protected $guarded = [];

    public static function importKegiatanManmit($data){
        $res = [];
        $res['id']=$data['id_kegiatan_manmit'];
        $res['nama']=$data['nama_kegiatan_manmit'];
        $res['tgl_mulai_pelaksanaan']=$data['tanggal_mulai_pelaksanaan'] ?? null;
        $res['tgl_akhir_pelaksanaan']=$data['tanggal_akhir_pelaksanaan'] ?? null;
        $res['tgl_mulai_penawaran']=$data['tanggal_mulai_penawaran'] ?? null;
        $res['tgl_akhir_penawaran']=$data['tanggal_akhir_penawaran'] ?? null;
        return new KegiatanManmit($res);
    }
}
