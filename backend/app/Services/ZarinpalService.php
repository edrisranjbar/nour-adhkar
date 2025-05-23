<?php

namespace App\Services;

use App\Models\Donation;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ZarinpalService
{
    /**
     * Zarinpal API endpoints
     */
    protected const ZARINPAL_API_REQUEST = 'https://api.zarinpal.com/pg/v4/payment/request.json';
    protected const ZARINPAL_API_VERIFY = 'https://api.zarinpal.com/pg/v4/payment/verify.json';
    protected const ZARINPAL_GATEWAY = 'https://www.zarinpal.com/pg/StartPay/';
    
    /**
     * Zarinpal merchant ID from config
     */
    protected string $merchantId;
    
    /**
     * Callback URL for redirecting after payment
     */
    protected string $callbackUrl;
    
    public function __construct()
    {
        $this->merchantId = config('services.zarinpal.merchant_id');
        $this->callbackUrl = config('app.url') . '/api/donations/verify';
    }
    
    /**
     * Request a payment from Zarinpal
     * 
     * @param Donation $donation
     * @return array
     * @throws Exception
     */
    public function request(Donation $donation): array
    {
        try {
            $response = Http::post(self::ZARINPAL_API_REQUEST, [
                'merchant_id' => $this->merchantId,
                'amount' => $donation->amount,
                'description' => 'حمایت از اذکار نور',
                'callback_url' => $this->callbackUrl,
                'metadata' => [
                    'email' => $donation->email,
                ],
            ]);
            
            $result = $response->json();
            
            if ($response->successful() && $result['data']['code'] == 100) {
                // Update donation with authority
                $donation->update([
                    'transaction_id' => $result['data']['authority'],
                ]);
                
                return [
                    'success' => true,
                    'redirect_url' => self::ZARINPAL_GATEWAY . $result['data']['authority'],
                ];
            }
            
            Log::error('Zarinpal payment request failed', [
                'donation_id' => $donation->id,
                'response' => $result,
            ]);
            
            return [
                'success' => false,
                'message' => 'خطا در اتصال به درگاه پرداخت. لطفا مجددا تلاش کنید.',
                'error_code' => $result['errors']['code'] ?? $result['data']['code'] ?? 'unknown',
            ];
        } catch (Exception $e) {
            Log::error('Zarinpal payment request exception', [
                'donation_id' => $donation->id,
                'exception' => $e->getMessage(),
            ]);
            
            throw new Exception('خطا در اتصال به درگاه پرداخت: ' . $e->getMessage());
        }
    }
    
    /**
     * Verify a payment from Zarinpal
     * 
     * @param string $authority
     * @param int $amount
     * @return array
     */
    public function verify(string $authority, int $amount): array
    {
        try {
            $response = Http::post(self::ZARINPAL_API_VERIFY, [
                'merchant_id' => $this->merchantId,
                'authority' => $authority,
                'amount' => $amount,
            ]);
            
            $result = $response->json();
            
            if ($response->successful() && 
                ($result['data']['code'] == 100 || $result['data']['code'] == 101)) {
                
                return [
                    'success' => true,
                    'ref_id' => $result['data']['ref_id'],
                    'card_pan' => $result['data']['card_pan'] ?? null,
                    'card_hash' => $result['data']['card_hash'] ?? null,
                ];
            }
            
            Log::error('Zarinpal payment verification failed', [
                'authority' => $authority,
                'amount' => $amount,
                'response' => $result,
            ]);
            
            return [
                'success' => false,
                'message' => 'تراکنش ناموفق بود. وجه پرداختی به حساب شما برگشت داده خواهد شد.',
                'error_code' => $result['errors']['code'] ?? $result['data']['code'] ?? 'unknown',
            ];
        } catch (Exception $e) {
            Log::error('Zarinpal payment verification exception', [
                'authority' => $authority,
                'amount' => $amount,
                'exception' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'message' => 'خطا در تایید پرداخت: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Generate a unique reference ID
     * 
     * @return string
     */
    public static function generateReferenceId(): string
    {
        return 'ND' . strtoupper(Str::random(8));
    }
} 