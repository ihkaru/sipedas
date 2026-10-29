<?php

namespace Database\Seeders;

use App\Models\Pengaturan;
use Illuminate\Database\Seeder;

class PengaturanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $provinsi = config('satker.provinsi', 'KALIMANTAN BARAT');
        $kabupaten = config('satker.kabupaten', 'MEMPAWAH');
        $tingkat = config('satker.tingkat', 'kako');
        $kakoLabel = ($tingkat === 'kota' ? 'KOTA ' : 'KABUPATEN ').strtoupper($kabupaten);

        $nipKepala = config('satker.pejabat.kepala_nip', '196908031992111001');
        $nipPpk = config('satker.pejabat.ppk_nip', '199306062016021001');
        $nipKasubbag = config('satker.pejabat.kasubbag_tu_nip', '198008112005021004');
        $nipBendahara = config('satker.pejabat.bendahara_nip', '198801012010121001');
        $approvalMode = config('satker.defaults.st_approval_mode', 'delegasi_ppspm');

        Pengaturan::updateOrCreate(
            ['key' => 'SURTUG_JML_HARI_BATAS_PENGEMBALIAN'],
            ['nilai' => 13, 'deskripsi' => 'Jumlah hari untuk batas pengembalian dari tanggal terakhir penugasan']
        );
        Pengaturan::updateOrCreate(
            ['key' => 'SURTUG_JML_HARI_MULAI_PENGINGAT_SURTUG'],
            ['nilai' => 10, 'deskripsi' => 'Jumlah hari untuk memulai pengingat pengembalian dari tanggal terakhir penugasan']
        );
        Pengaturan::updateOrCreate(
            ['key' => 'SURTUG_JML_HARI_BATAS_PENCAIRAN'],
            ['nilai' => 17, 'deskripsi' => 'Jumlah hari untuk batas pencairan dari tanggal terakhir penugasan']
        );
        Pengaturan::updateOrCreate(
            ['key' => 'ID_PLH_DEFAULT'],
            ['nilai' => $nipKepala, 'deskripsi' => 'NIP Kepala Satker / PLH Default']
        );
        Pengaturan::updateOrCreate(
            ['key' => 'NAMA_PROVINSI'],
            ['nilai' => strtoupper($provinsi), 'deskripsi' => 'Nama Provinsi Satuan Kerja']
        );
        Pengaturan::updateOrCreate(
            ['key' => 'NAMA_KAKO'],
            ['nilai' => $kakoLabel, 'deskripsi' => 'Nama Kabupaten Satuan Kerja']
        );
        Pengaturan::updateOrCreate(
            ['key' => 'LEVEL_SATUAN_KERJA'],
            ['nilai' => strtoupper($tingkat), 'deskripsi' => 'Level satuan kerja: PROVINSI, KAKO']
        );
        Pengaturan::updateOrCreate(
            ['key' => 'NIP_PPK_SATER'],
            ['nilai' => $nipPpk, 'deskripsi' => 'NIP dari PPK Satker']
        );
        Pengaturan::updateOrCreate(
            ['key' => 'NIP_KASUBBAG_TU'],
            ['nilai' => $nipKasubbag, 'deskripsi' => 'NIP Kasubbag TU / PPSPM Satker']
        );
        Pengaturan::updateOrCreate(
            ['key' => 'NIP_BENDAHARA'],
            ['nilai' => $nipBendahara, 'deskripsi' => 'NIP Bendahara Pengeluaran Satker']
        );
        Pengaturan::updateOrCreate(
            ['key' => 'st_approval_mode'],
            ['nilai' => $approvalMode, 'deskripsi' => 'Mode Persetujuan Surat Tugas']
        );
        Pengaturan::updateOrCreate(
            ['key' => 'st_non_sppd_mode'],
            ['nilai' => config('satker.defaults.st_non_sppd_mode', 'auto_approve_notify'), 'deskripsi' => 'Mode Persetujuan Surat Tugas Non-SPPD']
        );
    }
}
