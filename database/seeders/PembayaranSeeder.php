<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PembayaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seeder untuk posisi_dokumen
        DB::table('sp_posisi_dokumen')->insertOrIgnore([
            ['id' => 1, 'nama' => 'Di Pengaju Pembayaran'],
            ['id' => 2, 'nama' => 'Di PPK'],
            ['id' => 3, 'nama' => 'Di PPSPM'],
            ['id' => 4, 'nama' => 'Di Bendahara'],
            ['id' => 5, 'nama' => 'Siap Cetak'],
            ['id' => 6, 'nama' => 'Selesai'],
        ]);

        // Seeder untuk status_pengajuan
        DB::table('sp_status_pengajuan')->insertOrIgnore([
            ['id' => 1, 'nama' => 'Diajukan'],
            ['id' => 2, 'nama' => 'Disetujui dengan catatan'],
            ['id' => 3, 'nama' => 'Ditunda'],
            ['id' => 4, 'nama' => 'Ditolak'],
            ['id' => 5, 'nama' => 'Disetujui tanpa catatan'],
        ]);

        // Seeder untuk status_pembayaran
        DB::table('sp_status_pembayaran')->insertOrIgnore([
            ['id' => 1, 'nama' => 'Sudah CMS'],
            ['id' => 2, 'nama' => 'Sudah LS'],
            ['id' => 3, 'nama' => 'Belum Tersedia Dok Fisik'],
            ['id' => 4, 'nama' => 'Proses Catat SPBY'],
            ['id' => 5, 'nama' => 'Sudah Cair/Tunai'],
            ['id' => 6, 'nama' => 'Proses Catat LS'],
            ['id' => 7, 'nama' => 'Sudah Tunai'],
        ]);

        // Seeder untuk jenis_dokumen
        DB::table('sp_jenis_dokumen')->insertOrIgnore([
            ['id' => 1, 'nama' => 'SPD / Rampung'],
            ['id' => 2, 'nama' => 'Honor Mitra'],
            ['id' => 3, 'nama' => 'Pengadaan Barang & Jasa'],
            ['id' => 4, 'nama' => 'Lainnya'],
        ]);
    }
}
