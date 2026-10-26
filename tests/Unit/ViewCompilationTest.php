<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;

class ViewCompilationTest extends TestCase
{
    /**
     * Test that all Blade views compile cleanly without missing component errors.
     */
    public function test_all_blade_views_compile_without_error(): void
    {
        $exitCode = Artisan::call('view:cache');
        $output = Artisan::output();

        $this->assertSame(0, $exitCode, 'view:cache should succeed: ' . $output);
        $this->assertStringContainsString('Blade templates cached successfully', $output);
    }
}
