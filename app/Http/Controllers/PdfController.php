<?php

namespace App\Http\Controllers;

use App\Models\AlokasiHonor;
use App\Models\Pegawai;
use App\Models\Pengaturan;
use App\Models\Penugasan;
use App\Models\Plh;
use App\Supports\Constants;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;

class PdfController extends Controller
{
    public function cetakPenugasan($id){
        // if(auth()->user()) abort(403);
        $penugasans = Penugasan::with(['satuSurat','suratTugas','suratPerjadin','kegiatan','pegawai','plh'])->find($id);
        // dd($penugasans);
        $ppk = Pegawai::find(Pengaturan::key("NIP_PPK_SATER")->nilai);
        $plhAktifSaatPengajuan = Plh::getPlhAktif($penugasans->tgl_pengajuan_tugas,true);
        $plhAktifSaatPerjalanan = Plh::getPlhAktif($penugasans->tgl_mulai_tugas,true);
        return view('surat_tugas.sendiri.pdf',[
            'penugasans'=>$penugasans,
            "ppk"=>$ppk,
            'namaSatker'=>Pengaturan::key('NAMA_KAKO')->nilai,
            'namaSatkerTanpaLevelAdministrasi'=>Pengaturan::namaSatker(false),
            'plhAktifSaatPengajuan'=>$plhAktifSaatPengajuan,
            'plhAktifSaatPerjalanan'=>$plhAktifSaatPerjalanan,
        ])->toHtml();
    }
    public function cetakPenugasanBersama($id){
        // if(auth()->user()) abort(403);
        $penugasan = Penugasan::find($id);
        $penugasans = $penugasan->suratTugasBersamaDisetujui(['satuSurat','suratTugas','suratPerjadin','kegiatan','pegawai','plh']);
        // dd($penugasans);
        $ppk = Pegawai::find(Pengaturan::key("NIP_PPK_SATER")->nilai);
        $plhAktifSaatPengajuan = Plh::getPlhAktif($penugasans->first()->tgl_pengajuan_tugas,true);
        $plhAktifSaatPerjalanan = Plh::getPlhAktif($penugasans->first()->tgl_mulai_tugas,true);
        // dd($plhAktifSaatPengajuan,$plhAktifSaatPerjalanan);
        return view('surat_tugas.bersama.pdf',[
            'penugasans'=>$penugasans,
            "ppk"=>$ppk,
            'namaSatker'=>Pengaturan::key('NAMA_KAKO')->nilai,
            'namaSatkerTanpaLevelAdministrasi'=>Pengaturan::namaSatker(false),
            "plhAktifSaatPengajuan"=>$plhAktifSaatPengajuan,
            "plhAktifSaatPerjalanan"=>$plhAktifSaatPerjalanan,
        ])->toHtml();
    }
    public function cetakKontrak(){
        $alokasiHonor = AlokasiHonor::with('suratPerjanjianKerja')->whereHas('suratPerjanjianKerja',function($q){return $q->where('jenis',Constants::JENIS_NOMOR_SURAT_PERJANJIAN_KERJA);})->orderBy('tanggal_akhir_kegiatan')->get();
        // dd($alokasiHonor->first());
        $tahun = request('tahun') ?? now()->year;
        $bulan = request('bulan') ?? now()->month;
        $id_honor = request('id_honor') ?? null;
        $bulan = str_pad($bulan,2,"0",STR_PAD_LEFT);
        $tanggalPengajuan = Carbon::parse("$tahun-$bulan-01");
        $ppk = Pegawai::find(Pengaturan::key("NIP_PPK_SATER")->nilai);
        return view('kontrak.pdf',[
            'alokasiHonor'=>$alokasiHonor,
            'tahun'=>$tanggalPengajuan->year,
            'bulan'=>$tanggalPengajuan->month,
            'ppk'=>$ppk,
            'id_honor'=>$id_honor,
        ]);
    }
    public function cetakBast(){
        $alokasiHonor = AlokasiHonor::with('suratBast')->whereHas('suratBast',function($q){return $q->where('jenis',Constants::JENIS_NOMOR_SURAT_BAST);})->orderBy('tanggal_akhir_kegiatan')->get();
        // dd($alokasiHonor->first());
        $tahun = request('tahun') ?? now()->year;
        $bulan = request('bulan') ?? now()->month;
        $bulan = str_pad($bulan,2,"0",STR_PAD_LEFT);
        $tanggalPengajuan = Carbon::parse("$tahun-$bulan-01");
        $id_honor = request('id_honor') ?? null;
        $ppk = Pegawai::find(Pengaturan::key("NIP_PPK_SATER")->nilai);
        return view('bast.pdf',[
            'alokasiHonor'=>$alokasiHonor,
            'tahun'=>$tanggalPengajuan->year,
            'bulan'=>$tanggalPengajuan->month,
            'id_honor'=>$id_honor,
            'ppk'=>$ppk,
        ]);
    }

}
