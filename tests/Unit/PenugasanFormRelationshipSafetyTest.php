<?php

namespace Tests\Unit;

require_once __DIR__ . '/../TestCase.php';

use App\Models\Penugasan;
use Tests\TestCase;

class PenugasanFormRelationshipSafetyTest extends TestCase
{
    public function test_penugasan_model_does_not_have_direct_location_relationships(): void
    {
        $penugasan = new Penugasan();

        $this->assertFalse(method_exists($penugasan, 'provinsi'), 'Penugasan must not define direct provinsi relationship');
        $this->assertFalse(method_exists($penugasan, 'kabkot'), 'Penugasan must not define direct kabkot relationship');
        $this->assertFalse(method_exists($penugasan, 'kecamatan'), 'Penugasan must not define direct kecamatan relationship');
        $this->assertFalse(method_exists($penugasan, 'desa'), 'Penugasan must not define direct desa relationship');
    }

    public function test_penugasan_resource_actions_do_not_call_nonexistent_relationships(): void
    {
        $resourceFile = file_get_contents(app_path('Filament/Resources/PenugasanResource.php'));

        // Action perlu_perbaikan & ajukan_revisi should not use ->relationship('provinsi'|'kabkot'|'kecamatan'|'desa')
        $this->assertStringNotContainsString(
            "->relationship('provinsi'",
            $resourceFile,
            'PenugasanResource must not use relationship(\'provinsi\') directly on Penugasan'
        );
        $this->assertStringNotContainsString(
            "->relationship('kabkot'",
            $resourceFile,
            'PenugasanResource must not use relationship(\'kabkot\') directly on Penugasan'
        );
        $this->assertStringNotContainsString(
            "->relationship('kecamatan'",
            $resourceFile,
            'PenugasanResource must not use relationship(\'kecamatan\') directly on Penugasan'
        );
        $this->assertStringNotContainsString(
            "->relationship('desa'",
            $resourceFile,
            'PenugasanResource must not use relationship(\'desa\') directly on Penugasan'
        );
    }

    public function test_penugasan_table_and_list_use_form_pengajuan_without_legacy_relationships(): void
    {
        $tableFile = file_get_contents(app_path('Filament/Resources/PenugasanResource/Widgets/PenugasanTable.php'));
        $listFile = file_get_contents(app_path('Filament/Resources/PenugasanResource/Pages/ListPenugasans.php'));

        $this->assertStringContainsString('form(PenugasanResource::formPengajuan())', $tableFile);
        $this->assertStringContainsString('form(PenugasanResource::formPengajuan())', $listFile);

        $this->assertStringNotContainsString("->relationship('provinsi'", $tableFile);
        $this->assertStringNotContainsString("->relationship('kabkot'", $tableFile);
        $this->assertStringNotContainsString("->relationship('kecamatan'", $tableFile);
        $this->assertStringNotContainsString("->relationship('desa'", $tableFile);

        $this->assertStringNotContainsString("->relationship('provinsi'", $listFile);
        $this->assertStringNotContainsString("->relationship('kabkot'", $listFile);
        $this->assertStringNotContainsString("->relationship('kecamatan'", $listFile);
        $this->assertStringNotContainsString("->relationship('desa'", $listFile);
    }
}
