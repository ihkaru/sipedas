<?php

namespace App\Imports;

use App\Models\NomorSurat;
use App\Supports\Constants;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithUpserts;

class NomorSuratImport implements ToModel,WithBatchInserts,WithChunkReading,WithStartRow,WithUpserts
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new NomorSurat([
            'nomor'=>trim($row[0]),
            'sub_nomor'=>(int) trim($row[1]) == 0 ? null : (int) trim($row[1]),
            'tanggal_nomor'=>Carbon::parse(trim($row[2])),
            'tahun'=>trim($row[3]),
            'jenis'=>Constants::JENIS_NOMOR_SURAT_TUGAS
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
        return ['nomor','sub_nomor','tahun'];
    }
}
