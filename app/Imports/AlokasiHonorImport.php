<?php

namespace App\Imports;

use App\Models\AlokasiHonor;
use App\Models\NomorSurat;
use App\Supports\Constants;
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
        // dd($row);

        // $nomorSurat = NomorSurat::where('');
        // $surat_perjanjian_kerja_id = NomorSurat::generateNomorSuratPerjanjianKerja($tanggal_pengajuan);
        foreach ($columns as $c) {
            if($c == 'ref') dd($c,$columns,$row,$res);
            if($timestampCol->contains($c)){
                $res[$c]=Carbon::parse(trim($row[$c]));
                continue;
            }
            $res[$c]=trim($row[$c]);

        }

        $tanggal_pengajuan = Carbon::parse(trim($row['tanggal_penanda_tanganan_spk_oleh_petugas']));
        $id_sobat = trim($row['id_sobat']);
        $surat_perjanjian_kerja_id = AlokasiHonor::where('id_sobat',$id_sobat)->whereRaw('YEAR(tanggal_penanda_tanganan_spk_oleh_petugas)='.$tanggal_pengajuan->year)->whereRaw('MONTH(tanggal_penanda_tanganan_spk_oleh_petugas)='.$tanggal_pengajuan->month)->first()?->surat_perjanjian_kerja_id;
        // if($surat_perjanjian_kerja_id == null){
        //     dd($res);
        // }
        $surat_perjanjian_kerja_id ??= NomorSurat::generateNomorSuratPerjanjianKerja($tanggal_pengajuan)->id;
        $res['surat_perjanjian_kerja_id'] = $surat_perjanjian_kerja_id;
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
