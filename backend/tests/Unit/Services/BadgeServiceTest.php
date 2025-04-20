<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\BadgeService;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BadgeServiceTest extends TestCase
{
    use RefreshDatabase;

    private BadgeService $badgeService;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->badgeService = new BadgeService();
        $this->user = User::factory()->create();
    }

    public function test_initialize_badges()
    {
        $this->badgeService->initializeBadges($this->user);

        $this->assertIsArray($this->user->badges);
        $this->assertFalse($this->user->badges['beginner']);
        $this->assertNull($this->user->badges['beginner_date']);
        $this->assertFalse($this->user->badges['hardworker']);
        $this->assertNull($this->user->badges['hardworker_date']);
        $this->assertFalse($this->user->badges['consistent']);
        $this->assertNull($this->user->badges['consistent_date']);
        $this->assertFalse($this->user->badges['golden_heart']);
        $this->assertNull($this->user->badges['golden_heart_date']);
    }

    public function test_check_and_award_beginner_badge()
    {
        $this->user->total_dhikrs = 1;
        $this->user->save();

        $updated = $this->badgeService->checkAndAwardBadges($this->user);

        $this->assertTrue($updated);
        $this->assertTrue($this->user->badges['beginner']);
        $this->assertEquals(now()->toDateString(), $this->user->badges['beginner_date']);
    }

    public function test_check_and_award_hardworker_badge()
    {
        $this->user->total_dhikrs = 100;
        $this->user->save();

        $updated = $this->badgeService->checkAndAwardBadges($this->user);

        $this->assertTrue($updated);
        $this->assertTrue($this->user->badges['hardworker']);
        $this->assertEquals(now()->toDateString(), $this->user->badges['hardworker_date']);
    }

    public function test_check_and_award_consistent_badge()
    {
        $dates = [];
        for ($i = 6; $i >= 0; $i--) {
            $dates[] = Carbon::today()->subDays($i)->toDateString();
        }
        $this->user->completed_dates = $dates;
        $this->user->save();

        $updated = $this->badgeService->checkAndAwardBadges($this->user);

        $this->assertTrue($updated);
        $this->assertTrue($this->user->badges['consistent']);
        $this->assertEquals(now()->toDateString(), $this->user->badges['consistent_date']);
    }

    public function test_check_and_award_golden_heart_badge()
    {
        $this->user->heart_score = 100;
        $this->user->save();

        $updated = $this->badgeService->checkAndAwardBadges($this->user);

        $this->assertTrue($updated);
        $this->assertTrue($this->user->badges['golden_heart']);
        $this->assertEquals(now()->toDateString(), $this->user->badges['golden_heart_date']);
    }

    public function test_no_badge_awarded_when_conditions_not_met()
    {
        $this->user->total_dhikrs = 0;
        $this->user->completed_dates = [];
        $this->user->heart_score = 0;
        $this->user->save();

        $updated = $this->badgeService->checkAndAwardBadges($this->user);

        $this->assertFalse($updated);
        $this->assertFalse($this->user->badges['beginner']);
        $this->assertFalse($this->user->badges['hardworker']);
        $this->assertFalse($this->user->badges['consistent']);
        $this->assertFalse($this->user->badges['golden_heart']);
    }

    public function test_update_streak_first_time()
    {
        $this->user->completed_dates = [];
        $this->user->last_dhikr_completed_at = null;
        $this->user->save();

        $this->badgeService->updateStreak($this->user);

        $this->assertEquals(1, $this->user->streak);
        $this->assertEquals(Carbon::today()->toDateString(), $this->user->last_dhikr_completed_at->toDateString());
        $this->assertContains(Carbon::today()->toDateString(), $this->user->completed_dates);
    }

    public function test_update_streak_consecutive_day()
    {
        $yesterday = Carbon::yesterday()->toDateString();
        $this->user->completed_dates = [$yesterday];
        $this->user->last_dhikr_completed_at = Carbon::yesterday();
        $this->user->save();

        $this->badgeService->updateStreak($this->user);

        $this->assertEquals(2, $this->user->streak);
        $this->assertEquals(Carbon::today()->toDateString(), $this->user->last_dhikr_completed_at->toDateString());
        $this->assertContains($yesterday, $this->user->completed_dates);
        $this->assertContains(Carbon::today()->toDateString(), $this->user->completed_dates);
    }

    public function test_update_streak_break_streak()
    {
        $twoDaysAgo = Carbon::now()->subDays(2)->toDateString();
        $this->user->completed_dates = [$twoDaysAgo];
        $this->user->last_dhikr_completed_at = Carbon::now()->subDays(2);
        $this->user->save();

        $this->badgeService->updateStreak($this->user);

        $this->assertEquals(1, $this->user->streak);
        $this->assertEquals(Carbon::today()->toDateString(), $this->user->last_dhikr_completed_at->toDateString());
        $this->assertContains($twoDaysAgo, $this->user->completed_dates);
        $this->assertContains(Carbon::today()->toDateString(), $this->user->completed_dates);
    }

    public function test_update_streak_same_day_no_change()
    {
        $today = Carbon::today()->toDateString();
        $yesterday = Carbon::yesterday()->toDateString();
        $this->user->completed_dates = [$yesterday, $today];
        $this->user->last_dhikr_completed_at = Carbon::today();
        $this->user->save();

        $this->badgeService->updateStreak($this->user);

        $this->assertEquals(2, $this->user->streak);
        $this->assertEquals(Carbon::today()->toDateString(), $this->user->last_dhikr_completed_at->toDateString());
        $this->assertContains($yesterday, $this->user->completed_dates);
        $this->assertContains($today, $this->user->completed_dates);
    }

    public function test_award_badge_directly()
    {
        $this->badgeService->awardBadge($this->user, 'beginner');

        $this->assertTrue($this->user->badges['beginner']);
        $this->assertEquals(now()->toDateString(), $this->user->badges['beginner_date']);
    }

    public function test_award_badge_does_not_overwrite_existing()
    {
        $originalDate = '2023-01-01';
        $this->user->badges = [
            'beginner' => true,
            'beginner_date' => $originalDate
        ];
        $this->user->save();

        $this->badgeService->awardBadge($this->user, 'beginner');

        $this->assertTrue($this->user->badges['beginner']);
        $this->assertEquals($originalDate, $this->user->badges['beginner_date']);
    }
} 