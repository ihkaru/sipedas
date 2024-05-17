<?php

namespace App\Imports;

use App\Models\AlokasiHonor;
use App\Supports\Constants;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;


class AlokasiHonorImport implements ToModel,WithBatchInserts,WithChunkReading,WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $columns = Constants::COLUMN_ALOKASI_HONOR_IMPORT;
        $res = [];
        // dd($row);
        foreach ($columns as $c) {
            $res[$c]=trim($row[$c]);
        }
        return new AlokasiHonor($res);
    }
    public function chunkSize(): int
    {
        return 1000;
    }
    public function batchSize(): int
    {
        return 1000;
    }
}
