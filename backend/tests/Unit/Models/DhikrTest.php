<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\UserDhikr;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DhikrTest extends TestCase
{
    use RefreshDatabase;

    public function test_dhikr_belongs_to_user()
    {
        $user = User::factory()->create();
        $dhikr = UserDhikr::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $dhikr->user);
        $this->assertEquals($user->id, $dhikr->user->id);
    }

    public function test_dhikr_has_required_fields()
    {
        $dhikr = UserDhikr::factory()->create([
            'title' => 'Test Dhikr',
            'arabic_text' => 'اللَّهُمَّ',
            'translation' => 'O Allah',
            'transliteration' => 'Allahumma'
        ]);

        $this->assertEquals('Test Dhikr', $dhikr->title);
        $this->assertEquals('اللَّهُمَّ', $dhikr->arabic_text);
        $this->assertEquals('O Allah', $dhikr->translation);
        $this->assertEquals('Allahumma', $dhikr->transliteration);
    }

    public function test_dhikr_has_default_values()
    {
        $dhikr = UserDhikr::factory()->create();

        $this->assertNotNull($dhikr->title);
        $this->assertNotNull($dhikr->arabic_text);
        $this->assertNotNull($dhikr->translation);
        $this->assertNotNull($dhikr->transliteration);
        $this->assertFalse($dhikr->is_completed);
        $this->assertEquals(0, $dhikr->completed_count);
    }

    public function test_dhikr_factory_creates_valid_dhikr()
    {
        $dhikr = UserDhikr::factory()->create();

        $this->assertInstanceOf(UserDhikr::class, $dhikr);
        $this->assertNotNull($dhikr->id);
        $this->assertNotNull($dhikr->user_id);
        $this->assertNotNull($dhikr->title);
        $this->assertNotNull($dhikr->arabic_text);
        $this->assertNotNull($dhikr->translation);
        $this->assertNotNull($dhikr->transliteration);
        $this->assertNotNull($dhikr->count);
        $this->assertFalse($dhikr->is_completed);
        $this->assertEquals(0, $dhikr->completed_count);
    }
} 