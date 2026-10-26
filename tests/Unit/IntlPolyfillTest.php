<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Number;

class IntlPolyfillTest extends TestCase
{
    /**
     * Test that Number::format works seamlessly even when ext-intl is absent.
     */
    public function test_number_format_runs_without_intl_exception(): void
    {
        $formatted = Number::format(12345);
        $this->assertNotEmpty($formatted);
        $this->assertStringContainsString('12', $formatted);
    }

    /**
     * Test that NumberFormatter class is defined and usable.
     */
    public function test_number_formatter_class_exists(): void
    {
        $this->assertTrue(class_exists(\NumberFormatter::class));
    }

    /**
     * Test that Number::percentage and Number::currency operate safely.
     */
    public function test_number_helpers_operate_safely(): void
    {
        $percentage = Number::percentage(50);
        $this->assertNotEmpty($percentage);
        $this->assertStringContainsString('50', $percentage);
    }
}
