<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class MitraTemplateExport implements WithHeadings, ShouldAutoSize
{
    /**
     * Mendefinisikan baris header untuk template Excel.
     * Sesuai dengan contoh yang Anda berikan.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'Nama Lengkap',
            'Posisi',
            'Status Seleksi (1=Terpilih, 2=Tidak Terpilih)',
            'Posisi Daftar',
            'Alamat Detail',
            'Alamat Prov',
            'Alamat Kab',
            'Alamat Kec',
            'Alamat Desa',
            'Tempat, Tanggal Lahir (Umur)*',
            'Jenis Kelamin',
            'Pendidikan',
            'Pekerjaan',
            'Deskripsi Pekerjaan Lain',
            'No Telp',
            'SOBAT ID', // Kolom kunci untuk upsert
            'Email',
            'Nilai Ujian',
            'Waktu Mulai',
            'Waktu Submit',
            'Durasi (menit)',
            'Remedial (kali)',
            'Status PI',
            'Waktu PI',
        ];
    }
}
