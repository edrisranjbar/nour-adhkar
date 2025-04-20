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
                'donations' => [
                    '*' => [
                        'id',
                        'amount',
                        'status',
                        'created_at'
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

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'authority',
                'payment_url'
            ]);
    }

    public function test_can_verify_donation()
    {
        $this->mock(ZarinpalService::class, function ($mock) {
            $mock->shouldReceive('verify')
                ->once()
                ->andReturn([
                    'success' => true,
                    'ref_id' => '123456'
                ]);
        });

        $donation = Donation::factory()->create([
            'user_id' => $this->user->id,
            'authority' => 'test-authority'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/donations/verify?authority=test-authority&status=OK');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'ref_id'
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

    public function test_handles_failed_payment()
    {
        $this->mock(ZarinpalService::class, function ($mock) {
            $mock->shouldReceive('verify')
                ->once()
                ->andReturn([
                    'success' => false,
                    'error' => 'Payment failed'
                ]);
        });

        $donation = Donation::factory()->create([
            'user_id' => $this->user->id,
            'authority' => 'test-authority'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/donations/verify?authority=test-authority&status=OK');

        $response->assertStatus(400)
            ->assertJson([
                'error' => 'پرداخت با شکست مواجه شد'
            ]);
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
} 