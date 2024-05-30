<?php

namespace App\Imports;

use App\Models\AlokasiHonor;
use App\Models\NomorSurat;
use App\Supports\Constants;
use App\Supports\TanggalMerah;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;


class AlokasiHonorImport implements ToModel,WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $columns = Constants::COLUMN_ALOKASI_HONOR_IMPORT;
        $timestampCol = collect(Constants::COLUMN_TIMESTAMP_ALOKASI_HONOR_IMPORT);
        $res = [];
        foreach ($columns as $c) {
            if($c == 'ref') dd($c,$columns,$row,$res);
            if($timestampCol->contains($c)){
                $res[$c]=Carbon::parse(trim($row[$c]));
                continue;
            }
            $res[$c]=trim($row[$c]);

        }


        $tanggal_pengajuan_spk = TanggalMerah::getNextWorkDay(Carbon::parse(trim($row['tanggal_penanda_tanganan_spk_oleh_petugas'])),step: -1);
    // if($tanggal_pengajuan_spk->year == 2024 && $tanggal_pengajuan_spk->month == 3 && $tanggal_pengajuan_spk == 31) dd($tanggal_pengajuan_spk,"haha");
        $tes=0;
        while($tanggal_pengajuan_spk<Carbon::parse("2024-01-02")){
            $tes+=1;
            if($tes>10) dd($tanggal_pengajuan_spk,"While inf");
            $tanggal_pengajuan_spk = TanggalMerah::getNextWorkDay($tanggal_pengajuan_spk->addDay());
        }
        // dump("Tanggal SPK Sukses");
        $res['tanggal_penanda_tanganan_spk_oleh_petugas'] = $tanggal_pengajuan_spk->toDateString();
        $id_sobat = trim($row['id_sobat']);
        // dump("Try:".$tanggal_pengajuan_spk->month);
        $surat_perjanjian_kerja_id = AlokasiHonor::where('id_sobat',$id_sobat)
                                    ->whereRaw('YEAR(tanggal_penanda_tanganan_spk_oleh_petugas) = ?',[$tanggal_pengajuan_spk->year])
                                    ->whereRaw('MONTH(tanggal_penanda_tanganan_spk_oleh_petugas) = ?',[$tanggal_pengajuan_spk->month])
                                    ->first()?->surat_perjanjian_kerja_id;

        $surat_perjanjian_kerja_id ??= NomorSurat::generateNomorSuratPerjanjianKerja($tanggal_pengajuan_spk)->id;
        $res['surat_perjanjian_kerja_id'] = $surat_perjanjian_kerja_id;
        if($res['nama_petugas'] == "Andre"){
            // dump($tanggal_pengajuan_spk->toDateString(),NomorSurat::find($surat_perjanjian_kerja_id)->nomor_surat_perjanjian_kerja,$res['nama_kegiatan'],$row['tanggal_penanda_tanganan_spk_oleh_petugas']);
        }

        $tanggal_pengajuan_bast =TanggalMerah::getNextWorkDay(Carbon::parse(trim($row['tanggal_akhir_kegiatan']))->addDay(),step: -1);
        // if($tanggal_pengajuan_bast->toDateString() == "2024-03-31") dd("haihai");
        // dump($tanggal_pengajuan_bast->dayName);
        if(TanggalMerah::isLibur($tanggal_pengajuan_bast)) dd("haha1");
        $nomorSuratBast = AlokasiHonor::where('id_kegiatan',$res['id_kegiatan'])
            ->where('id_sobat',$res['id_sobat'])
            ->whereBetween('tanggal_akhir_kegiatan',[Carbon::parse($tanggal_pengajuan_bast)->startOfMonth(),Carbon::parse($tanggal_pengajuan_bast)->endOfMonth()])
            ->whereNotNull('surat_bast_id')->first()?->suratBast;
        if(Carbon::parse($nomorSuratBast?->tanggal_nomor)->dayName=="Minggu") dd("hahao");
        if(!$nomorSuratBast){
            // dump("Try: ".$tanggal_pengajuan_bast->toDateString().", Is Libur: ".(TanggalMerah::isLibur($tanggal_pengajuan_bast)));
            $nomorSuratBast = NomorSurat::generateNomorSuratBast($tanggal_pengajuan_bast);
        }
        // dd($tanggal_pengajuan_bast,"haha1");

        // if(collect(TanggalMerah::getLiburDates())->contains(Carbon::parse($nomorSuratBast->tanggal_nomor)->toDateString())) dd($nomorSuratBast);
        dump($nomorSuratBast?->nomor_surat_bast);
        $surat_bast_id ??= $nomorSuratBast->id;
        $res['surat_bast_id'] = $surat_bast_id;
        // if(TanggalMerah::isLibur(now()->addDay(2))) dd($nomorSuratBast);
        return new AlokasiHonor($res);
    }
    // public function chunkSize(): int
    // {
    //     return 1000;
    // }
    // public function batchSize(): int
    // {
    //     return 1000;
    // }
}
