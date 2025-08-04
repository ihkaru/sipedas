<?php

namespace App\Imports;

use App\Models\KegiatanManmit;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithMapping;
// --- PERBAIKI BARIS INI ---
use PhpOffice\PhpSpreadsheet\Shared\Date; // Menggunakan namespace yang benar dari PhpSpreadsheet

class KegiatanManmitFromUploadImport implements ToModel, WithHeadingRow, WithUpserts, WithMapping
{
    /**
     * Memetakan dan mengubah data dari setiap baris.
     */
    public function map($row): array
    {
        return [
            // Header dari file excel diubah menjadi snake_case yang benar oleh maatwebsite
            'id_kegiatan'        => $row['id_kegiatan'],
            'nama_kegiatan'      => $row['nama_kegiatan'],
            'jenis_kegiatan'     => $row['jenis_kegiatan'],
            'frekuensi_kegiatan' => $row['frekuensi_kegiatan'],
            // Menggunakan kelas Date yang sudah benar untuk konversi
            'tanggal_mulai'      => is_numeric($row['tanggal_mulai']) ? Date::excelToDateTimeObject($row['tanggal_mulai']) : $row['tanggal_mulai'],
            'tanggal_selesai'    => is_numeric($row['tanggal_selesai']) ? Date::excelToDateTimeObject($row['tanggal_selesai']) : $row['tanggal_selesai'],
        ];
    }

    /**
     * @param array $row data yang sudah bersih dari method map()
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        if (empty($row['id_kegiatan'])) {
            return null;
        }

        return new KegiatanManmit([
            'id'                      => $row['id_kegiatan'],
            'nama'                    => $row['nama_kegiatan'],
            'jenis_kegiatan'          => $row['jenis_kegiatan'] ?? 'SURVEI',
            'tgl_mulai_pelaksanaan'   => $row['tanggal_mulai'] ?? null,
            'tgl_akhir_pelaksanaan'   => $row['tanggal_selesai'] ?? null,
            'frekuensi_kegiatan'      => $row['frekuensi_kegiatan'] ?? null,
        ]);
    }

    public function uniqueBy()
    {
        return 'id';
    }
}
