<?php

namespace App\Models;

use App\Supports\Constants;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class NomorSurat extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function penugasans(){
        return $this->hasMany(Penugasan::class,"surat_tugas_id");
    }

    public static function generateNomorSuratTugas(Carbon $tanggal_pengajuan){
        $nomorSuratTerakhir = self::
            where("tahun",$tanggal_pengajuan->year)
            ->where('jenis',Constants::JENIS_NOMOR_SURAT_TUGAS)
            ->orderBy("tanggal_nomor","desc")
            ->orderBy('nomor','desc')
            ->first();
        if(!$nomorSuratTerakhir){
            // dump("lok1");
            $nomorSuratTerakhir = self::create([
                "nomor"=>1,
                "tanggal_nomor"=>$tanggal_pengajuan,
                "jenis"=>Constants::JENIS_NOMOR_SURAT_TUGAS,
                "tahun"=>$tanggal_pengajuan->year
            ]);
            $nomorSuratBaru = $nomorSuratTerakhir;
        }else{
            $tanggal_nomor_terakhir = Carbon::parse($nomorSuratTerakhir->tanggal_nomor);
            if($tanggal_nomor_terakhir->startOfDay()<=$tanggal_pengajuan->startOfDay()){
                // dump("lok2");
                $nomorSuratBaru = self::create([
                    'nomor'=>$nomorSuratTerakhir->nomor + 1,
                    'tanggal_nomor'=>$tanggal_pengajuan,
                    'jenis'=>Constants::JENIS_NOMOR_SURAT_TUGAS,
                    "tahun"=>$tanggal_pengajuan->year
                ]);
            }
            if($tanggal_nomor_terakhir->startOfDay()>$tanggal_pengajuan->startOfDay()){
                // dump($tanggal_nomor_terakhir,$tanggal_pengajuan);
                $nomor_terakhir_sesuai_tanggal_pengajuan = self::whereDate("tanggal_nomor","<=",$tanggal_pengajuan->toDateString())
                    ->where("tahun",$tanggal_pengajuan->year)
                    ->where('jenis',Constants::JENIS_NOMOR_SURAT_TUGAS)
                    ->orderBy('tanggal_nomor','desc')
                    ->orderBy('nomor','asc')
                    ->orderBy('sub_nomor','desc')
                    ->first();
                if(!$nomor_terakhir_sesuai_tanggal_pengajuan){
                    // dump("lok3");
                    $nomor_terakhir_sesuai_tanggal_pengajuan=self::
                        orderBy('nomor','asc')
                        ->where("tahun",$tanggal_pengajuan->year)
                        ->where('jenis',Constants::JENIS_NOMOR_SURAT_TUGAS)
                        ->orderBy('sub_nomor','desc')
                        ->first();
                }else{
                    // dump("lok5");
                    $nomor_terakhir_sesuai_tanggal_pengajuan = self::
                    where('nomor',$nomor_terakhir_sesuai_tanggal_pengajuan->nomor)
                        ->where("tahun",$tanggal_pengajuan->year)
                        ->where('jenis',Constants::JENIS_NOMOR_SURAT_TUGAS)
                        ->orderBy('nomor','desc')
                        ->orderBy('sub_nomor','desc')
                        ->first();
                    // dump($nomor_terakhir_sesuai_tanggal_pengajuan->nomorSuratTugas);
                }
                // dump("lok4");
                // dump($nomor_terakhir_sesuai_tanggal_pengajuan->nomorSuratTugas);
                $nomorSuratBaru = self::create([
                    'nomor'=>$nomor_terakhir_sesuai_tanggal_pengajuan->nomor,
                    'sub_nomor'=>$nomor_terakhir_sesuai_tanggal_pengajuan->sub_nomor ? $nomor_terakhir_sesuai_tanggal_pengajuan->sub_nomor + 1 : 1,
                    'tanggal_nomor'=>$tanggal_pengajuan,
                    "tahun"=>$tanggal_pengajuan->year,
                    'jenis'=>Constants::JENIS_NOMOR_SURAT_TUGAS,
                ]);
            }

        }
        // dump("Tanggal Pengajuan: $tanggal_pengajuan");
        // dump(NomorSurat::orderBy("tanggal_nomor","asc")->orderBy('nomor',"asc")->orderBy('sub_nomor')->get()->pluck('nomor_surat_tugas'));
        return $nomorSuratBaru;
    }
    public static function generateNomorSuratPerjadin(Carbon $tanggal_pengajuan){
        $nomorSuratTerakhir = self::
            where("tahun",$tanggal_pengajuan->year)
            ->where('jenis',Constants::JENIS_NOMOR_SURAT_PERJALAN_DINAS)
            ->orderBy("tanggal_nomor","desc")
            ->orderBy('nomor','desc')
            ->first();
        if(!$nomorSuratTerakhir){
            // dump("lok1");
            $nomorSuratTerakhir = self::create([
                "nomor"=>1,
                "tanggal_nomor"=>$tanggal_pengajuan,
                "jenis"=>Constants::JENIS_NOMOR_SURAT_PERJALAN_DINAS,
                "tahun"=>$tanggal_pengajuan->year
            ]);
            $nomorSuratBaru = $nomorSuratTerakhir;
        }else{
            $tanggal_nomor_terakhir = Carbon::parse($nomorSuratTerakhir->tanggal_nomor);
            if($tanggal_nomor_terakhir->startOfDay()<=$tanggal_pengajuan->startOfDay()){
                // dump("lok2");
                $nomorSuratBaru = self::create([
                    'nomor'=>$nomorSuratTerakhir->nomor + 1,
                    'tanggal_nomor'=>$tanggal_pengajuan,
                    'jenis'=>Constants::JENIS_NOMOR_SURAT_PERJALAN_DINAS,
                    "tahun"=>$tanggal_pengajuan->year
                ]);
            }
            if($tanggal_nomor_terakhir->startOfDay()>$tanggal_pengajuan->startOfDay()){
                // dump($tanggal_nomor_terakhir,$tanggal_pengajuan);
                $nomor_terakhir_sesuai_tanggal_pengajuan = self::whereDate("tanggal_nomor","<=",$tanggal_pengajuan->toDateString())
                    ->where("tahun",$tanggal_pengajuan->year)
                    ->where('jenis',Constants::JENIS_NOMOR_SURAT_PERJALAN_DINAS)
                    ->orderBy('tanggal_nomor','desc')
                    ->orderBy('nomor','asc')
                    ->orderBy('sub_nomor','desc')
                    ->first();
                if(!$nomor_terakhir_sesuai_tanggal_pengajuan){
                    // dump("lok3");
                    $nomor_terakhir_sesuai_tanggal_pengajuan=self::
                        where("tahun",$tanggal_pengajuan->year)
                        ->where('jenis',Constants::JENIS_NOMOR_SURAT_PERJALAN_DINAS)
                        ->orderBy('nomor','asc')
                        ->orderBy('sub_nomor','desc')
                        ->first();
                }else{
                    // dump("lok5");
                    $nomor_terakhir_sesuai_tanggal_pengajuan = self::
                    where('nomor',$nomor_terakhir_sesuai_tanggal_pengajuan->nomor)
                        ->where("tahun",$tanggal_pengajuan->year)
                        ->where('jenis',Constants::JENIS_NOMOR_SURAT_PERJALAN_DINAS)
                        ->orderBy('nomor','desc')
                        ->orderBy('sub_nomor','desc')
                        ->first();
                    // dump($nomor_terakhir_sesuai_tanggal_pengajuan->nomorSuratTugas);
                }
                // dump("lok4");
                // dump($nomor_terakhir_sesuai_tanggal_pengajuan->nomorSuratTugas);
                $nomorSuratBaru = self::create([
                    'nomor'=>$nomor_terakhir_sesuai_tanggal_pengajuan->nomor,
                    'sub_nomor'=>$nomor_terakhir_sesuai_tanggal_pengajuan->sub_nomor ? $nomor_terakhir_sesuai_tanggal_pengajuan->sub_nomor + 1 : 1,
                    'tanggal_nomor'=>$tanggal_pengajuan,
                    "tahun"=>$tanggal_pengajuan->year,
                    'jenis'=>Constants::JENIS_NOMOR_SURAT_PERJALAN_DINAS,
                ]);
            }

        }
        // dump("Tanggal Pengajuan: $tanggal_pengajuan");
        // dump(NomorSurat::orderBy("tanggal_nomor","asc")->orderBy('nomor',"asc")->orderBy('sub_nomor')->get()->pluck('nomor_surat_tugas'));
        return $nomorSuratBaru;
    }

    public static function generateNomorSuratPerjanjianKerja(Carbon $tanggal_pengajuan){
        $nomorSuratTerakhir = self::
            where("tahun",$tanggal_pengajuan->year)
            ->whereRaw("MONTH(tanggal_nomor)=$tanggal_pengajuan->month")
            ->where('jenis',Constants::JENIS_NOMOR_SURAT_PERJANJIAN_KERJA)
            ->orderBy("tanggal_nomor","desc")
            ->orderBy('nomor','desc')
            ->first();
        if(!$nomorSuratTerakhir){
            // dump("lok1");
            $nomorSuratTerakhir = self::create([
                "nomor"=>1,
                "tanggal_nomor"=>$tanggal_pengajuan,
                "jenis"=>Constants::JENIS_NOMOR_SURAT_PERJANJIAN_KERJA,
                "tahun"=>$tanggal_pengajuan->year
            ]);
            $nomorSuratBaru = $nomorSuratTerakhir;
        }else{
            $tanggal_nomor_terakhir = Carbon::parse($nomorSuratTerakhir->tanggal_nomor);
            if($tanggal_nomor_terakhir->startOfDay()<=$tanggal_pengajuan->startOfDay()){
                // dump("lok2");
                $nomorSuratBaru = self::create([
                    'nomor'=>$nomorSuratTerakhir->nomor + 1,
                    'tanggal_nomor'=>$tanggal_pengajuan,
                    'jenis'=>Constants::JENIS_NOMOR_SURAT_PERJANJIAN_KERJA,
                    "tahun"=>$tanggal_pengajuan->year
                ]);
            }
            if($tanggal_nomor_terakhir->startOfDay()>$tanggal_pengajuan->startOfDay()){
                // dump($tanggal_nomor_terakhir,$tanggal_pengajuan);
                $nomor_terakhir_sesuai_tanggal_pengajuan = self::whereDate("tanggal_nomor","<=",$tanggal_pengajuan->toDateString())
                    ->where("tahun",$tanggal_pengajuan->year)
                    ->whereRaw("MONTH(tanggal_nomor)",$tanggal_pengajuan->month)
                    ->where('jenis',Constants::JENIS_NOMOR_SURAT_PERJANJIAN_KERJA)
                    ->orderBy('tanggal_nomor','desc')
                    ->orderBy('nomor','asc')
                    ->orderBy('sub_nomor','desc')
                    ->first();
                if(!$nomor_terakhir_sesuai_tanggal_pengajuan){
                    // dump("lok3");
                    $nomor_terakhir_sesuai_tanggal_pengajuan=self::
                        orderBy('nomor','asc')
                        ->where("tahun",$tanggal_pengajuan->year)
                        ->whereRaw("MONTH(tanggal_nomor)",$tanggal_pengajuan->month)
                        ->where('jenis',Constants::JENIS_NOMOR_SURAT_PERJANJIAN_KERJA)
                        ->orderBy('sub_nomor','desc')
                        ->first();
                }else{
                    // dump("lok5");
                    $nomor_terakhir_sesuai_tanggal_pengajuan = self::
                    where('nomor',$nomor_terakhir_sesuai_tanggal_pengajuan->nomor)
                        ->where("tahun",$tanggal_pengajuan->year)
                        ->where('jenis',Constants::JENIS_NOMOR_SURAT_PERJANJIAN_KERJA)
                        ->whereRaw("MONTH(tanggal_nomor)",$tanggal_pengajuan->month)
                        ->orderBy('nomor','desc')
                        ->orderBy('sub_nomor','desc')
                        ->first();
                    // dump($nomor_terakhir_sesuai_tanggal_pengajuan->nomorSuratTugas);
                }
                // dump("lok4");
                // dump($nomor_terakhir_sesuai_tanggal_pengajuan->nomorSuratTugas);
                $nomorSuratBaru = self::create([
                    'nomor'=>$nomor_terakhir_sesuai_tanggal_pengajuan->nomor,
                    'sub_nomor'=>$nomor_terakhir_sesuai_tanggal_pengajuan->sub_nomor ? $nomor_terakhir_sesuai_tanggal_pengajuan->sub_nomor + 1 : 1,
                    'tanggal_nomor'=>$tanggal_pengajuan,
                    "tahun"=>$tanggal_pengajuan->year,
                    'jenis'=>Constants::JENIS_NOMOR_SURAT_PERJANJIAN_KERJA,
                ]);
            }

        }
        // dump("Tanggal Pengajuan: $tanggal_pengajuan");
        // dump(NomorSurat::orderBy("tanggal_nomor","asc")->orderBy('nomor',"asc")->orderBy('sub_nomor')->get()->pluck('nomor_surat_tugas'));
        return $nomorSuratBaru;
    }

    protected function nomorSuratTugas(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes){
                $sub_nomor = $attributes['sub_nomor'] ?".".$attributes['sub_nomor']: "";
                $nomor = $attributes['nomor'];
                $nomor = str_pad($nomor,3,"0",STR_PAD_LEFT);
                $tanggal_nomor = Carbon::parse($attributes['tanggal_nomor']);
                $bulan = $tanggal_nomor->month;
                $bulan = str_pad($bulan,2,"0",STR_PAD_LEFT);
                $tahun = $tanggal_nomor->year;
                $res = "B-$nomor".$sub_nomor."/61041/KP.650/$bulan/$tahun";
                return $res;
        },
        );
    }
    protected function nomorSuratPerjadin(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes){
                $sub_nomor = $attributes['sub_nomor'] ?".".$attributes['sub_nomor']: "";
                $nomor = $attributes['nomor'];
                $nomor = str_pad($nomor,3,"0",STR_PAD_LEFT);
                $tanggal_nomor = Carbon::parse($attributes['tanggal_nomor']);
                $bulan = $tanggal_nomor->month;
                $bulan = str_pad($bulan,2,"0",STR_PAD_LEFT);
                $tahun = $tanggal_nomor->year;
                $res = "$nomor".$sub_nomor."/SPD/$bulan/$tahun";
                return $res;
        },
        );
    }
    protected function nomorSuratPerjanjianKerja(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes){
                $sub_nomor = $attributes['sub_nomor'] ?".".$attributes['sub_nomor']: "";
                $nomor = $attributes['nomor'];
                $nomor = str_pad($nomor,3,"0",STR_PAD_LEFT);
                $tanggal_nomor = Carbon::parse($attributes['tanggal_nomor']);
                $bulan = $tanggal_nomor->month;
                $bulan = str_pad($bulan,2,"0",STR_PAD_LEFT);
                $tahun = $tanggal_nomor->year;
                $res = "$nomor".$sub_nomor."/61041/PL.300/$bulan/$tahun";
                return $res;
        },
        );
    }

    public function alokasiHonor(){
        return $this->hasMany(AlokasiHonor::class,'surat_perjanjian_kerja_id','id');
    }


}
