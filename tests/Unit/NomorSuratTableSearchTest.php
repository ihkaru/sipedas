<?php

namespace Tests\Unit;

use App\Filament\Resources\NomorSuratResource;
use App\Models\NomorSurat;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use App\Filament\Resources\NomorSuratResource\Pages\ListNomorSurats;

class NomorSuratTableSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_nomor_surat_table_nomor_surat_tugas_column_is_searchable(): void
    {
        $livewire = new ListNomorSurats();
        $table = NomorSuratResource::table(Table::make($livewire));

        $nomorSuratColumn = null;
        foreach ($table->getColumns() as $column) {
            if ($column->getName() === 'nomor_surat_tugas') {
                $nomorSuratColumn = $column;
                break;
            }
        }

        $this->assertNotNull($nomorSuratColumn, 'Column nomor_surat_tugas must exist.');
        $this->assertTrue($nomorSuratColumn->isSearchable(), 'Column nomor_surat_tugas must be searchable.');
    }

    public function test_nomor_surat_table_search_does_not_fail_with_unknown_column(): void
    {
        $livewire = new ListNomorSurats();
        $table = NomorSuratResource::table(Table::make($livewire));

        $nomorSuratColumn = null;
        foreach ($table->getColumns() as $column) {
            if ($column->getName() === 'nomor_surat_tugas') {
                $nomorSuratColumn = $column;
                break;
            }
        }

        $this->assertNotNull($nomorSuratColumn);

        $query = NomorSurat::query();
        $isFirst = true;
        $nomorSuratColumn->applySearchConstraint($query, 'usaha', $isFirst);

        $sql = $query->toSql();

        // Ensure SQL does NOT reference virtual accessor column nomor_surat_tugas
        $this->assertStringNotContainsString('`nomor_surat_tugas`', $sql);
        $this->assertStringNotContainsString('nomor_surat_tugas like', strtolower($sql));

        // Ensure SQL searches actual columns
        $this->assertStringContainsString('nomor', strtolower($sql));
        $this->assertStringContainsString('sub_nomor', strtolower($sql));
        $this->assertStringContainsString('tahun', strtolower($sql));

        // Ensure executing the query works without PDOException / Column not found
        $results = $query->get();
        $this->assertCount(0, $results);
    }
}
