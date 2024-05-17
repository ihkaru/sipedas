<?php

namespace App\Http\Controllers;

use App\Models\AlokasiHonor;
use App\Models\Pegawai;
use App\Models\Pengaturan;
use App\Models\Penugasan;
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
        $alokasiHonor = AlokasiHonor::get();
        $tahun = request('tahun') ?? now()->year;
        $bulan = request('bulan') ?? now()->month;
        return view('kontrak.pdf',[
            'alokasiHonor'=>$alokasiHonor,
            'tahun'=>$tahun,
            'bulan'=>$bulan
        ]);
    }

}
