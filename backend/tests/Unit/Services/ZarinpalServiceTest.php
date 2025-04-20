<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\ZarinpalService;
use App\Models\Donation;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Exception;

class ZarinpalServiceTest extends TestCase
{
    use RefreshDatabase;

    private ZarinpalService $zarinpalService;
    private Donation $donation;

    protected function setUp(): void
    {
        parent::setUp();
        $this->zarinpalService = new ZarinpalService();
        
        $user = User::factory()->create();
        $this->donation = Donation::factory()->create([
            'user_id' => $user->id,
            'amount' => 10000,
            'email' => 'test@example.com'
        ]);
    }

    public function test_successful_payment_request()
    {
        Http::fake([
            'api.zarinpal.com/pg/v4/payment/request.json' => Http::response([
                'data' => [
                    'code' => 100,
                    'message' => 'Success',
                    'authority' => '123456789',
                    'fee_type' => 'Merchant',
                    'fee' => 0
                ],
                'errors' => []
            ], 200)
        ]);

        $result = $this->zarinpalService->request($this->donation);

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('123456789', $result['redirect_url']);
        $this->assertEquals('123456789', $this->donation->fresh()->transaction_id);
    }

    public function test_failed_payment_request()
    {
        Http::fake([
            'api.zarinpal.com/pg/v4/payment/request.json' => Http::response([
                'data' => [
                    'code' => -9,
                    'message' => 'Error'
                ],
                'errors' => [
                    'code' => -9,
                    'message' => 'Error Message'
                ]
            ], 200)
        ]);

        $result = $this->zarinpalService->request($this->donation);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('message', $result);
        $this->assertEquals(-9, $result['error_code']);
    }

    public function test_payment_request_handles_network_error()
    {
        Http::fake([
            'api.zarinpal.com/pg/v4/payment/request.json' => function() {
                throw new \Illuminate\Http\Client\ConnectionException();
            }
        ]);

        $this->expectException(Exception::class);
        $this->zarinpalService->request($this->donation);
    }

    public function test_successful_payment_verification()
    {
        Http::fake([
            'api.zarinpal.com/pg/v4/payment/verify.json' => Http::response([
                'data' => [
                    'code' => 100,
                    'message' => 'Success',
                    'ref_id' => '12345',
                    'card_pan' => '1234****5678',
                    'card_hash' => 'hash123'
                ],
                'errors' => []
            ], 200)
        ]);

        $result = $this->zarinpalService->verify('authority123', 10000);

        $this->assertTrue($result['success']);
        $this->assertEquals('12345', $result['ref_id']);
        $this->assertEquals('1234****5678', $result['card_pan']);
        $this->assertEquals('hash123', $result['card_hash']);
    }

    public function test_failed_payment_verification()
    {
        Http::fake([
            'api.zarinpal.com/pg/v4/payment/verify.json' => Http::response([
                'data' => [
                    'code' => -9,
                    'message' => 'Error'
                ],
                'errors' => [
                    'code' => -9,
                    'message' => 'Error Message'
                ]
            ], 200)
        ]);

        $result = $this->zarinpalService->verify('authority123', 10000);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('message', $result);
        $this->assertEquals(-9, $result['error_code']);
    }

    public function test_payment_verification_handles_network_error()
    {
        Http::fake([
            'api.zarinpal.com/pg/v4/payment/verify.json' => function() {
                throw new \Illuminate\Http\Client\ConnectionException();
            }
        ]);

        $result = $this->zarinpalService->verify('authority123', 10000);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('message', $result);
    }

    public function test_generate_reference_id_format()
    {
        $refId = ZarinpalService::generateReferenceId();
        
        $this->assertStringStartsWith('ND', $refId);
        $this->assertEquals(10, strlen($refId));
        $this->assertMatchesRegularExpression('/^ND[A-Z0-9]{8}$/', $refId);
    }

    public function test_generate_reference_id_uniqueness()
    {
        $refIds = [];
        for ($i = 0; $i < 100; $i++) {
            $refIds[] = ZarinpalService::generateReferenceId();
        }
        
        $this->assertEquals(count($refIds), count(array_unique($refIds)));
    }
}