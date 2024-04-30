<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Plh extends Model
{
    use HasFactory;
    protected $guarded=[];

    public function pegawai(){
        return $this->belongsTo(Pegawai::class,"pegawai_pengganti_id","nip");
    }

    public static function jadikanPlh(Pegawai $pegawai, string $tgl_mulai, string $tgl_akhir){
        $ctgl_mulai = Carbon::parse($tgl_mulai);
        $ctgl_akhir = Carbon::parse($tgl_akhir);
        return self::create([
            "pegawai_pengganti_id"=>$pegawai->nip,
            "tgl_mulai"=>$ctgl_mulai,
            "tgl_selesai"=>$ctgl_akhir,
        ]);
    }
    public static function getPlhAktif(Carbon | string $date = null ,$returnPegawai = false){
        $resDate = is_string($date) ? Carbon::parse($date) : $date;
        $resDate = $date ?? now();
        $plh = self::where("tgl_mulai","<=",$resDate)
            ->where("tgl_selesai",">=",$resDate)
            ->first();
        // dump(1);
        // dump($plh,$plh->pegawai);
        if($plh && $returnPegawai) return $plh->pegawai;
        // dump(2);
        if($plh) return $plh;
        // dump(3);
        if($returnPegawai) return Pegawai::find(Pengaturan::key("ID_PLH_DEFAULT"))->first();
        // dump(4);
        return null;
    }

}
