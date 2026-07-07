<?php

namespace App\Console\Commands;

use App\Models\Honor;
use App\Services\HonorTanggalService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class FixHonorTanggalPerjanjian extends Command
{
    protected $signature   = 'honor:fix-tanggal {--dry-run : Tampilkan rencana tanpa mengubah data}';
    protected $description = 'Perbaiki tanggal_mulai/akhir_perjanjian, penanda_tanganan SPK, dan tanggal_nomor BAST/SPK agar konsisten dengan honor.tanggal_akhir_kegiatan.';

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        $this->info($isDryRun
            ? '🔍 DRY RUN — tidak ada perubahan yang akan disimpan.'
            : '🔧 Menjalankan perbaikan data...'
        );

        // Cari honor ID yang alokasi-nya tidak konsisten
        $honorIdsToFix = \Illuminate\Support\Facades\DB::table('alokasi_honors')
            ->join('honors', 'alokasi_honors.honor_id', '=', 'honors.id')
            ->whereNotNull('alokasi_honors.tanggal_akhir_perjanjian')
            ->whereNotNull('honors.tanggal_akhir_kegiatan')
            ->where(function ($q) {
                $q->whereRaw('YEAR(alokasi_honors.tanggal_akhir_perjanjian) != YEAR(honors.tanggal_akhir_kegiatan)')
                  ->orWhereRaw('MONTH(alokasi_honors.tanggal_akhir_perjanjian) != MONTH(honors.tanggal_akhir_kegiatan)');
            })
            ->distinct()
            ->pluck('honors.id');

        $honorsToFix = Honor::whereIn('id', $honorIdsToFix)
            ->with(['kegiatanManmit', 'alokasiHonors'])
            ->get();

        $this->info("Ditemukan {$honorsToFix->count()} honor dengan data tidak konsisten.");
        $this->newLine();

        if ($honorsToFix->isEmpty()) {
            $this->info('✅ Semua data sudah konsisten!');
            return self::SUCCESS;
        }

        $totalAlokasi = 0;

        // Tabel preview
        $rows = [];
        foreach ($honorsToFix as $honor) {
            $tanggalAkhirKegiatan = Carbon::parse($honor->tanggal_akhir_kegiatan);
            $seharusnyaMulai      = $tanggalAkhirKegiatan->copy()->startOfMonth()->toDateString();
            $seharusnyaAkhir      = $tanggalAkhirKegiatan->copy()->endOfMonth()->toDateString();
            $jumlahAlokasi        = $honor->alokasiHonors->count();
            $totalAlokasi        += $jumlahAlokasi;

            $rows[] = [
                $honor->id,
                $honor->kegiatanManmit?->id ?? '-',
                $honor->tanggal_akhir_kegiatan,
                $seharusnyaMulai . ' s/d ' . $seharusnyaAkhir,
                $jumlahAlokasi,
            ];
        }

        $this->table(
            ['Honor ID', 'Kegiatan', 'tanggal_akhir_kegiatan', 'Kontrak (seharusnya)', 'Jml Alokasi'],
            $rows
        );

        $this->newLine();
        $this->line("Total alokasi yang akan diperbarui: <comment>{$totalAlokasi}</comment>");

        if ($isDryRun) {
            $this->newLine();
            $this->warn('DRY RUN selesai. Jalankan tanpa --dry-run untuk menerapkan perubahan.');
            return self::SUCCESS;
        }

        if (!$this->confirm("Lanjutkan perbaikan untuk {$totalAlokasi} alokasi?", false)) {
            $this->info('Dibatalkan.');
            return self::SUCCESS;
        }

        $this->newLine();
        $bar = $this->output->createProgressBar($honorsToFix->count());
        $bar->start();

        $totalFixed = 0;
        foreach ($honorsToFix as $honor) {
            $totalFixed += HonorTanggalService::propagate($honor);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("✅ Selesai! {$totalFixed} alokasi honor berhasil diperbaiki.");

        return self::SUCCESS;
    }
}
