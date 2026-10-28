<?php

namespace App\Console\Commands;

use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SatkerImportPegawaiCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'satker:import-pegawai
        {--file= : Path ke berkas CSV atau JSON Pegawai}
        {--create-users : Buat akun User login otomatis untuk setiap pegawai}
        {--default-password=password123 : Password awal untuk akun user baru}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data master Pegawai BPS dan sinkronisasi akun pengguna';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->components->info('Memulai import data master Pegawai...');

        $filePath = $this->resolveFilePath();

        if (! $filePath || ! File::exists($filePath)) {
            $this->components->error('Berkas data pegawai tidak ditemukan!');
            $this->components->warn('Letakkan berkas di [database/seeders/data/pegawai.csv] atau [database/seeders/data/pegawai.json]');
            $this->components->warn('Gunakan template contoh di [database/seeders/data/pegawai_sample.csv]');

            return Command::FAILURE;
        }

        $this->components->info("Membaca berkas: {$filePath}");

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $records = [];

        if ($extension === 'json') {
            $records = json_decode(File::get($filePath), true) ?: [];
        } elseif ($extension === 'csv') {
            $records = $this->parseCsv($filePath);
        } else {
            $this->components->error("Format berkas .{$extension} tidak didukung. Gunakan CSV atau JSON.");

            return Command::FAILURE;
        }

        if (empty($records)) {
            $this->components->warn('Tidak ada data pegawai yang dapat dibaca dari berkas.');

            return Command::SUCCESS;
        }

        $shouldCreateUsers = $this->option('create-users');
        $defaultPassword = $this->option('default-password') ?: 'password123';

        // Pastikan roles esensial terdaftar
        $this->ensureRolesExist();

        $importedCount = 0;
        $usersCreatedCount = 0;

        $satkerNipKepala = config('satker.pejabat.kepala_nip');
        $satkerNipKasubbag = config('satker.pejabat.kasubbag_tu_nip');
        $satkerNipPpk = config('satker.pejabat.ppk_nip');
        $satkerNipBendahara = config('satker.pejabat.bendahara_nip');
        $satkerNama = config('satker.nama', 'BPS Kabupaten Mempawah');

        foreach ($records as $row) {
            $parsed = $this->normalizeRow($row, $satkerNama);
            if (empty($parsed['nip'])) {
                continue;
            }

            // Pertahankan nomor_wa dan panggilan lama jika data impor tidak menyediakannya
            $existing = Pegawai::where('nip', $parsed['nip'])->first();
            if ($existing) {
                if (empty($row['panggilan']) && ! empty($existing->panggilan)) {
                    $parsed['panggilan'] = $existing->panggilan;
                }
                if (empty($row['nomor_wa']) && ! empty($existing->nomor_wa)) {
                    $parsed['nomor_wa'] = $existing->nomor_wa;
                }
            }

            Pegawai::updateOrCreate(
                ['nip' => $parsed['nip']],
                $parsed
            );
            $importedCount++;

            if ($shouldCreateUsers && ! empty($parsed['email'])) {
                $user = User::firstOrCreate(
                    ['email' => $parsed['email']],
                    [
                        'name' => $parsed['nama'],
                        'password' => Hash::make($defaultPassword),
                    ]
                );

                if ($user->wasRecentlyCreated) {
                    $usersCreatedCount++;
                }

                // Berikan role dasar 'pegawai'
                if (! $user->hasRole('pegawai')) {
                    $user->assignRole('pegawai');
                }

                // Tetapkan role struktural berdasarkan NIP 4 Pejabat
                if ($parsed['nip'] === $satkerNipKepala && ! $user->hasRole('kepala_satker')) {
                    $user->assignRole('kepala_satker');
                }
                if ($parsed['nip'] === $satkerNipKasubbag && ! $user->hasRole('ppspm')) {
                    $user->assignRole('ppspm');
                }
                if ($parsed['nip'] === $satkerNipPpk && ! $user->hasRole('ppk')) {
                    $user->assignRole('ppk');
                }
                if ($parsed['nip'] === $satkerNipBendahara && ! $user->hasRole('bendahara')) {
                    $user->assignRole('bendahara');
                }
            }
        }

        $this->components->success("Import Pegawai Selesai! Berhasil memproses {$importedCount} data pegawai.");
        if ($shouldCreateUsers) {
            $this->components->info("Akun pengguna dibuat/diperbarui: {$usersCreatedCount} akun baru (Password default: {$defaultPassword}).");
        }

        return Command::SUCCESS;
    }

    /**
     * Normalize a row from JSON or CSV into unified Pegawai attributes.
     */
    protected function normalizeRow(array $row, string $satkerNama): array
    {
        // Mendukung format JSON (nip_baru, nip_lama, golongan_akhir) dan CSV (nip, nip9, pangkat, golongan)
        $nip = $row['nip'] ?? $row['nip_baru'] ?? null;
        $nip9 = $row['nip9'] ?? $row['nip_lama'] ?? null;
        $nama = $row['nama'] ?? 'Pegawai';
        $jabatan = $row['jabatan'] ?? 'Pelaksana';
        $email = $row['email'] ?? null;
        $nomorWa = $row['nomor_wa'] ?? $row['no_wa'] ?? $row['telepon'] ?? null;
        $panggilan = $row['panggilan'] ?? null;
        $unitKerja = $row['unit_kerja'] ?? $satkerNama;

        $pangkat = $row['pangkat'] ?? null;
        $golongan = $row['golongan'] ?? null;

        if (empty($pangkat) && ! empty($row['golongan_akhir'])) {
            $parts = explode('/', $row['golongan_akhir']);
            $pangkat = trim($parts[0] ?? '');
            $golongan = trim($parts[1] ?? '');
        }

        $pangkat = $pangkat ?: 'III';
        $golongan = $golongan ?: 'a';

        if (empty($nip9) && $nip) {
            $nip9 = '340'.substr($nip, -6);
        }

        if (empty($panggilan)) {
            $cleanNama = preg_replace('/\b(dr\.|drs\.|drh\.|ir\.)\b/i', '', $nama);
            $words = explode(' ', trim((string) $cleanNama));
            $firstWord = trim(str_replace([',', '.'], '', $words[0] ?? ''));
            $panggilan = $firstWord ?: 'Pegawai';
        }

        // Normalisasi nomor WA ke format 628...
        if ($nomorWa) {
            $cleanWa = preg_replace('/[^0-9]/', '', (string) $nomorWa);
            if (str_starts_with($cleanWa, '08')) {
                $nomorWa = '62'.substr($cleanWa, 1);
            } elseif (str_starts_with($cleanWa, '8')) {
                $nomorWa = '62'.$cleanWa;
            } else {
                $nomorWa = $cleanWa;
            }
        }

        return [
            'nip' => (string) $nip,
            'nip9' => $nip9 ? (string) $nip9 : null,
            'nama' => $nama,
            'jabatan' => $jabatan,
            'pangkat' => $pangkat,
            'golongan' => $golongan,
            'email' => $email,
            'nomor_wa' => $nomorWa,
            'panggilan' => $panggilan,
            'unit_kerja' => $unitKerja,
        ];
    }

    /**
     * Parse CSV file into associative array.
     */
    protected function parseCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            return [];
        }

        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);

            return [];
        }

        $header = array_map(fn ($col) => strtolower(trim($col)), $header);
        $records = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) === count($header)) {
                $records[] = array_combine($header, $row);
            }
        }

        fclose($handle);

        return $records;
    }

    /**
     * Ensure critical roles exist before assignment.
     */
    protected function ensureRolesExist(): void
    {
        $roles = ['super_admin', 'kepala_satker', 'operator_umum', 'pegawai', 'ppspm', 'ppk', 'bendahara'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }
    }

    /**
     * Resolve target file path.
     */
    protected function resolveFilePath(): ?string
    {
        if ($custom = $this->option('file')) {
            return base_path($custom);
        }

        $candidates = [
            database_path('seeders/data/pegawai.csv'),
            database_path('seeders/data/pegawai.json'),
            database_path('data/pegawai.csv'),
            database_path('data/pegawai.json'),
            database_path('seeders/data/pegawai_sample.csv'),
        ];

        foreach ($candidates as $candidate) {
            if (File::exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
