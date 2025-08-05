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


class AlokasiHonorImport implements ToModel, WithHeadingRow
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
            if ($c == 'ref') dd($c, $columns, $row, $res);
            if ($timestampCol->contains($c)) {
                $res[$c] = Carbon::parse(trim($row[$c]));
                continue;
            }
            $res[$c] = trim($row[$c]);
        }

        return AlokasiHonor::importAlokasiHonor($res, $row);
    }
}
