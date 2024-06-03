<?php

namespace App\Imports;

use App\Models\Mitra;
use App\Supports\Constants;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MitrasImport implements ToModel,WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $columns = Constants::COLUMN_MITRA_IMPORT;
        $res = [];
        foreach ($columns as $c) {
            $res[$c]=trim($row[$c]);
        }
        return new Mitra($res);
    }
}
