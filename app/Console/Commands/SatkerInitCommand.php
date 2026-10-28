<?php

namespace App\Console\Commands;

use App\Models\Pengaturan;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SatkerInitCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'satker:init
        {--kode= : Kode 4 digit BPS Satker (misal: 6104)}
        {--nama= : Nama lengkap Satuan Kerja}
        {--tingkat= : Tingkat satuan kerja (kako/provinsi/kota)}
        {--kabupaten= : Nama Kabupaten/Kota}
        {--provinsi= : Nama Provinsi}
        {--ibukota= : Nama Ibu Kota kedudukan}
        {--nip-kepala= : NIP Kepala Satker}
        {--nip-kasubbag= : NIP Kasubbag TU / PPSPM}
        {--nip-ppk= : NIP PPK}
        {--nip-bendahara= : NIP Bendahara}
        {--auth-mode= : Mode autentikasi (email_password, hybrid, sso_saml)}
        {--approval-mode= : Mode approval surat tugas (delegasi_ppspm, kepala_only, ppspm_only, auto_approve)}
        {--create-admin : Buat user admin default jika belum ada}
        {--force : Lewati pertanyaan konfirmasi}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inisialisasi dan konfigurasi identitas Satuan Kerja BPS baru untuk Dokter-V';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->components->info('=====================================================');
        $this->components->info('  DOKTER-V — Multi-Satker BPS Setup Wizard');
        $this->components->info('=====================================================');

        $isInteractive = $this->input->isInteractive() && ! $this->option('force') && empty($this->option('kode'));

        // 1. Identitas Satker
        $defaultKode = config('satker.kode', '6104');
        $kode = $this->option('kode') ?: ($isInteractive ? $this->ask('1. Kode 4 Digit BPS Satker', $defaultKode) : $defaultKode);

        $defaultNama = config('satker.nama', 'BPS Kabupaten Mempawah');
        $nama = $this->option('nama') ?: ($isInteractive ? $this->ask('2. Nama Instansi Satker', $defaultNama) : $defaultNama);

        $defaultTingkat = config('satker.tingkat', 'kako');
        $tingkat = $this->option('tingkat') ?: ($isInteractive ? $this->choice('3. Tingkat Satuan Kerja', ['kako', 'provinsi', 'kota'], $defaultTingkat) : $defaultTingkat);

        $defaultKabupaten = config('satker.kabupaten', 'Mempawah');
        $kabupaten = $this->option('kabupaten') ?: ($isInteractive ? $this->ask('4. Nama Wilayah Kab/Kota', $defaultKabupaten) : $defaultKabupaten);

        $defaultProvinsi = config('satker.provinsi', 'Kalimantan Barat');
        $provinsi = $this->option('provinsi') ?: ($isInteractive ? $this->ask('5. Nama Provinsi Satker', $defaultProvinsi) : $defaultProvinsi);

        $defaultIbukota = config('satker.ibukota', 'Mempawah');
        $ibukota = $this->option('ibukota') ?: ($isInteractive ? $this->ask('6. Ibu Kota Kedudukan Satker', $defaultIbukota) : $defaultIbukota);

        // 2. Empat Pejabat Definitif / Otoritas
        $defaultNipKepala = config('satker.pejabat.kepala_nip', '196908031992111001');
        $nipKepala = $this->option('nip-kepala') ?: ($isInteractive ? $this->ask('7. NIP Kepala Satker / PLH Default', $defaultNipKepala) : $defaultNipKepala);

        $defaultNipKasubbag = config('satker.pejabat.kasubbag_tu_nip', '198008112005021004');
        $nipKasubbag = $this->option('nip-kasubbag') ?: ($isInteractive ? $this->ask('8. NIP Kasubbag TU / PPSPM', $defaultNipKasubbag) : $defaultNipKasubbag);

        $defaultNipPpk = config('satker.pejabat.ppk_nip', '199306062016021001');
        $nipPpk = $this->option('nip-ppk') ?: ($isInteractive ? $this->ask('9. NIP Pejabat Pembuat Komitmen (PPK)', $defaultNipPpk) : $defaultNipPpk);

        $defaultNipBendahara = config('satker.pejabat.bendahara_nip', '198801012010121001');
        $nipBendahara = $this->option('nip-bendahara') ?: ($isInteractive ? $this->ask('10. NIP Bendahara Pengeluaran', $defaultNipBendahara) : $defaultNipBendahara);

        // 3. Kebijakan Autentikasi & Approval
        $defaultAuthMode = config('satker.auth.mode', 'email_password');
        $authMode = $this->option('auth-mode') ?: ($isInteractive ? $this->choice('11. Mode Autentikasi Login', ['email_password', 'hybrid', 'sso_saml'], $defaultAuthMode) : $defaultAuthMode);

        $defaultApprovalMode = config('satker.defaults.st_approval_mode', 'delegasi_ppspm');
        $approvalMode = $this->option('approval-mode') ?: ($isInteractive ? $this->choice('12. Mode Approval Surat Tugas', ['delegasi_ppspm', 'kepala_only', 'ppspm_only', 'auto_approve'], $defaultApprovalMode) : $defaultApprovalMode);

        // 4. Update .env File
        $envData = [
            'SATKER_KODE' => $kode,
            'SATKER_NAMA' => "\"{$nama}\"",
            'SATKER_TINGKAT' => $tingkat,
            'SATKER_KABUPATEN' => "\"{$kabupaten}\"",
            'SATKER_PROVINSI' => "\"{$provinsi}\"",
            'SATKER_IBUKOTA' => "\"{$ibukota}\"",
            'SATKER_NIP_KEPALA' => $nipKepala,
            'SATKER_NIP_KASUBBAG_TU' => $nipKasubbag,
            'SATKER_NIP_PPK' => $nipPpk,
            'SATKER_NIP_BENDAHARA' => $nipBendahara,
            'AUTH_MODE' => $authMode,
            'ST_APPROVAL_MODE' => $approvalMode,
        ];

        if (! app()->runningUnitTests()) {
            $this->updateEnvironmentFile($envData);
            $this->components->info('Berkas .env berhasil disinkronisasi.');
        }

        // 5. Update Database Pengaturan & Settings
        $kakoLabel = ($tingkat === 'kota' ? 'KOTA ' : 'KABUPATEN ').strtoupper($kabupaten);

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
            ['key' => 'ID_PLH_DEFAULT'],
            ['nilai' => $nipKepala, 'deskripsi' => 'NIP Kepala Satker / PLH Default']
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

        Setting::updateOrCreate(
            ['key' => 'st_approval_mode'],
            ['value' => $approvalMode]
        );

        $this->components->info('Database pengaturans & settings berhasil disinkronisasi.');

        // 6. Setup Admin Default jika belum ada
        if ($authMode === 'email_password' || $this->option('create-admin') || User::count() === 0) {
            $this->ensureAdminUserExists();
        }

        // 7. Ringkasan Konfigurasi
        $this->newLine();
        $this->components->info('Ringkasan Konfigurasi Satker:');
        $this->table(
            ['Parameter', 'Nilai'],
            [
                ['Kode Satker BPS', $kode],
                ['Nama Instansi', $nama],
                ['Tingkat & Wilayah', strtoupper($tingkat)." - {$kabupaten}, {$provinsi}"],
                ['Ibu Kota Kedudukan', $ibukota],
                ['NIP Kepala / PLH Default', $nipKepala],
                ['NIP Kasubbag TU (PPSPM)', $nipKasubbag],
                ['NIP PPK', $nipPpk],
                ['NIP Bendahara', $nipBendahara],
                ['Mode Autentikasi', $authMode],
                ['Mode ST Approval', $approvalMode],
            ]
        );

        $this->newLine();
        $this->components->success("Inisialisasi Satker [{$kode}] {$nama} Berhasil!");
        $this->components->warn('Langkah Selanjutnya: Jalankan [php artisan satker:import-all --create-users] untuk mengisi data master SLS, Pegawai, dan Mitra.');

        return Command::SUCCESS;
    }

    /**
     * Update or append environment variables in the .env file safely.
     */
    protected function updateEnvironmentFile(array $data): void
    {
        $envPath = base_path('.env');
        if (! File::exists($envPath)) {
            $examplePath = base_path('.env.example');
            if (File::exists($examplePath)) {
                File::copy($examplePath, $envPath);
            } else {
                File::put($envPath, '');
            }
        }

        $content = File::get($envPath);

        foreach ($data as $key => $value) {
            $pattern = "/^{$key}=.*$/m";
            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, "{$key}={$value}", $content);
            } else {
                $content .= "\n{$key}={$value}";
            }
        }

        File::put($envPath, $content);
    }

    /**
     * Ensure a default super_admin account exists for email/password authentication.
     */
    protected function ensureAdminUserExists(): void
    {
        // Pastikan role super_admin terdaftar
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        $adminEmail = 'admin@bps.go.id';
        $adminUser = User::where('email', $adminEmail)->first();

        if (! $adminUser) {
            $adminUser = User::create([
                'name' => 'Administrator Satker',
                'email' => $adminEmail,
                'password' => Hash::make('password123'),
            ]);
            $this->components->info("Akun Super Admin dibuat: [{$adminEmail}] (Password: password123)");
        }

        if (! $adminUser->hasRole('super_admin')) {
            $adminUser->assignRole($superAdminRole);
        }
    }
}
