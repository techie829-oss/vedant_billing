<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CashfreeGstService
{
    protected $clientId;
    protected $clientSecret;
    protected $baseUrl;

    public function __construct()
    {
        $this->clientId = env('CASHFREE_CLIENT_ID');
        $this->clientSecret = env('CASHFREE_CLIENT_SECRET');
        $this->baseUrl = env('CASHFREE_ENV') === 'production'
            ? 'https://api.cashfree.com/verification/gstin'
            : 'https://sandbox.cashfree.com/verification/gstin';
    }

    /**
     * Verify GSTIN using Cashfree API
     * 
     * @param string $gstin
     * @param string $businessName (Optional)
     * @return array
     */
    public function verify($gstin, $businessName = 'Business Name')
    {
        try {
            $response = Http::withHeaders([
                'x-client-id' => $this->clientId,
                'x-client-secret' => $this->clientSecret,
                'x-api-version' => '2023-08-01', // Recommended version
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl, [
                        'GSTIN' => $gstin,
                        'business_name' => $businessName
                    ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            Log::error('Cashfree GST Verification Failed', [
                'gstin' => $gstin,
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [
                'success' => false,
                'error' => $response->json()['message'] ?? 'Verification failed',
                'details' => $response->json()
            ];

        } catch (\Exception $e) {
            Log::error('Cashfree GST Service Error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => 'Service connection error'
            ];
        }
    }
}
