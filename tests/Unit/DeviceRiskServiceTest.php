<?php

namespace Tests\Unit;

use App\Models\Absence;
use App\Models\Setting;
use App\Models\User;
use App\Services\DeviceRiskService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceRiskServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_same_day_device_borrowing_sets_warning_and_danger(): void
    {
        Carbon::setTestNow('2026-04-08 08:00:00');

        $service = app(DeviceRiskService::class);
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Owner checks in first.
        $risk1 = $service->assessForCheckIn($user1, 'shared-device');
        $this->assertSame('safe', $risk1);

        $absenceUser1 = Absence::create([
            'user_id' => $user1->id,
            'tanggal' => today(),
            'jam_masuk' => now(),
            'risk_level' => $risk1,
        ]);

        // Borrower checks in with same device on the same day.
        $risk2 = $service->assessForCheckIn($user2, 'shared-device');
        $this->assertSame('danger', $risk2);

        $absenceUser2 = Absence::create([
            'user_id' => $user2->id,
            'tanggal' => today(),
            'jam_masuk' => now(),
            'risk_level' => $risk2,
        ]);

        $absenceUser1->refresh();
        $absenceUser2->refresh();

        // Owner is warned, borrower is danger.
        $this->assertSame('warning', $absenceUser1->risk_level);
        $this->assertSame('danger', $absenceUser2->risk_level);
    }

    public function test_next_day_no_borrowing_resets_to_safe_while_history_remains(): void
    {
        $service = app(DeviceRiskService::class);
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Day 1: shared device triggers risk.
        Carbon::setTestNow('2026-04-08 08:00:00');

        $day1RiskUser1 = $service->assessForCheckIn($user1, 'shared-device');
        Absence::create([
            'user_id' => $user1->id,
            'tanggal' => today(),
            'jam_masuk' => now(),
            'risk_level' => $day1RiskUser1,
        ]);

        $day1RiskUser2 = $service->assessForCheckIn($user2, 'shared-device');
        Absence::create([
            'user_id' => $user2->id,
            'tanggal' => today(),
            'jam_masuk' => now(),
            'risk_level' => $day1RiskUser2,
        ]);

        // Day 2: each user uses a different device, should be safe again.
        Carbon::setTestNow('2026-04-09 08:00:00');

        $day2RiskUser1 = $service->assessForCheckIn($user1, 'user1-private-device');
        $day2RiskUser2 = $service->assessForCheckIn($user2, 'user2-private-device');

        $this->assertSame('safe', $day2RiskUser1);
        $this->assertSame('safe', $day2RiskUser2);

        $day2AbsenceUser1 = Absence::create([
            'user_id' => $user1->id,
            'tanggal' => today(),
            'jam_masuk' => now(),
            'risk_level' => $day2RiskUser1,
        ]);

        $day2AbsenceUser2 = Absence::create([
            'user_id' => $user2->id,
            'tanggal' => today(),
            'jam_masuk' => now(),
            'risk_level' => $day2RiskUser2,
        ]);

        $this->assertSame('safe', $day2AbsenceUser1->risk_level);
        $this->assertSame('safe', $day2AbsenceUser2->risk_level);

        // History remains in database (4 absences across 2 days).
        $this->assertDatabaseCount('absences', 4);
    }

    public function test_device_validation_off_always_returns_safe_even_if_device_is_shared(): void
    {
        Setting::updateOrCreate(
            ['key' => 'device_validation_enabled'],
            ['value' => '0', 'type' => 'boolean', 'description' => 'test']
        );

        Carbon::setTestNow('2026-04-08 08:00:00');

        $service = app(DeviceRiskService::class);
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $risk1 = $service->assessForCheckIn($user1, 'shared-device');
        $risk2 = $service->assessForCheckIn($user2, 'shared-device');

        $this->assertSame('safe', $risk1);
        $this->assertSame('safe', $risk2);
    }
}
