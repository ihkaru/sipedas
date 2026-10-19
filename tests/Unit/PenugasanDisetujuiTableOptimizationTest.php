<?php

namespace Tests\Unit;

use App\Filament\Resources\PenugasanResource\Widgets\PenugasanDisetujuiTable;
use Filament\Tables\Table;
use Tests\TestCase;

class PenugasanDisetujuiTableOptimizationTest extends TestCase
{
    public function test_penugasan_disetujui_table_query_has_comprehensive_eager_loading(): void
    {
        $widget = new PenugasanDisetujuiTable();
        $table = $widget->table(Table::make($widget));
        $query = $table->getQuery();

        $eagerLoads = $query->getEagerLoads();

        $this->assertArrayHasKey('riwayatPengajuan', $eagerLoads, 'Query must eager load riwayatPengajuan');
        $this->assertArrayHasKey('pegawai', $eagerLoads, 'Query must eager load pegawai');
        $this->assertArrayHasKey('mitra', $eagerLoads, 'Query must eager load mitra');
        $this->assertArrayHasKey('kegiatan', $eagerLoads, 'Query must eager load kegiatan');
        $this->assertArrayHasKey('suratTugas', $eagerLoads, 'Query must eager load suratTugas');
        $this->assertArrayHasKey('tujuanSuratTugas.provinsi', $eagerLoads, 'Query must eager load tujuanSuratTugas.provinsi to prevent N+1');
        $this->assertArrayHasKey('tujuanSuratTugas.kabkot', $eagerLoads, 'Query must eager load tujuanSuratTugas.kabkot to prevent N+1');
        $this->assertArrayHasKey('tujuanSuratTugas.kecamatan', $eagerLoads, 'Query must eager load tujuanSuratTugas.kecamatan to prevent N+1');
        $this->assertArrayHasKey('tujuanSuratTugas.desa', $eagerLoads, 'Query must eager load tujuanSuratTugas.desa to prevent N+1');
        $this->assertArrayHasKey('satuSurat.riwayatPengajuan', $eagerLoads, 'Query must eager load satuSurat.riwayatPengajuan to evaluate joint letters in memory');
    }

    public function test_penugasan_disetujui_table_pagination_is_bounded(): void
    {
        $widget = new PenugasanDisetujuiTable();
        $table = $widget->table(Table::make($widget));

        $this->assertTrue($table->isPaginated(), 'PenugasanDisetujuiTable must be paginated');
        $this->assertSame(10, $table->getDefaultPaginationPageOption(), 'Default pagination must be 10');
        $this->assertEquals([5, 10, 25], $table->getPaginationPageOptions(), 'Pagination options must be bounded');
    }

    public function test_penugasan_disetujui_table_columns_and_actions(): void
    {
        $widget = new PenugasanDisetujuiTable();
        $table = $widget->table(Table::make($widget));

        $columns = [];
        foreach ($table->getColumns() as $col) {
            $columns[$col->getName()] = $col;
        }

        $this->assertArrayHasKey('tertugas', $columns, 'Column tertugas must exist');
        $this->assertTrue($columns['tertugas']->isSearchable(), 'Column tertugas must be searchable');

        $this->assertArrayHasKey('tujuan_penugasan', $columns, 'Column tujuan_penugasan must exist');
        $this->assertArrayHasKey('kegiatan.nama', $columns, 'Column kegiatan.nama must exist');
        $this->assertTrue($columns['kegiatan.nama']->isSearchable(), 'Column kegiatan.nama must be searchable');

        $actions = [];
        foreach ($table->getActions() as $action) {
            $actions[$action->getName()] = $action;
        }

        $this->assertArrayHasKey('pdf', $actions, 'Action pdf must exist');
        $this->assertArrayHasKey('lihat', $actions, 'Action lihat must exist');
    }

    public function test_pdf_action_does_not_call_cetak_mutation_in_render_callback(): void
    {
        $fileContent = file_get_contents(app_path('Filament/Resources/PenugasanResource/Widgets/PenugasanDisetujuiTable.php'));

        $this->assertStringNotContainsString(
            '$record->cetak()',
            $fileContent,
            'PDF action URL generator must never call $record->cetak() because it runs on every row render and mutates database'
        );
    }
}
