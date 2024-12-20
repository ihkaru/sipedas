<?php

namespace App\Imports;

use App\Models\Kegiatan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithUpserts;

class KegiatanImport implements ToModel,WithBatchInserts,WithChunkReading,WithStartRow,WithUpserts
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Kegiatan([
            "id"=>Str::slug(trim($row[1])),
            "nama"=>trim($row[1]),
            "tgl_awal_perjadin"=>null,
            "tgl_akhir_perjadin"=>null,
            "pj_kegiatan_id"=>["198008112005021004","200110202023021003"][rand(0,1)],
        ]);
    }
    public function chunkSize(): int
    {
        return 1000;
    }
    public function batchSize(): int
    {
        return 1000;
    }
    public function startRow(): int{
        return 2;
    }
    public function uniqueBy(){
        return "id";
    }
}
