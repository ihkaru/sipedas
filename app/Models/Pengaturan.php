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

}
