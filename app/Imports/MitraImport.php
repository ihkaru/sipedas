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

        // Tangani data alamat yang mungkin berformat "(Kode) Nama" pada file KEPKA
        $provRaw = $row['alamat_provinsi'] ?? $row['alamat_prov'] ?? null;
        $kabRaw = $row['alamat_kab_kota'] ?? $row['alamat_kabkota'] ?? $row['alamat_kab'] ?? null;
        $kecRaw = $row['alamat_kecamatan'] ?? $row['alamat_kec'] ?? null;
        $desaRaw = $row['alamat_desa_kel'] ?? $row['alamat_desakel'] ?? $row['alamat_desa'] ?? null;

        $provCode = $this->parseCode($provRaw);
        $kabCode = $this->parseCode($kabRaw);
        $kecCode = $this->parseCode($kecRaw);
        $desaCode = $this->parseCode($desaRaw);

        $kabName = $this->parseName($kabRaw);
        $kecName = $this->parseName($kecRaw);
        $desaName = $this->parseName($desaRaw);

        // Langkah 1: Update atau Buat data Mitra berdasarkan SOBAT ID.
        $mitra = Mitra::updateOrCreate(
            ['id_sobat' => $sobatId],
            [
                // Petakan kolom dari Excel ke kolom database
                'sobat_id' => $sobatId,
                'nama_1' => $row['nama_lengkap'] ?? null,
                'nama_2' => $row['nama_lengkap'] ?? null,
                'posisi' => $row['posisi'] ?? null,
                'status_seleksi_1_terpilih_2_tidak_terpilih' => $row['status_seleksi'] ?? $row['status_seleksi_1terpilih_2tidak_terpilih'] ?? null,
                'posisi_daftar' => $row['posisi_daftar'] ?? null,
                'alamat_detail' => $row['alamat_detail'] ?? null,
                'alamat_prov' => $provCode,
                'alamat_kab' => $kabCode,
                'alamat_kec' => $kecCode,
                'alamat_desa' => $desaCode,
                'kabupaten_domisili' => $kabName,
                'kecamatan_domisili' => $kecName,
                'desa_domisili' => $desaName,
                'tanggal_lahir_dd_mm_yyyy' => $row['tempat_tanggal_lahir'] ?? $row['tempat_tanggal_lahir_umur'] ?? null,
                'jenis_kelamin' => $row['jenis_kelamin'] ?? null,
                'pendidikan' => $row['pendidikan'] ?? null,
                'pekerjaan' => $row['pekerjaan'] ?? null,
                'deskripsi_pekerjaan_lain' => $row['deskripsi_pekerjaan_lain'] ?? null,
                'no_telp' => $row['no_telp'] ?? null,
                'email' => $row['email'] ?? null,

                // Riwayat keikutsertaan survei
                'mengikuti_pendataan_bps' => $row['pernah_pendataan_bps'] ?? null,
                'sp' => $row['pernah_sp'] ?? null,
                'st' => $row['pernah_st'] ?? null,
                'se' => $row['pernah_se'] ?? null,
                'susenas' => $row['pernah_susenas'] ?? null,
                'sakernas' => $row['pernah_sakernas'] ?? null,
                'sbh' => $row['pernah_sbh'] ?? null,
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

    /**
     * Mengekstrak kode angka di dalam tanda kurung, atau mengembalikan nilai asli jika tidak ada.
     */
    private function parseCode(mixed $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        $valueStr = (string)$value;
        if (preg_match('/\((.*?)\)/', $valueStr, $matches)) {
            return trim($matches[1]);
        }

        return trim($valueStr);
    }

    /**
     * Mengekstrak nama setelah tanda kurung tutup ')', atau mengembalikan nilai asli jika tidak ada.
     */
    private function parseName(mixed $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        $valueStr = (string)$value;
        if (str_contains($valueStr, ')')) {
            $parts = explode(')', $valueStr, 2);
            return isset($parts[1]) ? trim($parts[1]) : trim($valueStr);
        }

        return trim($valueStr);
    }
}

