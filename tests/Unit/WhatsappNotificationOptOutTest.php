<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Setting;
use App\Services\WhatsappNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WhatsappNotificationOptOutTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_opt_out_evaluation_with_numbers(): void
    {
        // When setting is empty, nobody is opted out
        Setting::where('key', 'whatsapp_opt_out_numbers')->delete();
        $this->assertFalse(WhatsappNotifier::isNumberOptedOut('628123456789'));

        // Set opt out for 08123456789
        Setting::updateOrCreate(
            ['key' => 'whatsapp_opt_out_numbers'],
            ['value' => json_encode(['08123456789', '62899999999'])]
        );

        // Matching with different prefix (62 vs 08)
        $this->assertTrue(WhatsappNotifier::isNumberOptedOut('628123456789'));
        $this->assertTrue(WhatsappNotifier::isNumberOptedOut('08123456789'));
        $this->assertTrue(WhatsappNotifier::isNumberOptedOut('628123456789@s.whatsapp.net'));

        // Non-opted out number
        $this->assertFalse(WhatsappNotifier::isNumberOptedOut('087711223344'));
    }

    public function test_master_switch_and_event_evaluation(): void
    {
        // Default when not present is true
        Setting::where('key', 'whatsapp_notification_enabled')->delete();
        $this->assertTrue(WhatsappNotifier::isNotificationEnabled());

        // Explicitly disabled
        Setting::updateOrCreate(['key' => 'whatsapp_notification_enabled'], ['value' => '0']);
        $this->assertFalse(WhatsappNotifier::isNotificationEnabled());

        // Explicitly enabled
        Setting::updateOrCreate(['key' => 'whatsapp_notification_enabled'], ['value' => '1']);
        $this->assertTrue(WhatsappNotifier::isNotificationEnabled());

        // Event check
        Setting::updateOrCreate(['key' => 'whatsapp_notify_pencairan'], ['value' => '0']);
        $this->assertFalse(WhatsappNotifier::isEventEnabled('whatsapp_notify_pencairan'));
        $this->assertTrue(WhatsappNotifier::isEventEnabled('whatsapp_notify_pengajuan_baru'));
    }
}
