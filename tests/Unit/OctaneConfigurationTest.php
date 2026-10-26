<?php

namespace Tests\Unit;

use Tests\TestCase;

class OctaneConfigurationTest extends TestCase
{
    /**
     * Test that Octane max_execution_time is disabled (0) by default
     * to prevent fatal execution timeouts (30s) under FrankenPHP workers.
     */
    public function test_octane_max_execution_time_is_zero_by_default(): void
    {
        $this->assertSame(
            0,
            config('octane.max_execution_time'),
            'Octane max_execution_time should be 0 to avoid cumulative worker timeout fatal errors'
        );
    }

    /**
     * Test that Octane default server is configured to frankenphp.
     */
    public function test_octane_server_is_frankenphp(): void
    {
        $this->assertSame(
            'frankenphp',
            config('octane.server')
        );
    }
}
