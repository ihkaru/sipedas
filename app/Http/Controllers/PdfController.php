<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\Pengaturan;
use App\Models\Penugasan;
use Illuminate\Http\Request;

class PdfController extends Controller
{
    public function cetak($id){
        // if(auth()->user()) abort(403);
        $penugasans = Penugasan::with(['satuSurat','suratTugas','suratPerjadin','kegiatan','pegawai','plh'])->find($id);
        // dd($penugasans);
        $ppk = Pegawai::find(Pengaturan::key("NIP_PPK_SATER")->nilai);
        return view('surat_tugas.sendiri.pdf',['penugasans'=>$penugasans,"ppk"=>$ppk])->toHtml();
    }

}
