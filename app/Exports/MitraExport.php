<?php

namespace App\Exports;

use App\Models\Mitra;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Collection;

class MitraExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping {
    protected ?Collection $records;

    public function __construct(?Collection $records = null) {
        $this->records = $records;
    }

    public function collection() {
        // Jika ada records spesifik (dari bulk action), gunakan itu
        // Jika tidak, ambil semua data
        return $this->records ?? Mitra::with('kemitraans')->get();
    }

    public function headings(): array {
        return [
            'ID Sobat',
            'Nama',
            'NIK',
            'Jenis Kelamin',
            'Email',
            'No Telp',
            'Pekerjaan',
            'Pendidikan',
            'Kabupaten Domisili',
            'Kecamatan Domisili',
            'Desa Domisili',
            'Alamat Detail',
            'Status Kemitraan',
            'Posisi',
            'Username',
            'NPWP',
            'Catatan',
        ];
    }

    public function map($mitra): array {
        // Format status kemitraan
        $statusKemitraan = $mitra->kemitraans->map(function ($kemitraan) {
            return $kemitraan->tahun . ': ' . $kemitraan->status;
        })->implode(', ') ?: 'Belum Ada';

        return [
            $mitra->id_sobat,
            $mitra->nama_1,
            $mitra->nik,
            $mitra->jenis_kelamin,
            $mitra->email,
            $mitra->no_telp,
            $mitra->pekerjaan,
            $mitra->pendidikan,
            $mitra->kabupaten_name,
            $mitra->kecamatan_name,
            $mitra->desa_name,
            $mitra->alamat_detail,
            $statusKemitraan,
            $mitra->posisi,
            $mitra->username,
            $mitra->npwp,
            $mitra->catatan,
        ];
    }
}
