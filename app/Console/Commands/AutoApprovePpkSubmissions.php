<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Models\Sipancong\Pengajuan;
use App\Services\Sipancong\PengajuanServices;
use App\Supports\SipancongConstants as Constants;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoApprovePpkSubmissions extends Command
{
    protected $signature = 'sipancong:auto-approve-ppk';
    protected $description = 'Mencari pengajuan yang melewati batas waktu di PPK dan menyetujuinya secara otomatis.';

    public function handle()
    {
        $this->info('Mulai pengecekan pengajuan PPK yang melewati batas waktu...');

        $maxDaysSetting = Setting::where('key', 'ppk_auto_approve_days')->first();
        $maxDays = (int) ($maxDaysSetting->value ?? 0);

        if ($maxDays <= 0) {
            $this->info('Fitur auto-approve berbasis waktu tidak aktif (nilai <= 0). Proses dihentikan.');
            return self::SUCCESS;
        }

        $cutoffDate = now()->subDays($maxDays);

        // Query yang spesifik: hanya di posisi PPK dan sudah melewati batas tanggal
        $overdueSubmissions = Pengajuan::where('posisi_dokumen_id', Constants::POSISI_PPK)
            ->where('updated_at', '<=', $cutoffDate)
            ->get();

        if ($overdueSubmissions->isEmpty()) {
            $this->info('Tidak ditemukan pengajuan yang melewati batas waktu.');
            return self::SUCCESS;
        }

        $this->info("Ditemukan {$overdueSubmissions->count()} pengajuan untuk diproses.");

        foreach ($overdueSubmissions as $pengajuan) {
            // Siapkan data seolah-olah PPK menginput form
            $dataForApproval = [
                'status_pengajuan_ppk_id' => Constants::STATUS_DISETUJUI_TANPA_CATATAN,
                'catatan_ppk' => 'Disetujui otomatis oleh sistem karena melebihi batas waktu pemeriksaan (' . $maxDays . ' hari).',
            ];

            try {
                // PENTING: Panggil service yang sudah ada agar semua logika (termasuk notifikasi) berjalan
                PengajuanServices::pemeriksaanPpk($dataForApproval, $pengajuan);
                $this->info("Pengajuan #{$pengajuan->id} ({$pengajuan->uraian_pengajuan}) berhasil disetujui otomatis.");
                Log::info("Pengajuan #{$pengajuan->id} disetujui otomatis oleh sistem (batas waktu).");
            } catch (\Throwable $th) {
                $this->error("Gagal memproses pengajuan #{$pengajuan->id}: " . $th->getMessage());
                Log::error("Gagal auto-approve pengajuan #{$pengajuan->id}", ['error' => $th->getMessage()]);
            }
        }

        $this->info('Proses otomatisasi PPK selesai.');
        return self::SUCCESS;
    }
}
