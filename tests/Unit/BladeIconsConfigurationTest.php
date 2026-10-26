<?php

namespace Tests\Unit;

use Tests\TestCase;
use BladeUI\Icons\Factory;

class BladeIconsConfigurationTest extends TestCase
{
    /**
     * Test that blade-icons configuration is properly configured.
     */
    public function test_blade_icons_configuration_is_valid(): void
    {
        $this->assertIsBool(
            config('blade-icons.components.disabled')
        );
    }

    /**
     * Test that icons can still be rendered via SVG helper/factory.
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
