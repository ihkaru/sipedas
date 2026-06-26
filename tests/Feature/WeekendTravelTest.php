<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Supports\TanggalMerah;
use Illuminate\Support\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WeekendTravelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Reset static cache of TanggalMerah between tests
        TanggalMerah::$tanggalMerahDates = null;
    }

    public function test_weekends_are_libur_by_default(): void
    {
        // Ensure no setting exists or it is set to false
        Setting::where('key', 'allow_weekend_travel')->delete();

        // 2026-06-27 is Saturday, 2026-06-28 is Sunday
        $saturday = Carbon::parse('2026-06-27');
        $sunday = Carbon::parse('2026-06-28');

        $this->assertTrue(TanggalMerah::isLibur($saturday));
        $this->assertTrue(TanggalMerah::isLibur($sunday));
    }

    public function test_weekends_are_not_libur_when_allow_weekend_travel_is_true(): void
    {
        // Enable weekend travel setting
        Setting::updateOrCreate(
            ['key' => 'allow_weekend_travel'],
            ['value' => '1']
        );

        // 2026-06-27 is Saturday, 2026-06-28 is Sunday
        $saturday = Carbon::parse('2026-06-27');
        $sunday = Carbon::parse('2026-06-28');

        $this->assertFalse(TanggalMerah::isLibur($saturday));
        $this->assertFalse(TanggalMerah::isLibur($sunday));
    }
}
