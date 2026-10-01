<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProgressSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['jwt.secret' => str_repeat('test-only-progress-sync-', 3)]);
    }

    private function record(int $modified = 100, bool $deleted = false, string $device = '00000000-0000-4000-8000-000000000001'): array
    {
        return ['key' => 's:activity_day_keys:MTIz', 'modified' => $modified, 'device' => $device,
            'deleted' => $deleted, 'value' => $deleted ? null : true];
    }

    public function test_requires_authentication(): void
    {
        $this->postJson('/api/progress/sync', ['version' => 1, 'records' => []])->assertUnauthorized();
    }

    public function test_restores_only_the_signed_in_users_progress(): void
    {
        $first = User::factory()->create();
        $second = User::factory()->create();
        $this->actingAs($first, 'api')->postJson('/api/progress/sync', ['version' => 1, 'records' => [$this->record()]])->assertOk()->assertJsonCount(1, 'records');
        $this->actingAs($second, 'api')->postJson('/api/progress/sync', ['version' => 1, 'records' => []])->assertOk()->assertJsonCount(0, 'records');
        $this->actingAs($first, 'api')->postJson('/api/progress/sync', ['version' => 1, 'records' => []])->assertOk()->assertJsonPath('records.0.value', true);
    }

    public function test_stale_upload_does_not_undo_deletion_and_retries_are_idempotent(): void
    {
        $this->actingAs(User::factory()->create(), 'api');
        foreach ([$this->record(), $this->record(200, true), $this->record(), $this->record()] as $record) {
            $this->postJson('/api/progress/sync', ['version' => 1, 'records' => [$record]])->assertOk();
        }
        $this->postJson('/api/progress/sync', ['version' => 1, 'records' => []])->assertJsonCount(1, 'records')->assertJsonPath('records.0.deleted', true);
        $this->assertEquals(1, DB::table('app_progress_backups')->count());
    }

    public function test_equal_timestamps_have_a_deterministic_winner(): void
    {
        $this->actingAs(User::factory()->create(), 'api');
        $newer = $this->record(100, true, '00000000-0000-4000-8000-000000000002');
        $this->postJson('/api/progress/sync', ['version' => 1, 'records' => [$newer]])->assertOk();
        $this->postJson('/api/progress/sync', ['version' => 1, 'records' => [$this->record()]])->assertOk()->assertJsonPath('records.0.deleted', true);
    }

    public function test_rejects_unknown_versions_credentials_and_malformed_history(): void
    {
        $this->actingAs(User::factory()->create(), 'api');
        $this->postJson('/api/progress/sync', ['version' => 2, 'records' => []])->assertUnprocessable();
        $bad = $this->record(); $bad['key'] = 'p:token';
        $this->postJson('/api/progress/sync', ['version' => 1, 'records' => [$bad]])->assertUnprocessable();
        $bad['key'] = 'h:'.str_repeat('a', 64);
        $this->postJson('/api/progress/sync', ['version' => 1, 'records' => [$bad]])->assertUnprocessable();
        $this->assertEquals(0, DB::table('app_progress_backups')->count());
    }

    public function test_account_deletion_removes_backup(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api')->postJson('/api/progress/sync', ['version' => 1, 'records' => [$this->record()]])->assertOk();
        $user->delete();
        $this->assertEquals(0, DB::table('app_progress_backups')->count());
    }
}
