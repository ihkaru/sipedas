<?php

namespace Tests\Unit;

use App\Filament\Resources\PenugasanResource\Widgets\Concerns\HasStatusSuratTugasChartConfiguration;
use App\Filament\Resources\PenugasanResource\Widgets\StatusSuratTugasAndaChart;
use App\Filament\Resources\PenugasanResource\Widgets\StatusSuratTugasChart;
use App\Models\Kegiatan;
use App\Models\Pegawai;
use App\Models\Penugasan;
use App\Models\RiwayatPengajuan;
use App\Models\User;
use App\Supports\Constants;
use Filament\Support\RawJs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardStatusSuratTugasChartTest extends TestCase
{
    use RefreshDatabase;

    public function test_chart_configuration_trait_provides_bar_type_and_raw_js_options(): void
    {
        $dummy = new class {
            use HasStatusSuratTugasChartConfiguration;

            public function exposeOptions(): RawJs
            {
                return $this->getOptions();
            }

            public function exposeType(): string
            {
                return $this->getType();
            }

            public function exposeFormat(array $counts): array
            {
                return $this->formatChartData($counts);
            }
        };

        $this->assertSame('bar', $dummy->exposeType());

        $options = $dummy->exposeOptions();
        $this->assertInstanceOf(RawJs::class, $options);

        $jsString = (string) $options;

        // Verify datalabels alignment and suppression of 0 values
        $this->assertStringContainsString("anchor: 'end'", $jsString);
        $this->assertStringContainsString("align: 'top'", $jsString);
        $this->assertStringContainsString('offset: 4', $jsString);
        $this->assertStringContainsString('val > 0', $jsString);
        $this->assertStringContainsString("formatter: (value) => (value > 0 ? value : '')", $jsString);

        // Verify dynamic theme adaptation
        $this->assertStringContainsString("classList.contains('dark')", $jsString);
        $this->assertStringContainsString('#cbd5e1', $jsString);
        $this->assertStringContainsString('#334155', $jsString);

        // Verify scale headroom (grace) so labels above bars are not clipped
        $this->assertStringContainsString("grace: '12%'", $jsString);
        $this->assertStringContainsString('beginAtZero: true', $jsString);

        // Verify modern aesthetics
        $this->assertStringContainsString('borderRadius: 6', $jsString);
        $this->assertStringContainsString('display: false', $jsString);
    }

    public function test_format_chart_data_maps_all_eight_statuses_correctly(): void
    {
        $dummy = new class {
            use HasStatusSuratTugasChartConfiguration;

            public function exposeFormat(array $counts): array
            {
                return $this->formatChartData($counts);
            }
        };

        $counts = [
            Constants::STATUS_PENGAJUAN_DIKIRIM => 5,
            Constants::STATUS_PENGAJUAN_DISETUJUI => 18,
            Constants::STATUS_PENGAJUAN_DICAIRKAN => 2,
        ];

        $chartData = $dummy->exposeFormat($counts);

        $expectedLabels = [
            'Dikirim',
            'Dibatalkan',
            'Perlu Revisi',
            'Ditolak',
            'Disetujui',
            'Dicetak',
            'Dikumpulkan',
            'Dicairkan',
        ];

        $this->assertSame($expectedLabels, $chartData['labels']);
        $this->assertCount(1, $chartData['datasets']);

        $dataset = $chartData['datasets'][0];
        $this->assertSame('Status Surat Tugas', $dataset['label']);
        $this->assertSame([5, 0, 0, 0, 18, 0, 0, 2], $dataset['data']);
        $this->assertSame(6, $dataset['borderRadius']);
        $this->assertSame(48, $dataset['maxBarThickness']);
    }

    public function test_status_surat_tugas_chart_can_view_authorization(): void
    {
        // Unauthenticated
        $this->assertFalse(StatusSuratTugasChart::canView());

        Role::findOrCreate('operator_umum');
        Role::findOrCreate('pegawai');

        $userOperator = User::factory()->create();
        $userOperator->assignRole('operator_umum');

        $this->actingAs($userOperator);
        $this->assertTrue(StatusSuratTugasChart::canView());

        $userRegular = User::factory()->create();
        $userRegular->assignRole('pegawai');

        $this->actingAs($userRegular);
        $this->assertFalse(StatusSuratTugasChart::canView());
    }

    public function test_status_surat_tugas_anda_chart_runs_safely_when_unauthenticated(): void
    {
        $widget = new StatusSuratTugasAndaChart();

        $reflection = new \ReflectionClass($widget);
        $method = $reflection->getMethod('getData');
        $method->setAccessible(true);

        $data = $method->invoke($widget);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('labels', $data);
        $this->assertArrayHasKey('datasets', $data);
        $this->assertSame([0, 0, 0, 0, 0, 0, 0, 0], $data['datasets'][0]['data']);
    }

    public function test_status_surat_tugas_chart_aggregates_database_records_accurately(): void
    {
        $kegiatan = Kegiatan::factory()->create(['id' => 999111, 'nama' => 'Survei SHKK 2026']);
        $pegawai = Pegawai::factory()->create();

        $createPenugasanWithStatus = function (string $status) use ($kegiatan, $pegawai) {
            $penugasan = Penugasan::create([
                'nip' => $pegawai->nip,
                'kegiatan_id' => $kegiatan->id,
                'nip_pengaju' => $pegawai->nip,
                'level_tujuan_penugasan' => Constants::LEVEL_PENUGASAN_TANPA_LOKASI,
                'nama_tempat_tujuan' => 'Kantor',
                'tgl_mulai_tugas' => now()->toDateString(),
                'tgl_akhir_tugas' => now()->toDateString(),
                'tgl_pengajuan_tugas' => now()->toDateString(),
                'jenis_peserta' => Constants::JENIS_PESERTA_SURAT_TUGAS_PEGAWAI,
                'grup_id' => random_int(100000, 999999),
                'jenis_surat_tugas' => Constants::NON_SPPD,
                'plh_id' => $pegawai->nip,
            ]);

            RiwayatPengajuan::create([
                'penugasan_id' => $penugasan->id,
                'status' => $status,
            ]);

            return $penugasan;
        };

        // 2 Dikirim, 3 Disetujui, 1 Dicairkan
        $createPenugasanWithStatus(Constants::STATUS_PENGAJUAN_DIKIRIM);
        $createPenugasanWithStatus(Constants::STATUS_PENGAJUAN_DIKIRIM);
        $createPenugasanWithStatus(Constants::STATUS_PENGAJUAN_DISETUJUI);
        $createPenugasanWithStatus(Constants::STATUS_PENGAJUAN_DISETUJUI);
        $createPenugasanWithStatus(Constants::STATUS_PENGAJUAN_DISETUJUI);
        $createPenugasanWithStatus(Constants::STATUS_PENGAJUAN_DICAIRKAN);

        $widget = new StatusSuratTugasChart();

        $reflection = new \ReflectionClass($widget);
        $method = $reflection->getMethod('getData');
        $method->setAccessible(true);

        $data = $method->invoke($widget);

        // Expected order: Dikirim (2), Dibatalkan (0), Perlu Revisi (0), Ditolak (0), Disetujui (3), Dicetak (0), Dikumpulkan (0), Dicairkan (1)
        $this->assertSame([2, 0, 0, 0, 3, 0, 0, 1], $data['datasets'][0]['data']);
    }

    public function test_status_surat_tugas_anda_chart_filters_only_authenticated_pegawai_records(): void
    {
        $kegiatan = Kegiatan::factory()->create(['id' => 999222, 'nama' => 'Survei Sakernas 2026']);

        $pegawaiA = Pegawai::factory()->create();
        $userA = User::factory()->create();
        $userA->pegawai()->save($pegawaiA);

        $pegawaiB = Pegawai::factory()->create();
        $userB = User::factory()->create();
        $userB->pegawai()->save($pegawaiB);

        $createPenugasan = function (Pegawai $pegawai, string $status) use ($kegiatan) {
            $penugasan = Penugasan::create([
                'nip' => $pegawai->nip,
                'kegiatan_id' => $kegiatan->id,
                'nip_pengaju' => $pegawai->nip,
                'level_tujuan_penugasan' => Constants::LEVEL_PENUGASAN_TANPA_LOKASI,
                'nama_tempat_tujuan' => 'Kantor',
                'tgl_mulai_tugas' => now()->toDateString(),
                'tgl_akhir_tugas' => now()->toDateString(),
                'tgl_pengajuan_tugas' => now()->toDateString(),
                'jenis_peserta' => Constants::JENIS_PESERTA_SURAT_TUGAS_PEGAWAI,
                'grup_id' => random_int(100000, 999999),
                'jenis_surat_tugas' => Constants::NON_SPPD,
                'plh_id' => $pegawai->nip,
            ]);

            RiwayatPengajuan::create([
                'penugasan_id' => $penugasan->id,
                'status' => $status,
            ]);
        };

        // User A records: 1 Dikirim, 2 Disetujui
        $createPenugasan($pegawaiA, Constants::STATUS_PENGAJUAN_DIKIRIM);
        $createPenugasan($pegawaiA, Constants::STATUS_PENGAJUAN_DISETUJUI);
        $createPenugasan($pegawaiA, Constants::STATUS_PENGAJUAN_DISETUJUI);

        // User B record: 5 Disetujui (should not leak into User A chart)
        $createPenugasan($pegawaiB, Constants::STATUS_PENGAJUAN_DISETUJUI);
        $createPenugasan($pegawaiB, Constants::STATUS_PENGAJUAN_DISETUJUI);
        $createPenugasan($pegawaiB, Constants::STATUS_PENGAJUAN_DISETUJUI);
        $createPenugasan($pegawaiB, Constants::STATUS_PENGAJUAN_DISETUJUI);
        $createPenugasan($pegawaiB, Constants::STATUS_PENGAJUAN_DISETUJUI);

        $this->actingAs($userA);

        $widget = new StatusSuratTugasAndaChart();
        $reflection = new \ReflectionClass($widget);
        $method = $reflection->getMethod('getData');
        $method->setAccessible(true);

        $data = $method->invoke($widget);

        // Expected order: Dikirim (1), Dibatalkan (0), Perlu Revisi (0), Ditolak (0), Disetujui (2), Dicetak (0), Dikumpulkan (0), Dicairkan (0)
        $this->assertSame([1, 0, 0, 0, 2, 0, 0, 0], $data['datasets'][0]['data']);
    }
}
