<?php

namespace Tests\Feature\Satker;

use App\Models\MasterSls;
use App\Models\NomorSurat;
use App\Models\Pegawai;
use App\Models\Pengaturan;
use App\Models\PresetRutePerjadin;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiSatkerCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_nomor_surat_token_rendering_reflects_configured_satker(): void
    {
        // 1. Default Satker (6104 Mempawah)
        config(['satker.kode' => '6104']);
        config(['satker.kode_surat' => '61041']);

        $nomor = new NomorSurat([
            'nomor' => 42,
            'sub_nomor' => '1',
            'kode_surat' => 'VS.100',
            'bulan' => 3,
            'tahun' => 2026,
            'peruntukan' => 'SPK',
        ]);

        $renderedDefault = $nomor->nomorSuratPerjanjianKerja;
        $this->assertStringContainsString('61041', $renderedDefault);
        $this->assertStringContainsString('042', $renderedDefault);
        $this->assertStringContainsString('03', $renderedDefault);

        // Also test renderFormat with roman month token
        $custom = NomorSurat::renderFormat('{nomor}/{kode_surat}/{bulan_romawi}/{tahun}', [
            'nomor' => '042',
            'bulan' => 3,
            'tahun' => 2026,
        ]);
        $this->assertEquals('042/61041/III/2026', $custom);

        // 2. Override to another Satker (e.g. 3201 BPS Kab. Bogor)
        config(['satker.kode' => '3201']);
        config(['satker.kode_surat' => '32011']);

        $renderedBogor = $nomor->nomorSuratPerjanjianKerja;
        $this->assertStringContainsString('32011', $renderedBogor);
        $this->assertStringNotContainsString('61041', $renderedBogor);
    }

    public function test_pengaturan_and_setting_fallbacks(): void
    {
        config(['satker.provinsi' => 'JAWA TIMUR']);
        config(['satker.defaults.st_approval_mode' => 'auto_approve']);

        // When DB record is absent, Pengaturan::getValue falls back to config
        $prov = Pengaturan::getValue('NAMA_PROVINSI');
        $this->assertEquals('JAWA TIMUR', $prov);

        // Setting::get falls back to config
        $mode = Setting::get('st_approval_mode');
        $this->assertEquals('auto_approve', $mode);
    }

    public function test_master_sls_scoping_derives_from_satker_kode(): void
    {
        config(['satker.kode' => '3501']); // Pacitan, Jawa Timur (Prov 35)

        $this->assertEquals(['3501'], MasterSls::getSatkerKabkotIds());
        $this->assertEquals(['35'], MasterSls::getSatkerProvIds());
    }

    public function test_preset_rute_uses_dynamic_satker_metadata(): void
    {
        config(['satker.ibukota' => 'Cibinong']);
        config(['satker.kabupaten' => 'Bogor']);
        config(['satker.tingkat' => 'kako']);

        $this->assertEquals('Cibinong', PresetRutePerjadin::getIbukotaName());
        $this->assertEquals('Kab. Bogor', PresetRutePerjadin::getKabupatenLabel());

        $steps = PresetRutePerjadin::getStepsForKecamatan('Cibinong');
        $this->assertNotEmpty($steps);
        $this->assertStringContainsString('Cibinong', $steps[0]['uraian']);
    }

    public function test_satker_init_command_updates_database(): void
    {
        $this->artisan('satker:init', [
            '--kode' => '3201',
            '--nama' => 'BPS Kabupaten Bogor',
            '--tingkat' => 'kako',
            '--kabupaten' => 'Bogor',
            '--provinsi' => 'Jawa Barat',
            '--ibukota' => 'Cibinong',
            '--nip-kepala' => '197001011995031001',
            '--nip-kasubbag' => '197505052000031002',
            '--nip-ppk' => '198508082008011003',
            '--nip-bendahara' => '199010102012121004',
            '--auth-mode' => 'email_password',
            '--approval-mode' => 'delegasi_ppspm',
            '--create-admin' => true,
            '--force' => true,
        ])->assertSuccessful();

        $this->assertEquals('JAWA BARAT', Pengaturan::where('key', 'NAMA_PROVINSI')->value('nilai'));
        $this->assertEquals('KABUPATEN BOGOR', Pengaturan::where('key', 'NAMA_KAKO')->value('nilai'));
        $this->assertEquals('197001011995031001', Pengaturan::where('key', 'ID_PLH_DEFAULT')->value('nilai'));
        $this->assertEquals('198508082008011003', Pengaturan::where('key', 'NIP_PPK_SATER')->value('nilai'));
        $this->assertEquals('delegasi_ppspm', Setting::where('key', 'st_approval_mode')->value('value'));

        // Admin user created
        $this->assertTrue(User::where('email', 'admin@bps.go.id')->exists());
    }

    public function test_satker_import_sls_and_pegawai_sample(): void
    {
        config(['satker.pejabat.kepala_nip' => '196908031992111001']);

        // 1. Import SLS sample
        $this->artisan('satker:import-sls', [
            '--file' => 'database/seeders/data/master_sls_sample.csv',
        ])->assertSuccessful();

        $this->assertGreaterThan(0, MasterSls::count());

        // 2. Import Pegawai sample with auto-created user accounts
        $this->artisan('satker:import-pegawai', [
            '--file' => 'database/seeders/data/pegawai_sample.csv',
            '--create-users' => true,
        ])->assertSuccessful();

        $this->assertGreaterThan(0, Pegawai::count());

        // Kepala satker user account created and assigned role
        $kepalaUser = User::where('email', 'munawir@bps.go.id')->first();
        $this->assertNotNull($kepalaUser);
        $this->assertTrue($kepalaUser->hasRole('kepala_satker'));
        $this->assertTrue($kepalaUser->hasRole('pegawai'));
    }
}
