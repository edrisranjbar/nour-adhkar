<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Services\ZarinpalService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DonationController extends Controller
{
    /**
     * @var ZarinpalService
     */
    protected $zarinpalService;
    
    public function __construct(ZarinpalService $zarinpalService)
    {
        $this->zarinpalService = $zarinpalService;
    }
    
    /**
     * Get recent donations
     * 
     * @return JsonResponse
     */
    public function getRecentDonations(): JsonResponse
    {
        $donations = Donation::recent(4)
            ->select(['id', 'name', 'amount', 'paid_at'])
            ->get()
            ->map(function($donation) {
                return [
                    'name' => $donation->name ?: 'حامی ناشناس',
                    'amount' => $donation->amount,
                    'date' => $donation->paid_at->locale('fa')->diffForHumans(),
                ];
            });
            
        return response()->json([
            'success' => true,
            'data' => [
                'donations' => $donations
            ]
        ]);
    }
    
    /**
     * Create a new donation request
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|integer|min:10000',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'اطلاعات نامعتبر است',
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            // Create donation record
            $donation = Donation::create([
                'user_id' => Auth::check() ? Auth::id() : null,
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'amount' => $request->input('amount'),
                'reference_id' => ZarinpalService::generateReferenceId(),
                'status' => 'pending',
                'description' => 'حمایت از اذکار نور',
            ]);
            
            // Request payment from Zarinpal
            $result = $this->zarinpalService->request($donation);
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'redirect_url' => $result['redirect_url'],
                ]);
            }
            
            // Payment request failed
            $donation->update(['status' => 'failed']);
            
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 400);
            
        } catch (Exception $e) {
            Log::error('Failed to create donation', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'خطا در ایجاد درخواست پرداخت. لطفا مجددا تلاش کنید.',
            ], 500);
        }
    }
    
    /**
     * Verify a payment from Zarinpal
     * 
     * @param Request $request
     * @return mixed
     */
    public function verify(Request $request)
    {
        $authority = $request->input('Authority');
        $status = $request->input('Status');
        
        // Handle canceled payments
        if ($status !== 'OK' || empty($authority)) {
            return redirect(config('app.frontend_url') . '/donation/failed?message=' . urlencode('پرداخت توسط کاربر لغو شد'));
        }
        
        // Find the donation by transaction_id (authority)
        $donation = Donation::where('transaction_id', $authority)
                            ->where('status', 'pending')
                            ->first();
        
        if (!$donation) {
            Log::error('Donation not found for verification', [
                'authority' => $authority,
            ]);
            
            return redirect(config('app.frontend_url') . '/donation/failed?message=' . urlencode('تراکنش قابل شناسایی نیست'));
        }
        
        // Verify the payment
        $result = $this->zarinpalService->verify($authority, $donation->amount);
        
        if ($result['success']) {
            // Update the donation record
            $donation->update([
                'status' => 'completed',
                'paid_at' => Carbon::now(),
                'card_pan' => $result['card_pan'] ?? null,
            ]);
            
            return redirect(config('app.frontend_url') . '/donation/success?reference=' . $donation->reference_id);
        }
        
        // Payment verification failed
        $donation->update(['status' => 'failed']);
        
        return redirect(config('app.frontend_url') . '/donation/failed?message=' . urlencode($result['message']));
    }
}
