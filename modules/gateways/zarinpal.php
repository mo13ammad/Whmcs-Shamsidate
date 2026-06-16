<?php

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

require_once __DIR__ . '/zarinpal/lib/ZarinpalClient.php';

use WHMCSZarinpal\ZarinpalClient;

if (defined('ZARINPAL_GATEWAY_FUNCTIONS_LOADED')) {
    return;
}

define('ZARINPAL_GATEWAY_FUNCTIONS_LOADED', true);

if (!function_exists('zarinpal_MetaData')) {
    function zarinpal_MetaData(): array
    {
        return [
            'DisplayName' => 'Zarinpal',
            'APIVersion' => '1.1',
            'DisableLocalCredtCardInput' => true,
            'TokenisedStorage' => false,
        ];
    }
}

if (!function_exists('zarinpal_config')) {
    function zarinpal_config(): array
    {
        return [
            'FriendlyName' => [
                'Type' => 'System',
                'Value' => 'Zarinpal',
            ],
            'merchantId' => [
                'FriendlyName' => 'Merchant ID',
                'Type' => 'text',
                'Size' => '40',
                'Default' => '',
                'Description' => 'کد ۳۶ کاراکتری پذیرنده زرین‌پال. در sandbox می‌تواند UUID دلخواه باشد.',
            ],
            'sandbox' => [
                'FriendlyName' => 'Sandbox Mode',
                'Type' => 'yesno',
                'Description' => 'برای تست، درخواست‌ها به sandbox.zarinpal.com ارسال می‌شوند.',
            ],
            'currency' => [
                'FriendlyName' => 'Zarinpal Currency',
                'Type' => 'dropdown',
                'Options' => 'IRT,IRR',
                'Default' => 'IRT',
                'Description' => 'IRT تومان، IRR ریال. واحد WHMCS شما باید با این مقدار هماهنگ باشد.',
            ],
            'amountMultiplier' => [
                'FriendlyName' => 'Amount Multiplier',
                'Type' => 'text',
                'Size' => '8',
                'Default' => '1',
                'Description' => 'برای تبدیل واحد WHMCS به واحد ارسالی زرین‌پال. معمولا 1؛ اگر WHMCS تومان است و IRR می‌فرستید 10.',
            ],
            'description' => [
                'FriendlyName' => 'Payment Description',
                'Type' => 'text',
                'Size' => '60',
                'Default' => 'پرداخت فاکتور #{invoiceid}',
                'Description' => 'متغیرهای قابل استفاده: {invoiceid}, {companyname}, {clientname}',
            ],
            'autoVerify' => [
                'FriendlyName' => 'Force Auto Verify',
                'Type' => 'yesno',
                'Description' => 'metadata.auto_verify=true ارسال شود. اگر مطمئن نیستید خاموش بگذارید.',
            ],
            'buttonText' => [
                'FriendlyName' => 'Pay Button Text',
                'Type' => 'text',
                'Size' => '30',
                'Default' => 'پرداخت با زرین‌پال',
            ],
        ];
    }
}

if (!function_exists('zarinpal_link')) {
    function zarinpal_link(array $params): string
    {
        $merchantId = trim((string) ($params['merchantId'] ?? ''));

        if ($merchantId === '') {
            return zarinpal_error('Merchant ID تنظیم نشده است.');
        }

        $amount = zarinpal_gateway_amount((float) $params['amount'], $params);
        if ($amount <= 0) {
            return zarinpal_error('مبلغ فاکتور برای ارسال به زرین‌پال معتبر نیست.');
        }

        $invoiceId = (int) $params['invoiceid'];
        $currency = zarinpal_gateway_currency($params);
        $callbackUrl = zarinpal_callback_url($params, $invoiceId, $amount, $currency);
        $client = new ZarinpalClient($merchantId, zarinpal_is_sandbox($params));

        $metadata = [
            'order_id' => (string) $invoiceId,
        ];

        if (!empty($params['clientdetails']['email'])) {
            $metadata['email'] = (string) $params['clientdetails']['email'];
        }

        if (!empty($params['clientdetails']['phonenumber'])) {
            $metadata['mobile'] = (string) $params['clientdetails']['phonenumber'];
        }

        if (!empty($params['autoVerify']) && $params['autoVerify'] === 'on') {
            $metadata['auto_verify'] = true;
        }

        $response = $client->request([
            'amount' => $amount,
            'currency' => $currency,
            'callback_url' => $callbackUrl,
            'description' => zarinpal_description($params),
            'metadata' => $metadata,
        ]);

        logTransaction('zarinpal', [
            'action' => 'request',
            'invoiceid' => $invoiceId,
            'amount' => $amount,
            'currency' => $currency,
            'sandbox' => zarinpal_is_sandbox($params) ? 'yes' : 'no',
            'response' => $response['decoded'] ?? $response['raw'],
            'error' => $response['error'],
            'http_code' => $response['http_code'],
        ], $response['success'] ? 'Request Sent' : 'Request Failed');

        $data = $response['decoded']['data'] ?? [];
        $code = (int) ($data['code'] ?? 0);
        $authority = (string) ($data['authority'] ?? '');

        if (!$response['success'] || $code !== 100 || $authority === '') {
            $message = zarinpal_response_message($response['decoded'] ?? null) ?: 'خطا در اتصال به زرین‌پال.';

            return zarinpal_error($message);
        }

        $payUrl = $client->startPayUrl($authority);
        $buttonText = htmlspecialchars((string) ($params['buttonText'] ?: 'پرداخت با زرین‌پال'), ENT_QUOTES, 'UTF-8');

        return '<form method="get" action="' . htmlspecialchars($payUrl, ENT_QUOTES, 'UTF-8') . '">'
            . '<button type="submit" class="btn btn-primary">' . $buttonText . '</button>'
            . '</form>';
    }
}

