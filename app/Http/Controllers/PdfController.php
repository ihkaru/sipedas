<?php

namespace App\Http\Controllers;

use App\Models\AlokasiHonor;
use App\Models\Pegawai;
use App\Models\Pengaturan;
use App\Models\Penugasan;
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
        return view('surat_tugas.sendiri.pdf',['penugasans'=>$penugasans,"ppk"=>$ppk])->toHtml();
    }
    public function cetakKontrak(){
        $alokasiHonor = AlokasiHonor::with('suratPerjanjianKerja')->get();
        // dd($alokasiHonor->first());
        $tahun = request('tahun') ?? now()->year;
        $bulan = request('bulan') ?? now()->month;
        $bulan = str_pad($bulan,2,"0",STR_PAD_LEFT);
        $tanggalPengajuan = Carbon::parse("$tahun-$bulan-01");
        $ppk = Pegawai::find(Pengaturan::key("NIP_PPK_SATER")->nilai);
        return view('kontrak.pdf',[
            'alokasiHonor'=>$alokasiHonor,
            'tahun'=>$tanggalPengajuan->year,
            'bulan'=>$tanggalPengajuan->month,
            'ppk'=>$ppk,
            'c'
        ]);
    }

}
