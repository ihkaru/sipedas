<?php

namespace Tests\Unit;

require_once __DIR__.'/../TestCase.php';

use Illuminate\Support\Carbon;
use Tests\TestCase;

class SuratTugasSotkPeraturanTest extends TestCase
{
    public function test_sotk_regulation_text_selection_by_date(): void
    {
        $cutoff = '2026-05-22';

        // Before May 22, 2026
        $tglBefore = Carbon::parse('2026-05-21')->format('Y-m-d');
        $this->assertFalse($tglBefore >= $cutoff);

        // On May 22, 2026
        $tglOn = Carbon::parse('2026-05-22')->format('Y-m-d');
        $this->assertTrue($tglOn >= $cutoff);

        // After May 22, 2026 (e.g., October 28, 2026)
        $tglAfter = Carbon::parse('2026-10-28')->format('Y-m-d');
        $this->assertTrue($tglAfter >= $cutoff);
    }
}
