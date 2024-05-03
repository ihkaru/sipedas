<?php

namespace App\Models;

use App\Supports\Constants;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\TextUI\Configuration\Constant;
use Illuminate\Database\Eloquent\Casts\Attribute;

class RiwayatPengajuan extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function penugasan(){
        return $this->belongsTo(Penugasan::class,"id","penugasan_id");
    }

    protected function lastStatus(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => Constants::STATUS_PENGAJUAN_OPTIONS[$attributes["status"]],
        );
    }
    protected function lastStatusTimestamp(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                $dates = collect([
                    ["date"=>$attributes["tgl_dikirim"]],
                    ["date"=>$attributes["tgl_diterima"]],
                    ["date"=>$attributes["tgl_dibuat"]],
                    ["date"=>$attributes["tgl_dikumpulkan"]],
                    ["date"=>$attributes["tgl_butuh_perbaikan"]],
                    ["date"=>$attributes["tgl_ditolak"]],
                    ["date"=>$attributes["tgl_pencairan"]],
                ]);
                return $dates->sortByDesc('date')->first()['date'];
            },
        );
    }

    public static function kirim(array $arrIdPengajuan){
        $now = now()->toDateTimeString();
        foreach ($arrIdPengajuan as $id){
            self::updateOrCreate([
                "penugasan_id" => $id,
            ],[
                "status"=>Constants::STATUS_PENGAJUAN_DIKIRIM,
                "tgl_dikirim"=>$now,
            ]);
        }
    }
}
