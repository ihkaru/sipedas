<?php

namespace App\Models;

use App\Supports\Constants;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterSls extends Model
{
    use HasFactory;
    protected $guarded=[];
    protected static Collection $mastersls;

    public static function getMasterSls(){
        self::$mastersls = self::$mastersls ?? self::get();
        return self::$mastersls;
    }

    public static function getIdByName($nama,$level,$data = null){
        if($nama == null) return null;
        $masterSls = self::getMasterSls();
        $res = [];
        $res["prov_ids"] = ["61"];
        $res["kabkot_ids"] = ["6104"];
        $res["kecamatan_ids"] = [];
        $res["desa_kel_ids"] = [];
        $nama = explode(",",$nama);
        foreach ($nama as $key => $n) {
            if($level == Constants::LEVEL_PENUGASAN_DESA_KELURAHAN){
                $master = $masterSls->where("desa_kel",strtoupper(trim($n)))->first();
                // if($master == null) {
                //     dump($n,$level);
                //     return [];
                // }
                $res["kecamatan_ids"][] = $master->kec_id;
                $res["desa_kel_ids"][] = $master->desa_kel_id;
            }
            if($level == Constants::LEVEL_PENUGASAN_KECAMATAN){
                $master = $masterSls->where("kecamatan",strtoupper(trim($n)))->first();
                // if($master == null) {
                //     dump($n,$level,$data);
                //     return [];
                // }
                $res["kecamatan_ids"][] = $master->kec_id;
            }
        }
        // dump($res,$level);
        return $res;

    }

}
