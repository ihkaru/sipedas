<?php

namespace App\Models;

use App\Supports\Constants;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TujuanSuratTugas extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function penugasan(){
        return $this->belongsTo(Penugasan::class);
    }
    public function desa(){
        return $this->belongsTo(MasterSls::class,"desa_kel_id","desa_kel_id");
    }
    public function kecamatan(){
        return $this->belongsTo(MasterSls::class,"kecamatan_id","kec_id");
    }
    public function kabkot(){
        return $this->belongsTo(MasterSls::class,"kabkot_id","kabkot_id");
    }
    public function provinsi(){
        return $this->belongsTo(MasterSls::class,"prov_id");
    }
    public static function ajukan($data,$pengajuan_id){
        $now = now();
        if($data["level_tujuan_penugasan"] == Constants::LEVEL_PENUGASAN_NAMA_TEMPAT){
            self::create([
                "penugasan_id"=>$pengajuan_id,
                "level_tujuan_penugasan" => $data["level_tujuan_penugasan"],
                "nama_tempat_tujuan" => $data["nama_tempat_tujuan"],
                "created_at"=>$now,
                "updated_at"=>$now,
            ]);
        }
        if($data["level_tujuan_penugasan"] == Constants::LEVEL_PENUGASAN_KABUPATEN_KOTA){
            $res = [];
            foreach ($data["kabkot_ids"] as $d) {
                $res[] = [
                    "penugasan_id"=>$pengajuan_id,
                    "level_tujuan_penugasan" => $data["level_tujuan_penugasan"],
                    "prov_id" => $data["prov_ids"][0],
                    "kabkot_id" => $d,
                    "created_at"=>$now,
                    "updated_at"=>$now,
                ];
            }
            self::insert($res);
        };
        if($data["level_tujuan_penugasan"] == Constants::LEVEL_PENUGASAN_KECAMATAN){
            $res = [];
            foreach ($data["kecamatan_ids"] as $d) {
                $res[] = [
                    "penugasan_id"=>$pengajuan_id,
                    "level_tujuan_penugasan" => $data["level_tujuan_penugasan"],
                    "prov_id" => $data["prov_ids"][0],
                    "kabkot_id" => $data["kabkot_ids"][0],
                    "kecamatan_id" => $d,
                    "created_at"=>$now,
                    "updated_at"=>$now,
                ];
            }
            self::insert($res);
        };
        if($data["level_tujuan_penugasan"] == Constants::LEVEL_PENUGASAN_DESA_KELURAHAN){
            $res = [];
            foreach ($data["desa_kel_ids"] as $d) {
                $res[] = [
                    "penugasan_id"=>$pengajuan_id,
                    "level_tujuan_penugasan" => $data["level_tujuan_penugasan"],
                    "prov_id" => $data["prov_ids"][0],
                    "kabkot_id" => $data["kabkot_ids"][0],
                    "kecamatan_id" => $data["kecamatan_ids"][0],
                    "desa_kel_id" => $d,
                    "created_at"=>$now,
                    "updated_at"=>$now,
                ];
            }
            self::insert($res);
        };


    }
    public static function combinerTujuan($data,$masterSls){
        $res = "";
        foreach ($data as $d) {
            if($d['level_tujuan_penugasan'] == Constants::LEVEL_PENUGASAN_NAMA_TEMPAT) return ucwords(strtolower($d->nama_tempat_tujuan));
            if($d['level_tujuan_penugasan'] == Constants::LEVEL_PENUGASAN_KABUPATEN_KOTA) $res=$res.", ".ucwords(strtolower($masterSls->where('kabkot_id',$d['kabkot_id'])->first()->kabkot));
            if($d['level_tujuan_penugasan'] == Constants::LEVEL_PENUGASAN_KECAMATAN) $res=$res.", ".ucwords(strtolower($masterSls->where('kec_id',$d['kecamatan_id'])->first()->kecamatan));
            if($d['level_tujuan_penugasan'] == Constants::LEVEL_PENUGASAN_DESA_KELURAHAN) $res=$res.", ".ucwords(strtolower($masterSls->where('desa_kel_id',$d['desa_kel_id'])->first()->desa_kel));
        }
        return ltrim($res,", ");
    }
}
