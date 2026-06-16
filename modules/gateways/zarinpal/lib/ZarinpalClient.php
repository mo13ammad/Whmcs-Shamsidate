<?php

namespace WHMCSZarinpal;

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

class ZarinpalClient
{
    private const LIVE_BASE_URL = 'https://payment.zarinpal.com';
    private const SANDBOX_BASE_URL = 'https://sandbox.zarinpal.com';

    private string $merchantId;
    private bool $sandbox;

    public function __construct(string $merchantId, bool $sandbox = false)
    {
        $this->merchantId = trim($merchantId);
        $this->sandbox = $sandbox;
    }

    public function request(array $payload): array
    {
        $payload['merchant_id'] = $this->merchantId;

        return $this->post('/pg/v4/payment/request.json', $payload);
    }

    public function verify(int $amount, string $authority): array
    {
        return $this->post('/pg/v4/payment/verify.json', [
            'merchant_id' => $this->merchantId,
            'amount' => $amount,
            'authority' => $authority,
        ]);
    }

    public function startPayUrl(string $authority): string
    {
        return $this->baseUrl() . '/pg/StartPay/' . rawurlencode($authority);
    }

    private function post(string $path, array $payload): array
    {
        if (!function_exists('curl_init')) {
            return [
                'success' => false,
                'http_code' => 0,
                'raw' => '',
                'decoded' => null,
                'error' => 'PHP cURL extension is not available.',
            ];
        }

        $ch = curl_init($this->baseUrl() . $path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
            ],
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT => 45,
        ]);

        $raw = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decoded = is_string($raw) ? json_decode($raw, true) : null;

        return [
            'success' => $error === '' && $httpCode >= 200 && $httpCode < 300 && is_array($decoded),
            'http_code' => $httpCode,
            'raw' => is_string($raw) ? $raw : '',
            'decoded' => $decoded,
            'error' => $error,
        ];
    }

    private function baseUrl(): string
    {
        return $this->sandbox ? self::SANDBOX_BASE_URL : self::LIVE_BASE_URL;
    }
}
