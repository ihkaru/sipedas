<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SipancongSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seeder untuk posisi_dokumen
        DB::table('sp_posisi_dokumen')->insert([
            ['nama' => 'Di Pengaju Pembayaran'],
            ['nama' => 'Di PPK'],
            ['nama' => 'Di PPSPM'],
            ['nama' => 'Di Bendahara'],
        ]);

        // Seeder untuk status_pengajuan
        DB::table('sp_status_pengajuan')->insert([
            ['nama' => 'Diajukan'],
            ['nama' => 'Disetujui dengan catatan'],
            ['nama' => 'Ditunda'],
            ['nama' => 'Ditolak'],
        ]);

        // Seeder untuk status_pembayaran
        DB::table('sp_status_pembayaran')->insert([
            ['nama' => 'Sudah CMS'],
            ['nama' => 'Sudah LS'],
            ['nama' => 'Belum Tersedia Dok Fisik'],
            ['nama' => 'Proses Catat SPBY'],
            ['nama' => 'Sudah Cair dan Diproses'],
            ['nama' => 'Proses Catat LS'],
        ]);

        // Seeder untuk jenis_dokumen
        DB::table('sp_jenis_dokumen')->insert([
            ['nama' => 'SPM'],
            ['nama' => 'SPBY'],
        ]);
        DB::table('sp_sub_fungsi')->insert([
            ['nama' => 'Umum'],
            ['nama' => 'Produksi'],
            ['nama' => 'Distribusi'],
            ['nama' => 'Sosial'],
            ['nama' => 'Neraca'],
            ['nama' => 'IPDS'],
        ]);
    }
}
