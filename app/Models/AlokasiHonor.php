<?php

namespace App\Models;

use App\Supports\Constants;
use App\Supports\TanggalMerah;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class AlokasiHonor extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function suratPerjanjianKerja(){
        return $this->belongsTo(NomorSurat::class,"surat_perjanjian_kerja_id","id");
    }
    public function suratBast(){
        return $this->belongsTo(NomorSurat::class,"surat_bast_id","id");
    }
    public static function importAlokasiHonor($res,$row){

        $tanggal_pengajuan_spk = TanggalMerah::getNextWorkDay(Carbon::parse(trim($row['tanggal_penanda_tanganan_spk_oleh_petugas'])),step: -1);

        $tes=0;
        while($tanggal_pengajuan_spk < Carbon::parse("2024-01-02")){
            $tes+=1;
            if($tes>10) dd($tanggal_pengajuan_spk,"While inf");
            $tanggal_pengajuan_spk = TanggalMerah::getNextWorkDay($tanggal_pengajuan_spk->addDay());
        }

        $res['tanggal_penanda_tanganan_spk_oleh_petugas'] = $tanggal_pengajuan_spk->toDateString();
        $id_sobat = trim($row['id_sobat']);

        $surat_perjanjian_kerja_id = AlokasiHonor::where('id_sobat',$id_sobat)
                                    ->whereRaw('YEAR(tanggal_penanda_tanganan_spk_oleh_petugas) = ?',[$tanggal_pengajuan_spk->year])
                                    ->whereRaw('MONTH(tanggal_penanda_tanganan_spk_oleh_petugas) = ?',[$tanggal_pengajuan_spk->month])
                                    ->first()?->surat_perjanjian_kerja_id;

        $surat_perjanjian_kerja_id ??= NomorSurat::generateNomorSuratPerjanjianKerja($tanggal_pengajuan_spk)->id;
        $res['surat_perjanjian_kerja_id'] = $surat_perjanjian_kerja_id;

        $tanggal_pengajuan_bast =TanggalMerah::getNextWorkDay(Carbon::parse(trim($row['tanggal_akhir_kegiatan'])),step: -1);
        if($res['id_honor'] == "SEP24-LAPANGAN-PPL-PENDATAAN"){
            if(!$tanggal_pengajuan_bast->isSameDay(Carbon::parse("2024-06-28")))
            {
                dd($res['id_honor'],$tanggal_pengajuan_bast,$row['tanggal_akhir_kegiatan'],Carbon::parse(trim($row['tanggal_akhir_kegiatan']))->addDay());
            }
        }

        if(TanggalMerah::isLibur($tanggal_pengajuan_bast)) dd("haha1");
        $nomorSuratBast = AlokasiHonor::where('id_kegiatan',$res['id_kegiatan'])
            ->where('id_sobat',$res['id_sobat'])
            ->whereBetween('tanggal_akhir_kegiatan',[Carbon::parse($tanggal_pengajuan_bast)->startOfMonth(),Carbon::parse($tanggal_pengajuan_bast)->endOfMonth()])
            ->whereNotNull('surat_bast_id')->first()?->suratBast;
        if($nomorSuratBast && Carbon::parse($nomorSuratBast?->tanggal_nomor)->dayName=="Minggu"){
            dump($nomorSuratBast);
            dd("hahao");
        }
        if(!$nomorSuratBast){
            $nomorSuratBast = NomorSurat::generateNomorSuratBast($tanggal_pengajuan_bast);
        }

        $surat_bast_id ??= $nomorSuratBast->id;
        $res['surat_bast_id'] = $surat_bast_id;

        return new AlokasiHonor($res);
    }

}
