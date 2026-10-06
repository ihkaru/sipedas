<?php

namespace Tests\Unit;

use App\Models\NomorSurat;
use App\Models\Penugasan;
use Tests\TestCase;

class PenugasanBatalkanTest extends TestCase
{
    public function test_penugasan_model_has_batalkan_and_tolak_methods(): void
    {
        $ref = new \ReflectionClass(Penugasan::class);
        $this->assertTrue($ref->hasMethod('batalkan'), 'Penugasan must have batalkan method');
        $this->assertTrue($ref->hasMethod('tolak'), 'Penugasan must have tolak method');
    }

    public function test_batalkan_and_tolak_do_not_pass_eloquent_relation_instances_to_where_in(): void
    {
        $fileContent = file_get_contents(app_path('Models/Penugasan.php'));

        // Ensure invalid pattern $suratTugasId = $this->suratTugas is gone
        $this->assertDoesNotMatchRegularExpression(
            '/\$suratTugasId\s*=\s*\$this->suratTugas;/',
            $fileContent,
            'Penugasan::batalkan must not assign relation $this->suratTugas to $suratTugasId'
        );

        $this->assertDoesNotMatchRegularExpression(
            '/\$suratPerjadinId\s*=\s*\$this->suratPerjadin;/',
            $fileContent,
            'Penugasan::batalkan must not assign relation $this->suratPerjadin to $suratPerjadinId'
        );

        // Ensure $this->surat_tugas_id is used instead
        $this->assertMatchesRegularExpression(
            '/\$suratTugasId\s*=\s*\$this->surat_tugas_id;/',
            $fileContent,
            'Penugasan::batalkan must assign $this->surat_tugas_id to $suratTugasId'
        );

        $this->assertMatchesRegularExpression(
            '/\$suratPerjadinId\s*=\s*\$this->surat_perjadin_id;/',
            $fileContent,
            'Penugasan::batalkan must assign $this->surat_perjadin_id to $suratPerjadinId'
        );
    }

    public function test_batalkan_detaches_foreign_keys_before_deleting_nomor_surat(): void
    {
        $fileContent = file_get_contents(app_path('Models/Penugasan.php'));

        $this->assertStringContainsString('$this->surat_tugas_id = null;', $fileContent);
        $this->assertStringContainsString('$this->surat_perjadin_id = null;', $fileContent);
    }
}
