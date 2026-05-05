<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\UserDhikr;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DhikrControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_index_returns_dhikrs()
    {
        UserDhikr::factory()->count(3)->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/dhikr');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'user' => [
                            'id',
                            'name'
                        ]
                    ]
                ]
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    public function test_index_with_search_returns_filtered_dhikrs()
    {
        UserDhikr::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Test Dhikr 1'
        ]);
        UserDhikr::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Another Dhikr'
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/dhikr?search=Test');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_store_updates_user_statistics()
    {
        $initialTotal = $this->user->total_dhikrs;
        
        $response = $this->actingAs($this->user)
            ->postJson('/api/dhikr');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'user',
            ]);

        $this->user->refresh();
        $this->assertEquals($initialTotal + 1, $this->user->total_dhikrs);
        $this->assertContains(now()->format('Y-m-d'), $this->user->completed_dates);
    }

    public function test_store_requires_authentication()
    {
        $response = $this->postJson('/api/dhikr');

        $response->assertStatus(401);
    }
}
