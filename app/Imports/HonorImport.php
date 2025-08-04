<?php

namespace App\Imports;

use App\Models\Honor;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class HonorImport implements ToModel, WithHeadingRow, WithUpserts
{
    /**
     * @param array $row Data dari satu baris di Excel.
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $tanggal_key = 'tanggal_akhir_kegiatan';
        $tanggal_value = $row[$tanggal_key] ?? null;
        $tanggal_akhir = null;

        if (!empty($tanggal_value)) {
            try {
                // Jika nilainya numerik, gunakan metode standar Excel
                if (is_numeric($tanggal_value)) {
                    $tanggal_akhir = Date::excelToDateTimeObject($tanggal_value);
                }
                // --- PERUBAHAN UTAMA DI SINI ---
                // Jika nilainya string, beri tahu Carbon formatnya secara eksplisit
                else {
                    $tanggal_akhir = Carbon::createFromFormat('d/m/Y', $tanggal_value);
                }
            } catch (\Exception $e) {
                // Jika parsing gagal karena format salah, biarkan tanggal_akhir tetap null
                // Ini mencegah seluruh proses impor gagal hanya karena satu baris.
                $tanggal_akhir = null;
            }
        }

        $harga_key = 'harga_per_satuan_honor';
        $harga_value = $row[$harga_key] ?? 0;
        $harga_bersih = (float) preg_replace('/[^\d.]/', '', $harga_value);

        $id_kegiatan = $row['id_kegiatan'] ?? null;
        $jabatan = $row['jabatan'] ?? null;
        $jenis_honor = $row['jenis_honor'] ?? null;

        if (empty($id_kegiatan) || empty($jabatan) || empty($jenis_honor)) {
            return null;
        }

        $cleanData = [
            'id_kegiatan' => $id_kegiatan,
            'jabatan' => $jabatan,
            'jenis_honor' => $jenis_honor,
            'satuan_honor' => $row['satuan_honor'] ?? null,
            'harga_per_satuan_honor' => $harga_bersih,
            'tanggal_akhir_kegiatan' => $tanggal_akhir,
        ];

        return Honor::importHonor($cleanData);
    }

    public function uniqueBy()
    {
        return 'id';
    }
}
