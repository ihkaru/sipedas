<?php

namespace App\Imports;

use App\Models\AlokasiHonor;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts; // Impor untuk performa
use Maatwebsite\Excel\Concerns\WithChunkReading; // Impor untuk performa
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\SkipsOnFailure; // <-- IMPORT INI

class AlokasiHonorImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, WithBatchInserts, WithChunkReading
{
    private array $failures = [];

    public function getFailures(): array
    {
        return $this->failures;
    }

    /**
     * @param Failure ...$failures
     */
    public function onFailure(Failure ...$failures)
    {
        // Gabungkan semua kegagalan yang terjadi ke dalam properti kita.
        $this->failures = array_merge($this->failures, $failures);
    }

    /**
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     * @throws \Exception
     */
    public function model(array $row)
    {
        // Hapus try-catch. Biarkan exception terjadi jika ada masalah.
        // Proses ini akan ditangkap di level Action pada halaman List.
        return AlokasiHonor::createWithRelations(
            (string)$row['id_mitra'],
            (string)$row['id_honor'],
            (float)$row['target_per_satuan_honor']
        );
    }

    public function rules(): array
    {
        return [
            // Ubah 'integer' menjadi 'string' dan 'numeric'
            'id_mitra' => ['required'],
            'id_honor' => ['required', 'string'],
            'target_per_satuan_honor' => ['required', 'numeric', 'min:0'],
        ];
    }


    // Best practice untuk performa impor file besar
    public function batchSize(): int
    {
        return 500;
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
