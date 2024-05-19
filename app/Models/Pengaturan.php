<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengaturan extends Model
{
    use HasFactory;
    protected $guarded=[];

    public static $values;

    public static function key(string $key){
        self::$values = self::$values ?? self::get();
        return self::$values->where("key",$key)->first();
    }
    public static function namaSatker(bool $denganLevelAdministrasi = true){
        $namaSatker = self::key('NAMA_KAKO')->nilai;
        if($denganLevelAdministrasi) return $namaSatker;
        $res = "";
        foreach(explode(" ",$namaSatker) as $k=>$v){
            if($k == 0) continue;
            $res = $res." ".$v;
        }
        return trim($res);
    }

}
