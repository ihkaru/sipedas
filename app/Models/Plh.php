<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Plh extends Model
{
    use HasFactory;
    protected $guarded=[];

    public function pegawaiPengganti(){
        return $this->belongsTo(Pegawai::class,"pegawai_pengganti_id","nip");
    }
    public function pegawaiDigantikan(){
        return $this->belongsTo(Pegawai::class,"pegawai_digantikan_id","nip");
    }

    public static function jadikanPlh(Pegawai $pegawaiPeganti, Pegawai $pegawaiDigantikan, string $tgl_mulai, string $tgl_akhir){
        $ctgl_mulai = Carbon::parse($tgl_mulai);
        $ctgl_akhir = Carbon::parse($tgl_akhir);
        return self::create([
            "pegawai_pengganti_id"=>$pegawaiPeganti->nip,
            "pegawai_digantikan_id"=>$pegawaiDigantikan->nip,
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
    public static function getApprover(array $nipPegawais,string $tgl_mulai_tugas, bool $returnPegawai = false){
        $atasanLangsung = self::getAtasanTertinggi($nipPegawais);
        $plh = self::where("pegawai_digantikan_id",$atasanLangsung->nip)
            ->where("tgl_mulai","<=",$tgl_mulai_tugas)
            ->where("tgl_selesai",">=",$tgl_mulai_tugas)
            ->first()
        ;
        if($plh) return $plh->pegawaiPengganti;
        return $atasanLangsung;
    }
    public static function getAtasanTertinggi(array $nips){
        $atasanLangsung = Pegawai::select('atasan_langsung_id')->distinct()->whereIn('nip',$nips)->pluck('atasan_langsung_id');
        $trying = 0;
        while ( $atasanLangsung->count() > 1) {
            $trying +=1;
            if($trying>10) dd($atasanLangsung);
            $atasanLangsung = Pegawai::select('atasan_langsung_id')->distinct()->whereIn('nip',$atasanLangsung)->pluck('atasan_langsung_id');
        }
        return Pegawai::find($atasanLangsung->first());
    }

}
