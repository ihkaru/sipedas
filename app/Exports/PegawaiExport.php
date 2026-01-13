<?php

namespace App\Exports;

use App\Models\Pegawai;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Collection;

class PegawaiExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping {
    protected ?Collection $records;

    public function __construct(?Collection $records = null) {
        $this->records = $records;
    }

    public function collection() {
        // Jika ada records spesifik (dari bulk action), gunakan itu
        // Jika tidak, ambil semua data
        return $this->records ?? Pegawai::with('atasanLangsung')->get();
    }

    public function headings(): array {
        return [
            'Nama',
            'NIP',
            'NIP 9',
            'Golongan',
            'Pangkat',
            'Jabatan',
            'Email',
            'Atasan Langsung',
            'Unit Kerja',
            'Panggilan',
            'Nomor WA',
        ];
    }

    public function map($pegawai): array {
        return [
            $pegawai->nama,
            $pegawai->nip,
            $pegawai->nip9,
            $pegawai->golongan,
            $pegawai->pangkat,
            $pegawai->jabatan,
            $pegawai->email,
            $pegawai->atasanLangsung?->nama ?? '-',
            $pegawai->unit_kerja,
            $pegawai->panggilan,
            $pegawai->nomor_wa,
        ];
    }
}
