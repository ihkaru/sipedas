<?php

namespace Tests\Unit;

use Tests\TestCase;
use BladeUI\Icons\Factory;

class BladeIconsConfigurationTest extends TestCase
{
    /**
     * Test that blade-icons component auto-registration is disabled
     * to prevent fatal execution timeouts (30s) under FrankenPHP/Octane.
     */
    public function test_blade_icons_components_are_disabled_by_default(): void
    {
        $this->assertTrue(
            config('blade-icons.components.disabled'),
            'blade-icons components should be disabled to prevent registering thousands of individual SVG components'
        );
    }

    /**
     * Test that icons can still be resolved and rendered via SVG helper/factory.
     */
    public function test_icons_can_still_be_rendered(): void
    {
        $factory = app(Factory::class);
        $this->assertNotNull($factory);

        // Verify that default icon helper works
        $svg = svg('fluentui-settings-48-o');
        $this->assertNotEmpty($svg->toHtml());
        $this->assertStringContainsString('<svg', $svg->toHtml());
    }
}
