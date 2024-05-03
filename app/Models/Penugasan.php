<?php

namespace App\Models;

use App\DTO\PenugasanCreation;
use App\Supports\Constants;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Casts\Attribute;


class Penugasan extends Model
{
    use HasFactory;
    protected $guarded=[];

    protected function casts(): array
    {
        return [
            'plh_id' => 'string',
        ];
    }

    protected function jenisSurat(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => Constants::JENIS_SURAT_TUGAS_OPTIONS[$attributes["jenis_surat_tugas"]],
        );
    }
    protected function jenisTransportasi(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => Constants::JENIS_TRANSPORTASI_OPTIONS[$attributes["transportasi"]],
        );
    }



    public function pegawai(){
        return $this->belongsTo(Pegawai::class,"nip","nip");
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
    public function plh(){
        return $this->belongsTo(Pegawai::class,"plh_id","nip");
    }
    public function riwayatPengajuan(){
        return $this->hasOne(RiwayatPengajuan::class,"penugasan_id","id");
    }
    public function kegiatan(){
        return $this->belongsTo(Kegiatan::class,"kegiatan_id","id");
    }
    public static function ajukan(PenugasanCreation $penugasanDTO){
        $now = now()->toDateTimeString();
        $res = 0;
        $orderedUuid = (string) Str::orderedUuid();
        $pegawaiPlh = Plh::getPlhAktif($penugasanDTO->tglMulaiTugas,true);
        foreach($penugasanDTO->nips as $n){
            $pengajuan = self::create([
                "nip"=>$n,
                "kegiatan_id"=>$penugasanDTO->kegiatanId,
                "tgl_mulai_tugas"=>Carbon::parse($penugasanDTO->tglMulaiTugas)->toDateTimeString(),
                "tgl_akhir_tugas"=>Carbon::parse($penugasanDTO->tglAkhirTugas)->toDateTimeString(),
                "tbh_hari_jalan_awal"=>$penugasanDTO->tbhHariJalanAwal,
                "tbh_hari_jalan_akhir"=>$penugasanDTO->tbhHariJalanAkhir,
                "prov_id"=>$penugasanDTO->provId,
                "kabkot_id"=>$penugasanDTO->kabkotId,
                "kecamatan_id"=>$penugasanDTO->kecamatanId,
                "desa_kel_id"=>$penugasanDTO->desaKelId,
                "surat_tugas_id" => $orderedUuid,
                "jenis_surat_tugas" => $penugasanDTO->jenisSuratTugas,
                "plh_id"=>$pegawaiPlh->nip,
                "transportasi"=>$penugasanDTO->transportasi,
            ]);
            RiwayatPengajuan::kirim([$pengajuan->id]);
            if($pengajuan) $res+=1;
        }
        if($res == count($penugasanDTO->nips)) return true;
        return null;

    }
    public static function ajukanArr($data){

    }
}