if (!function_exists('zarinpal_gateway_amount')) {
    function zarinpal_gateway_amount(float $amount, array $params): int
    {
        $multiplier = (float) ($params['amountMultiplier'] ?? 1);
        if ($multiplier <= 0) {
            $multiplier = 1;
        }

        return (int) round($amount * $multiplier);
    }
}

if (!function_exists('zarinpal_gateway_currency')) {
    function zarinpal_gateway_currency(array $params): string
    {
        $currency = strtoupper((string) ($params['currency'] ?? 'IRT'));

        return in_array($currency, ['IRT', 'IRR'], true) ? $currency : 'IRT';
    }
}

if (!function_exists('zarinpal_is_sandbox')) {
    function zarinpal_is_sandbox(array $params): bool
    {
        return !empty($params['sandbox']) && $params['sandbox'] === 'on';
    }
}

if (!function_exists('zarinpal_callback_url')) {
    function zarinpal_callback_url(array $params, int $invoiceId, int $amount, string $currency): string
    {
        $baseUrl = rtrim((string) $params['systemurl'], '/');
        $mode = zarinpal_is_sandbox($params) ? 'sandbox' : 'live';
        $callbackModule = preg_replace('/[^a-zA-Z0-9_]/', '', (string) ($params['_callbackModule'] ?? 'zarinpal'));
        if ($callbackModule === '') {
            $callbackModule = 'zarinpal';
        }
        $signature = zarinpal_signature($params, $invoiceId, $amount, $currency, $mode);

        return $baseUrl . '/modules/gateways/callback/' . $callbackModule . '.php?'
            . http_build_query([
                'invoiceid' => $invoiceId,
                'amount' => $amount,
                'currency' => $currency,
                'mode' => $mode,
                'sig' => $signature,
            ]);
    }
}

if (!function_exists('zarinpal_signature')) {
    function zarinpal_signature(array $params, int $invoiceId, int $amount, string $currency, string $mode): string
    {
        $merchantId = trim((string) ($params['merchantId'] ?? ''));
        $payload = implode('|', [$invoiceId, $amount, $currency, $mode]);

        return hash_hmac('sha256', $payload, $merchantId);
    }
}

if (!function_exists('zarinpal_description')) {
    function zarinpal_description(array $params): string
    {
        $clientName = trim((string) (($params['clientdetails']['firstname'] ?? '') . ' ' . ($params['clientdetails']['lastname'] ?? '')));
        $description = (string) ($params['description'] ?: 'پرداخت فاکتور #{invoiceid}');

        return strtr($description, [
            '{invoiceid}' => (string) $params['invoiceid'],
            '{companyname}' => (string) ($params['companyname'] ?? ''),
            '{clientname}' => $clientName,
        ]);
    }
}

if (!function_exists('zarinpal_response_message')) {
    function zarinpal_response_message($decoded): string
    {
        if (!is_array($decoded)) {
            return '';
        }

        if (!empty($decoded['data']['message'])) {
            return (string) $decoded['data']['message'];
        }

        if (!empty($decoded['errors']['message'])) {
            return (string) $decoded['errors']['message'];
        }

        if (!empty($decoded['errors']) && is_array($decoded['errors'])) {
            return json_encode($decoded['errors'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';
        }

        return '';
    }
}

if (!function_exists('zarinpal_error')) {
    function zarinpal_error(string $message): string
    {
        return '<div class="alert alert-danger" dir="rtl">' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</div>';
    }
}
