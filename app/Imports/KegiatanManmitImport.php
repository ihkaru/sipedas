<?php

namespace App\Imports;

use App\Models\AlokasiHonor;
use App\Models\KegiatanManmit;
use App\Models\Penugasan;
use App\Supports\Constants;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;

class KegiatanManmitImport implements ToModel,WithHeadingRow,WithUpserts
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $columns = Constants::COLUMN_KEGIATAN_MANMIT_IMPORT;
        $timestampCol = collect(Constants::COLUMN_TIMESTAMP_KEGIATAN_MANMIT_IMPORT);
        $res = [];
        foreach ($columns as $c) {
            $c = trim($c);
            if($c == 'ref') dd($c,$columns,$row,$res);
            if($timestampCol->contains($c)){
                $res[$c]=Carbon::parse(trim($row[$c]));
                continue;
            }
            // dump($c,$columns,$row,$res);
            $res[$c]=trim($row[$c]);

        }

        return KegiatanManmit::importKegiatanManmit($res);
    }
    // public function chunkSize(): int
    // {
    //     return 1000;
    // }
    // public function batchSize(): int
    // {
    //     return 1000;
    // }
    public function uniqueBy()
    {
        return 'id';
    }
}
