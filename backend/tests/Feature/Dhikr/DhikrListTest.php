<?php

namespace Tests\Feature\Dhikr;

use Tests\TestCase;
use App\Models\User;
use App\Models\UserDhikr;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DhikrListTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_dhikr_list()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');
        
        $dhikrs = UserDhikr::factory()->count(3)->create([
            'user_id' => $user->id
        ]);

        $response = $this->getJson('/api/dhikr');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'arabic_text',
                        'translation',
                        'transliteration',
                        'count',
                        'user' => [
                            'id',
                            'name'
                        ]
                    ]
                ]
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_can_search_dhikr()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');
        
        $dhikr1 = UserDhikr::factory()->create([
            'title' => 'Morning Remembrance',
            'user_id' => $user->id
        ]);
        $dhikr2 = UserDhikr::factory()->create([
            'title' => 'Evening Prayer',
            'user_id' => $user->id
        ]);
        $dhikr3 = UserDhikr::factory()->create([
            'title' => 'Night Supplication',
            'user_id' => $user->id
        ]);

        $response = $this->getJson('/api/dhikr?search=morning');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $dhikr1->id);
    }
} 