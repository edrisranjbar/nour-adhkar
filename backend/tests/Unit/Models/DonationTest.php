<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Donation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DonationTest extends TestCase
{
    use RefreshDatabase;

    public function test_donation_has_required_fields()
    {
        $user = User::factory()->create();
        $donation = Donation::factory()->create([
            'user_id' => $user->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'amount' => 1000,
            'transaction_id' => 'tx_123',
            'reference_id' => 'ref_123',
            'card_pan' => '1234********5678',
            'status' => 'completed',
            'description' => 'Monthly donation',
            'paid_at' => now(),
        ]);

        $this->assertEquals($user->id, $donation->user_id);
        $this->assertEquals('John Doe', $donation->name);
        $this->assertEquals('john@example.com', $donation->email);
        $this->assertEquals(1000, $donation->amount);
        $this->assertEquals('tx_123', $donation->transaction_id);
        $this->assertEquals('ref_123', $donation->reference_id);
        $this->assertEquals('1234********5678', $donation->card_pan);
        $this->assertEquals('completed', $donation->status);
        $this->assertEquals('Monthly donation', $donation->description);
        $this->assertNotNull($donation->paid_at);
    }
    
    public function test_donation_belongs_to_user()
    {
        $user = User::factory()->create();
        $donation = Donation::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $donation->user);
        $this->assertEquals($user->id, $donation->user->id);
    }

    public function test_successful_scope_returns_only_completed_donations()
    {
        // Create completed donations
        Donation::factory()->count(3)->create(['status' => 'completed']);

        // Create pending donations
        Donation::factory()->count(2)->create(['status' => 'pending']);

        // Create failed donations
        Donation::factory()->create(['status' => 'failed']);

        $successfulDonations = Donation::successful()->get();

        $this->assertCount(3, $successfulDonations);
        $this->assertEquals('completed', $successfulDonations->first()->status);
    }

    public function test_recent_scope_returns_limited_completed_donations_ordered_by_paid_at()
    {
        // Create older donations with specific dates
        $olderDate = now()->subDays(5);
        Donation::factory()->count(3)->create([
            'status' => 'completed',
            'paid_at' => $olderDate,
        ]);

        // Create newer donations with specific dates
        $newerDate = now()->subDay();
        Donation::factory()->count(2)->create([
            'status' => 'completed',
            'paid_at' => $newerDate,
        ]);

        // Create pending donations
        Donation::factory()->create([
            'status' => 'pending',
            'paid_at' => null,
        ]);

        $recentDonations = Donation::recent(2)->get();

        $this->assertCount(2, $recentDonations);
        $this->assertEquals('completed', $recentDonations->first()->status);
    }

    public function test_attributes_are_cast_correctly()
    {
        $donation = Donation::factory()->create([
            'amount' => '1000', // String that should be cast to integer
            'paid_at' => now(),
        ]);

        $this->assertIsInt($donation->amount);
        $this->assertInstanceOf(Carbon::class, $donation->paid_at);
        $this->assertEquals(1000, $donation->amount);
    }

    public function test_fillable_attributes()
    {
        $donation = new Donation();
        
        $this->assertTrue(in_array('user_id', $donation->getFillable()));
        $this->assertTrue(in_array('name', $donation->getFillable()));
        $this->assertTrue(in_array('email', $donation->getFillable()));
        $this->assertTrue(in_array('amount', $donation->getFillable()));
        $this->assertTrue(in_array('transaction_id', $donation->getFillable()));
        $this->assertTrue(in_array('reference_id', $donation->getFillable()));
        $this->assertTrue(in_array('card_pan', $donation->getFillable()));
        $this->assertTrue(in_array('status', $donation->getFillable()));
        $this->assertTrue(in_array('description', $donation->getFillable()));
        $this->assertTrue(in_array('paid_at', $donation->getFillable()));
    }
} 