<?php

namespace Tests\Unit;

use App\Filament\Resources\RiwayatPengajuanResource\Widgets\RiwayatPengajuanTable;
use App\Models\MasterSls;
use App\Models\Pegawai;
use App\Models\Penugasan;
use App\Models\RiwayatPengajuan;
use App\Models\User;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RiwayatPengajuanTableSearchTest extends TestCase
{
    use RefreshDatabase;
    public function test_riwayat_pengajuan_table_location_column_is_searchable(): void
    {
        $widget = new RiwayatPengajuanTable();
        $table = $widget->table(Table::make($widget));

        $locationColumn = null;
        foreach ($table->getColumns() as $column) {
            if ($column->getName() === 'penugasan.tujuan_penugasan') {
                $locationColumn = $column;
                break;
            }
        }

        $this->assertNotNull($locationColumn, 'Column penugasan.tujuan_penugasan must exist.');
        $this->assertTrue($locationColumn->isSearchable(), 'Column penugasan.tujuan_penugasan must be searchable.');
    }

    public function test_riwayat_pengajuan_table_location_search_generates_optimized_sql_without_nested_or_where_has(): void
    {
        $widget = new RiwayatPengajuanTable();
        $table = $widget->table(Table::make($widget));

        $locationColumn = null;
        foreach ($table->getColumns() as $column) {
            if ($column->getName() === 'penugasan.tujuan_penugasan') {
                $locationColumn = $column;
                break;
            }
        }

        $this->assertNotNull($locationColumn);

        // Apply custom search query constraint on RiwayatPengajuan query
        $query = RiwayatPengajuan::query();
        $isFirst = true;
        $locationColumn->applySearchConstraint($query, 'desa cant', $isFirst);

        $sql = $query->toSql();

        // The query must use penugasan.tujuanSuratTugas
        $this->assertStringContainsString('tujuan_surat_tugas', strtolower($sql));

        // Ensure no deeply nested correlated subqueries into master_sls
        $fileContent = file_get_contents(app_path('Filament/Resources/RiwayatPengajuanResource/Widgets/RiwayatPengajuanTable.php'));
        $this->assertStringNotContainsString("orWhereHas('desa'", $fileContent);
        $this->assertStringNotContainsString("orWhereHas('kecamatan'", $fileContent);
        $this->assertStringNotContainsString("orWhereHas('kabkot'", $fileContent);
        $this->assertStringNotContainsString("orWhereHas('provinsi'", $fileContent);
    }

    public function test_riwayat_pengajuan_table_scopes_to_user_penugasan_ids(): void
    {
        $fileContent = file_get_contents(app_path('Filament/Resources/RiwayatPengajuanResource/Widgets/RiwayatPengajuanTable.php'));

        $this->assertStringContainsString('whereIn(\'penugasan_id\', $userPenugasanIds)', $fileContent);
        $this->assertStringContainsString('use App\Models\MasterSls;', $fileContent);
    }
}
