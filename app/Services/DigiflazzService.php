<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class DigiflazzService
{
    protected $apiUrl;
    protected $username;
    protected $apiKey;

    public function __construct()
    {
        $this->apiUrl = config('services.digiflazz.url');
        $this->username = config('services.digiflazz.username');
        $this->apiKey = config('services.digiflazz.api_key');
    }

    public function getProducts()
    {
        $response = Http::post($this->apiUrl . '/price-list', [
            'cmd' => 'prepaid',
            'username' => $this->username,
            'sign' => $this->generateSignature()
        ]);

        if ($response->successful()) {
            return $response->json()['data'];
        }

        return [];
    }

    public function topup($data)
    {
        $response = Http::post($this->apiUrl . '/transaction', [
            'username' => $this->username,
            'buyer_sku_code' => $data['product_id'],
            'customer_no' => $data['customer_phone'],
            'ref_id' => $data['order_id'],
            'sign' => $this->generateSignature()
        ]);

        return $response->json();
    }

    public function checkStatus($refId)
    {
        $response = Http::post($this->apiUrl . '/transaction-status', [
            'username' => $this->username,
            'ref_id' => $refId,
            'sign' => $this->generateSignature()
        ]);

        return $response->json();
    }

    public function deposit($amount)
    {
        $response = Http::post($this->apiUrl . '/deposit', [
            'username' => $this->username,
            'amount' => $amount,
            'sign' => $this->generateSignature()
        ]);

        return $response->json();
    }

    public function getBalance()
    {
        $response = Http::post($this->apiUrl . '/balance', [
            'username' => $this->username,
            'sign' => $this->generateSignature()
        ]);

        return $response->json();
    }

    private function generateSignature()
    {
        return md5($this->username . $this->apiKey . 'pricelist');
    }
}