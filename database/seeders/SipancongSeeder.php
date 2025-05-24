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
            ['nama' => 'Di Pengaju Pembayaran'], // 1
            ['nama' => 'Di PPK'], // 2
            ['nama' => 'Di PPSPM'], // 3
            ['nama' => 'Di Bendahara'], // 4
            ['nama' => 'Siap Cetak'], // 5
            ['nama' => 'Selesai'], // 6
        ]);

        // Seeder untuk status_pengajuan
        DB::table('sp_status_pengajuan')->insert([
            ['nama' => 'Diajukan'], // 1
            ['nama' => 'Disetujui dengan catatan'], // 2
            ['nama' => 'Ditunda'], // 3
            ['nama' => 'Ditolak'], // 4
            ['nama' => 'Disetujui tanpa catatan'], // 5
        ]);

        // Seeder untuk status_pembayaran
        DB::table('sp_status_pembayaran')->insert([
            ['nama' => 'Sudah CMS'], // 1
            ['nama' => 'Sudah LS'], // 2
            ['nama' => 'Belum Tersedia Dok Fisik'], // 3
            ['nama' => 'Proses Catat SPBY'], // 4
            ['nama' => 'Sudah Cair dan Diproses'], // 5
            ['nama' => 'Proses Catat LS'], // 6
            ['nama' => 'Sudah Tunai'], // 7
        ]);


        // Seeder untuk jenis_dokumen
        DB::table('sp_jenis_dokumen')->insert([
            ['nama' => 'SPM'], // 1
            ['nama' => 'SPBY'], // 2
        ]);
        DB::table('sp_sub_fungsi')->insert([
            ['nama' => 'Umum'], // 1
            ['nama' => 'Produksi'], // 2
            ['nama' => 'Distribusi'], // 3
            ['nama' => 'Sosial'], // 4
            ['nama' => 'Neraca'], // 5
            ['nama' => 'IPDS'], // 6
        ]);
    }
}
