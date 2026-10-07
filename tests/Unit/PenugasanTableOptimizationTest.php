<?php

namespace Tests\Unit;

use App\Filament\Resources\PenugasanResource;
use App\Filament\Resources\PenugasanResource\Widgets\PenugasanTable;
use Filament\Tables\Table;
use Tests\TestCase;

class PenugasanTableOptimizationTest extends TestCase
{
    public function test_penugasan_table_query_has_eager_loading(): void
    {
        $widget = new PenugasanTable();
        $table = $widget->table(Table::make($widget));
        $query = $table->getQuery();

        $eagerLoads = $query->getEagerLoads();

        $this->assertArrayHasKey('riwayatPengajuan', $eagerLoads, 'Query must eager load riwayatPengajuan');
        $this->assertArrayHasKey('pegawai', $eagerLoads, 'Query must eager load pegawai');
        $this->assertArrayHasKey('mitra', $eagerLoads, 'Query must eager load mitra');
        $this->assertArrayHasKey('pengaju', $eagerLoads, 'Query must eager load pengaju');
        $this->assertArrayHasKey('kegiatan', $eagerLoads, 'Query must eager load kegiatan');
    }

    public function test_penugasan_table_columns_are_searchable(): void
    {
        $widget = new PenugasanTable();
        $table = $widget->table(Table::make($widget));

        $columns = [];
        foreach ($table->getColumns() as $col) {
            $columns[$col->getName()] = $col;
        }

        $this->assertArrayHasKey('tertugas', $columns, 'Column tertugas must exist');
        $this->assertTrue($columns['tertugas']->isSearchable(), 'Column tertugas must be searchable');

        $this->assertArrayHasKey('pengaju.nama', $columns, 'Column pengaju.nama must exist');
        $this->assertTrue($columns['pengaju.nama']->isSearchable(), 'Column pengaju.nama must be searchable');

        $this->assertArrayHasKey('kegiatan.nama', $columns, 'Column kegiatan.nama must exist');
        $this->assertTrue($columns['kegiatan.nama']->isSearchable(), 'Column kegiatan.nama must be searchable');
    }

    public function test_penugasan_table_pagination_is_bounded(): void
    {
        $widget = new PenugasanTable();
        $table = $widget->table(Table::make($widget));

        $this->assertTrue($table->isPaginated(), 'PenugasanTable must be paginated');
        $this->assertSame(10, $table->getDefaultPaginationPageOption(), 'Default pagination must be 10');
        $this->assertEquals([5, 10, 25], $table->getPaginationPageOptions(), 'Pagination options must be bounded');
    }

    public function test_mitra_filter_does_not_preload_mass_records(): void
    {
        $fileContent = file_get_contents(app_path('Filament/Resources/PenugasanResource.php'));

        // Match SelectFilter::make('mitra') definition and verify it does NOT call ->preload()
        preg_match('/SelectFilter::make\([\'"]mitra[\'"]\)(.*?)(?:,\s*[A-Z]|\])/s', $fileContent, $matches);
        $this->assertNotEmpty($matches, 'SelectFilter for mitra must exist');
        $this->assertStringNotContainsString('->preload()', $matches[1], 'Mitra filter must not preload 700+ records');
    }
}
