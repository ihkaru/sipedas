<?php

namespace Tests\Unit;

require_once __DIR__ . '/../TestCase.php';

use Tests\TestCase;

class LivewirePayloadConfigurationTest extends TestCase
{
    public function test_livewire_payload_max_size_is_configured_and_exceeds_1mb(): void
    {
        $maxSize = config('livewire.payload.max_size');

        $this->assertNotNull($maxSize, 'livewire.payload.max_size must not be null');
        $this->assertIsInt($maxSize, 'livewire.payload.max_size must be an integer');
        $this->assertGreaterThanOrEqual(
            10 * 1024 * 1024,
            $maxSize,
            'livewire.payload.max_size must be at least 10MB to accommodate large Livewire requests'
        );
    }

    public function test_livewire_payload_guards_retain_sensible_safety_limits(): void
    {
        $maxNestingDepth = config('livewire.payload.max_nesting_depth');
        $maxCalls = config('livewire.payload.max_calls');
        $maxComponents = config('livewire.payload.max_components');

        $this->assertSame(10, $maxNestingDepth);
        $this->assertSame(50, $maxCalls);
        $this->assertSame(200, $maxComponents);
    }

    public function test_livewire_payload_accommodates_1277kb_alert_payload(): void
    {
        $alertPayloadBytes = 1277 * 1024; // 1277KB from OpenObserve SRE alert
        $configuredLimit = config('livewire.payload.max_size');

        $this->assertGreaterThan(
            $alertPayloadBytes,
            $configuredLimit,
            'livewire.payload.max_size must be strictly greater than 1277KB to resolve PayloadTooLargeException'
        );
    }

    public function test_livewire_config_file_contains_payload_max_size_and_env_binding(): void
    {
        $configFile = file_get_contents(config_path('livewire.php'));

        $this->assertStringContainsString("'payload'", $configFile);
        $this->assertStringContainsString("'max_size'", $configFile);
        $this->assertStringContainsString("LIVEWIRE_PAYLOAD_MAX_SIZE", $configFile);
        $this->assertStringContainsString("10 * 1024 * 1024", $configFile);
    }
}
