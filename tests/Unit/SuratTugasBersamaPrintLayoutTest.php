<?php

namespace Tests\Unit;

use Tests\TestCase;

class SuratTugasBersamaPrintLayoutTest extends TestCase
{
    public function test_surat_tugas_bersama_uses_paged_media_landscape_without_css_rotate_hack(): void
    {
        $templatePath = resource_path('views/surat_tugas/bersama/pdf.blade.php');
        $content = file_get_contents($templatePath);

        $this->assertNotFalse($content);

        // Must NOT use legacy transform: rotate(270deg) hack which causes >50 rows to overflow/leak into page 1
        $this->assertStringNotContainsString('rotate(270deg)', $content);
        $this->assertStringNotContainsString('translate(-250mm', $content);

        // Must use true CSS Paged Media named landscape page for multi-page pagination
        $this->assertStringContainsString('@page landscape-page', $content);
        $this->assertStringContainsString('size: A4 landscape', $content);
        $this->assertStringContainsString('page: landscape-page', $content);

        // Must wrap lampiran table header in <thead> with repeating header group and row/signature break protection
        $this->assertStringContainsString('display: table-header-group', $content);
        $this->assertStringContainsString('break-inside: avoid', $content);
        $this->assertStringContainsString('<table class="lampiran-table"', $content);
        $this->assertStringContainsString('<thead>', $content);
        $this->assertStringContainsString('<tbody>', $content);
        $this->assertStringContainsString('class="lampiran-signature"', $content);
    }
}
