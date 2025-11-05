<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class RekapPerjadin extends Page {
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.rekap-perjadin';

    public $year;
    public $data;

    public function mount() {
        $this->year = now()->year;
        $this->fetchData();
    }

    public function fetchData() {
        $this->data = DB::select("
            SELECT
                p.nama AS 'Nama Pegawai',
                COALESCE(SUM(DATEDIFF(pen.tgl_akhir_tugas, pen.tgl_mulai_tugas) + 1), 0) AS 'Total',
                COALESCE(SUM(CASE WHEN MONTH(pen.tgl_mulai_tugas) = 1 THEN DATEDIFF(pen.tgl_akhir_tugas, pen.tgl_mulai_tugas) + 1 ELSE 0 END), 0) AS 'Januari',
                COALESCE(SUM(CASE WHEN MONTH(pen.tgl_mulai_tugas) = 2 THEN DATEDIFF(pen.tgl_akhir_tugas, pen.tgl_mulai_tugas) + 1 ELSE 0 END), 0) AS 'Februari',
                COALESCE(SUM(CASE WHEN MONTH(pen.tgl_mulai_tugas) = 3 THEN DATEDIFF(pen.tgl_akhir_tugas, pen.tgl_mulai_tugas) + 1 ELSE 0 END), 0) AS 'Maret',
                COALESCE(SUM(CASE WHEN MONTH(pen.tgl_mulai_tugas) = 4 THEN DATEDIFF(pen.tgl_akhir_tugas, pen.tgl_mulai_tugas) + 1 ELSE 0 END), 0) AS 'April',
                COALESCE(SUM(CASE WHEN MONTH(pen.tgl_mulai_tugas) = 5 THEN DATEDIFF(pen.tgl_akhir_tugas, pen.tgl_mulai_tugas) + 1 ELSE 0 END), 0) AS 'Mei',
                COALESCE(SUM(CASE WHEN MONTH(pen.tgl_mulai_tugas) = 6 THEN DATEDIFF(pen.tgl_akhir_tugas, pen.tgl_mulai_tugas) + 1 ELSE 0 END), 0) AS 'Juni',
                COALESCE(SUM(CASE WHEN MONTH(pen.tgl_mulai_tugas) = 7 THEN DATEDIFF(pen.tgl_akhir_tugas, pen.tgl_mulai_tugas) + 1 ELSE 0 END), 0) AS 'Juli',
                COALESCE(SUM(CASE WHEN MONTH(pen.tgl_mulai_tugas) = 8 THEN DATEDIFF(pen.tgl_akhir_tugas, pen.tgl_mulai_tugas) + 1 ELSE 0 END), 0) AS 'Agustus',
                COALESCE(SUM(CASE WHEN MONTH(pen.tgl_mulai_tugas) = 9 THEN DATEDIFF(pen.tgl_akhir_tugas, pen.tgl_mulai_tugas) + 1 ELSE 0 END), 0) AS 'September',
                COALESCE(SUM(CASE WHEN MONTH(pen.tgl_mulai_tugas) = 10 THEN DATEDIFF(pen.tgl_akhir_tugas, pen.tgl_mulai_tugas) + 1 ELSE 0 END), 0) AS 'Oktober',
                COALESCE(SUM(CASE WHEN MONTH(pen.tgl_mulai_tugas) = 11 THEN DATEDIFF(pen.tgl_akhir_tugas, pen.tgl_mulai_tugas) + 1 ELSE 0 END), 0) AS 'November',
                COALESCE(SUM(CASE WHEN MONTH(pen.tgl_mulai_tugas) = 12 THEN DATEDIFF(pen.tgl_akhir_tugas, pen.tgl_mulai_tugas) + 1 ELSE 0 END), 0) AS 'Desember'
            FROM
                pegawais p
            LEFT JOIN
                penugasans pen ON p.nip = pen.nip AND YEAR(pen.tgl_mulai_tugas) = ?
            LEFT JOIN
                riwayat_pengajuans rp ON pen.id = rp.penugasan_id
            WHERE
                (pen.jenis_surat_tugas = 'PERJALAN_DINAS_DALAM_KOTA'
                 OR pen.jenis_surat_tugas = 'PERJALANAN_DINAS_LUAR_KOTA')
                AND rp.status IN (
                    'STATUS_PENGAJUAN_DICETAK',
                    'STATUS_PENGAJUAN_DICAIRKAN',
                    'STATUS_PENGAJUAN_DISETUJUI',
                    'STATUS_PENGAJUAN_DIKUMPULKAN'
                )
            GROUP BY
                p.nip, p.nama
            ORDER BY
                p.nama
        ", [$this->year]);
    }
}
