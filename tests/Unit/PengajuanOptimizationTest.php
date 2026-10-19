<?php

namespace Tests\Unit;

use App\Filament\Resources\Sipancong\PengajuanResource;
use App\Filament\Resources\Sipancong\PengajuanResource\Pages\ListPengajuans;
use App\Filament\Resources\Sipancong\PenugasanResource\Widgets\StatsProsesPembayaran;
use Filament\Actions\ActionGroup;
use Tests\TestCase;

class PengajuanOptimizationTest extends TestCase
{
    public function test_pengajuan_resource_query_has_eager_loading(): void
    {
        $query = PengajuanResource::getEloquentQuery();
        $eagerLoads = $query->getEagerLoads();

        $this->assertArrayHasKey('posisiDokumen', $eagerLoads, 'Query must eager load posisiDokumen');
        $this->assertArrayHasKey('penanggungJawab', $eagerLoads, 'Query must eager load penanggungJawab');
        $this->assertArrayHasKey('pegawai', $eagerLoads, 'Query must eager load pegawai');
        $this->assertArrayHasKey('statusPengajuanPpk', $eagerLoads, 'Query must eager load statusPengajuanPpk');
        $this->assertArrayHasKey('statusPengajuanPpspm', $eagerLoads, 'Query must eager load statusPengajuanPpspm');
        $this->assertArrayHasKey('statusPengajuanBendahara', $eagerLoads, 'Query must eager load statusPengajuanBendahara');
        $this->assertArrayHasKey('statusPembayaran', $eagerLoads, 'Query must eager load statusPembayaran');
    }

    public function test_stats_proses_pembayaran_uses_aggregated_query_without_n_plus_one(): void
    {
        $fileContent = file_get_contents(app_path('Filament/Resources/Sipancong/PenugasanResource/Widgets/StatsProsesPembayaran.php'));

        $this->assertStringNotContainsString(
            'PengajuanServices::jumlahSelesaiSubfungsi',
            $fileContent,
            'StatsProsesPembayaran must not execute 12 separate un-aggregated subfungsi queries in getStats'
        );
        $this->assertStringContainsString(
            'groupBy',
            $fileContent,
            'StatsProsesPembayaran must group by subfungsi in a single aggregated query'
        );
    }

    public function test_list_pengajuans_bulk_action_group_is_role_protected(): void
    {
        $fileContent = file_get_contents(app_path('Filament/Resources/Sipancong/PengajuanResource/Pages/ListPengajuans.php'));

        $this->assertStringContainsString(
            '->visible(fn(): bool => auth()->user()?->hasAnyRole(["super_admin", "Admin", "ppk", "ppspm", "bendahara"]))',
            $fileContent,
            'Terima Massal ActionGroup must have group-level visibility check to avoid evaluating for regular employees'
        );
    }
}
