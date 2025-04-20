<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\UserDhikr;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserDhikrTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_dhikr_belongs_to_user()
    {
        $user = User::factory()->create();
        $userDhikr = UserDhikr::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $userDhikr->user);
        $this->assertEquals($user->id, $userDhikr->user->id);
    }

    public function test_user_dhikr_has_required_fields()
    {
        $userDhikr = UserDhikr::factory()->create();

        $this->assertNotNull($userDhikr->user_id);
        $this->assertNotNull($userDhikr->arabic_text);
        $this->assertNotNull($userDhikr->translation);
        $this->assertNotNull($userDhikr->transliteration);
        $this->assertNotNull($userDhikr->count);
    }

    public function test_user_dhikr_has_default_values()
    {
        $userDhikr = UserDhikr::factory()->create();

        $this->assertEquals(0, $userDhikr->completed_count);
        $this->assertFalse($userDhikr->is_completed);
    }

    public function test_user_dhikr_factory_creates_valid_dhikr()
    {
        $userDhikr = UserDhikr::factory()->create();

        $this->assertInstanceOf(UserDhikr::class, $userDhikr);
        $this->assertNotNull($userDhikr->id);
        $this->assertNotNull($userDhikr->user_id);
        $this->assertNotNull($userDhikr->arabic_text);
        $this->assertNotNull($userDhikr->translation);
        $this->assertNotNull($userDhikr->transliteration);
        $this->assertNotNull($userDhikr->count);
    }
} 