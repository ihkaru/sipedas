<?php

namespace App\Models;

use App\Supports\Constants;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class KegiatanManmit extends Model
{
    use HasFactory;

    protected $fillable = ['nama', 'id'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = Str::slug($model->nama);
        });

        static::updating(function ($model) {
            $model->id = Str::slug($model->nama);
        });
    }

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
    public function kegiatans(){
        return $this->hasMany(Kegiatan::class);
    }
    protected function idNama(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => "(".$attributes["id"].")"." ".$attributes["nama"],
        );
    }
    public static function getSelectOptions(){
        return self::pluck('nama','id')->map(fn($v,$k)=>"($k) ".$v);
    }
}
