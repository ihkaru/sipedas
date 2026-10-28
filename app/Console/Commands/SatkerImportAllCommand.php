<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SatkerImportAllCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'satker:import-all
        {--create-users : Buat akun User login otomatis untuk setiap pegawai}
        {--tahun=2026 : Tahun kemitraan mitra}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import seluruh paket data master satker (Master SLS, Pegawai, dan Mitra)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->components->info('=====================================================');
        $this->components->info('  DOKTER-V — Eksekusi Impor Paket Data Satker');
        $this->components->info('=====================================================');

        // 1. Import Master SLS
        $this->newLine();
        $this->components->info('>>> [1/3] Menjalankan Impor Master SLS Wilkerstat...');
        $slsExit = $this->call('satker:import-sls');

        // 2. Import Pegawai
        $this->newLine();
        $this->components->info('>>> [2/3] Menjalankan Impor Data Pegawai Organik...');
        $pegawaiArgs = [];
        if ($this->option('create-users')) {
            $pegawaiArgs['--create-users'] = true;
        }
        $pegawaiExit = $this->call('satker:import-pegawai', $pegawaiArgs);

        // 3. Import Mitra
        $this->newLine();
        $this->components->info('>>> [3/3] Menjalankan Impor Data Mitra Statistik...');
        $mitraExit = $this->call('satker:import-mitra', ['--tahun' => $this->option('tahun')]);

        $this->newLine();
        if ($slsExit === Command::SUCCESS && $pegawaiExit === Command::SUCCESS && $mitraExit === Command::SUCCESS) {
            $this->components->success('Seluruh paket data master satker berhasil diimpor!');
        } else {
            $this->components->warn('Beberapa proses impor selesai dengan peringatan/catatan. Silakan periksa log di atas.');
        }

        return Command::SUCCESS;
    }
}
