<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\Donation;
use App\Services\ZarinpalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tymon\JWTAuth\Facades\JWTAuth;

class DonationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->token = JWTAuth::fromUser($this->user);
    }

    public function test_can_get_recent_donations()
    {
        $this->user->update(['role' => 'admin']);

        Donation::factory()->count(3)->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/donations/recent');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'donations' => [
                        '*' => [
                            'name',
                            'amount',
                            'date'
                        ]
                    ]
                ]
            ]);
    }

    public function test_can_create_donation()
    {
        $this->mock(ZarinpalService::class, function ($mock) {
            $mock->shouldReceive('request')
                ->once()
                ->andReturn([
                    'success' => true,
                    'authority' => 'test-authority'
                ]);
        });

        $donationData = [
            'amount' => 100000,
            'description' => 'Test Donation'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/donations/create', $donationData);

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'message' => 'خطا در ایجاد درخواست پرداخت. لطفا مجددا تلاش کنید.'
            ]);
    }

    public function test_validation_fails_for_invalid_donation_amount()
    {
        $response = $this->postJson('/api/donations/create', [
            'amount' => 0,
            'description' => 'Test Donation'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }

    public function test_handles_network_error()
    {
        $this->mock(ZarinpalService::class, function ($mock) {
            $mock->shouldReceive('request')
                ->once()
                ->andThrow(new \Exception('Network error'));
        });

        $response = $this->postJson('/api/donations/create', [
            'amount' => 100000,
            'description' => 'Test Donation'
        ]);

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'message' => 'خطا در ایجاد درخواست پرداخت. لطفا مجددا تلاش کنید.'
            ]);
    }

    public function test_handles_missing_authority_in_verification()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/donations/verify?status=OK');

        $response->assertStatus(302)
            ->assertRedirect(config('app.frontend_url') . '/donation/failed?message=' . urlencode('پرداخت توسط کاربر لغو شد'));
    }

    public function test_handles_invalid_status_in_verification()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/donations/verify?authority=test-authority&status=INVALID');

        $response->assertStatus(302)
            ->assertRedirect(config('app.frontend_url') . '/donation/failed?message=' . urlencode('پرداخت توسط کاربر لغو شد'));
    }

    public function test_can_get_user_donations()
    {
        Donation::factory()->count(3)->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/donations/user');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'donations' => [
                    '*' => [
                        'id',
                        'amount',
                        'status',
                        'authority',
                        'ref_id',
                        'created_at'
                    ]
                ]
            ]);
    }

    public function test_handles_zarinpal_service_error()
    {
        $this->mock(ZarinpalService::class, function ($mock) {
            $mock->shouldReceive('request')
                ->once()
                ->andReturn([
                    'success' => false,
                    'message' => 'خطا در اتصال به درگاه پرداخت. لطفا مجددا تلاش کنید.'
                ]);
        });

        $response = $this->postJson('/api/donations/create', [
            'amount' => 100000,
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'خطا در اتصال به درگاه پرداخت. لطفا مجددا تلاش کنید.'
            ]);

        $this->assertDatabaseHas('donations', [
            'amount' => 100000,
            'status' => 'failed'
        ]);
    }

    public function test_handles_donation_not_found_in_verification()
    {
        $response = $this->get('/api/donations/verify?Authority=non-existent&Status=OK');

        $response->assertStatus(302)
            ->assertRedirect(config('app.frontend_url') . '/donation/failed?message=' . urlencode('تراکنش قابل شناسایی نیست'));
    }

    public function test_handles_verification_failure()
    {
        // Create a pending donation
        $donation = Donation::factory()->create([
            'status' => 'pending',
            'transaction_id' => 'test-authority',
            'amount' => 100000
        ]);

        $this->mock(ZarinpalService::class, function ($mock) {
            $mock->shouldReceive('verify')
                ->once()
                ->with('test-authority', 100000)
                ->andReturn([
                    'success' => false,
                    'message' => 'پرداخت ناموفق'
                ]);
        });

        $response = $this->get('/api/donations/verify?Authority=test-authority&Status=OK');

        $response->assertStatus(302)
            ->assertRedirect(config('app.frontend_url') . '/donation/failed?message=' . urlencode('پرداخت ناموفق'));

        $this->assertDatabaseHas('donations', [
            'id' => $donation->id,
            'status' => 'failed'
        ]);
    }

    public function test_handles_successful_verification()
    {
        // Create a pending donation
        $donation = Donation::factory()->create([
            'status' => 'pending',
            'transaction_id' => 'test-authority',
            'amount' => 100000
        ]);

        $this->mock(ZarinpalService::class, function ($mock) {
            $mock->shouldReceive('verify')
                ->once()
                ->with('test-authority', 100000)
                ->andReturn([
                    'success' => true,
                    'card_pan' => '1234********5678'
                ]);
        });

        $response = $this->get('/api/donations/verify?Authority=test-authority&Status=OK');

        $response->assertStatus(302)
            ->assertRedirect(config('app.frontend_url') . '/donation/success?reference=' . $donation->reference_id);

        $this->assertDatabaseHas('donations', [
            'id' => $donation->id,
            'status' => 'completed',
            'card_pan' => '1234********5678'
        ]);
    }

    public function test_handles_unauthenticated_user_donations()
    {
        $response = $this->getJson('/api/donations/user');

        $response->assertStatus(401);
    }

    public function test_returns_empty_donations_for_new_user()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/donations/user');

        $response->assertStatus(200)
            ->assertJson([
                'donations' => []
            ]);
    }
} 