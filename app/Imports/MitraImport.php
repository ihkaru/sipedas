<?php

namespace App\Imports;

use App\Models\Kemitraan;
use App\Models\Mitra;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;

class MitraImport implements ToModel, WithHeadingRow, WithUpserts
{
    private int $tahun;
    private string $status;

    /**
     * Terima tahun dan status dari Filament Action.
     */
    public function __construct(int $tahun, string $status)
    {
        $this->tahun = $tahun;
        $this->status = $status;
    }

    /**
     * Proses setiap baris dari file Excel.
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Maatwebsite/Excel akan mengubah header menjadi snake_case.
        // "SOBAT ID" menjadi "sobat_id".
        $sobatId = $row['sobat_id'] ?? null;

        // Lewati baris jika SOBAT ID kosong (ini akan menangani baris kosong)
        if (empty($sobatId)) {
            return null;
        }

        // Langkah 1: Update atau Buat data Mitra berdasarkan SOBAT ID.
        $mitra = Mitra::updateOrCreate(
            ['id_sobat' => $sobatId],
            [
                // Petakan kolom dari Excel ke kolom database
                'nama_1' => $row['nama_lengkap'] ?? null,
                'nama_2' => $row['nama_lengkap'] ?? null, // Atau sesuaikan jika ada kolom nama kedua
                'posisi' => $row['posisi'] ?? null,
                'status_seleksi_1_terpilih_2_tidak_terpilih' => $row['status_seleksi_1terpilih_2tidak_terpilih'] ?? null,
                'posisi_daftar' => $row['posisi_daftar'] ?? null,
                'alamat_detail' => $row['alamat_detail'] ?? null,
                'alamat_prov' => $row['alamat_prov'] ?? null,
                'alamat_kab' => $row['alamat_kab'] ?? null,
                'alamat_kec' => $row['alamat_kec'] ?? null,
                'alamat_desa' => $row['alamat_desa'] ?? null,
                'tanggal_lahir_dd_mm_yyyy' => $row['tempat_tanggal_lahir_umur'] ?? null,
                'jenis_kelamin' => $row['jenis_kelamin'] ?? null,
                'pendidikan' => $row['pendidikan'] ?? null,
                'pekerjaan' => $row['pekerjaan'] ?? null,
                'deskripsi_pekerjaan_lain' => $row['deskripsi_pekerjaan_lain'] ?? null,
                'no_telp' => $row['no_telp'] ?? null,
                'email' => $row['email'] ?? null,
            ]
        );

        // Langkah 2: Setelah Mitra berhasil dibuat/diupdate, kelola data Kemitraan-nya.
        if ($mitra) {
            Kemitraan::updateOrCreate(
                [
                    'mitra_id' => $mitra->id,
                    'tahun' => $this->tahun,
                ],
                [
                    'status' => $this->status,
                ]
            );
        }

        return $mitra;
    }

    /**
     * Tentukan kolom unik untuk operasi upsert.
     */
    public function uniqueBy()
    {
        return 'id_sobat';
    }
}
