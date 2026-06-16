<?php

require_once __DIR__ . '/../../../init.php';

App::load_function('gateway');
App::load_function('invoice');

$gatewayModuleName = $zarinpalCallbackModuleName ?? basename(__FILE__, '.php');
$gatewayParams = getGatewayVariables($gatewayModuleName);

if (empty($gatewayParams['type'])) {
    die('Module Not Activated');
}

require_once __DIR__ . '/../zarinpal/lib/ZarinpalClient.php';
require_once __DIR__ . '/../zarinpal.php';

use WHMCSZarinpal\ZarinpalClient;

$invoiceId = (int) ($_GET['invoiceid'] ?? 0);
$authority = (string) ($_GET['Authority'] ?? '');
$status = strtoupper((string) ($_GET['Status'] ?? ''));
$amount = (int) ($_GET['amount'] ?? 0);
$currency = strtoupper((string) ($_GET['currency'] ?? 'IRT'));
$mode = strtolower((string) ($_GET['mode'] ?? 'live'));
$signature = (string) ($_GET['sig'] ?? '');
$success = false;
$transactionId = $authority;
$transactionStatus = 'Failed';
$verifyResponse = null;

$invoiceId = checkCbInvoiceID($invoiceId, $gatewayParams['name']);

if (!hash_equals(zarinpal_signature($gatewayParams, $invoiceId, $amount, $currency, $mode), $signature)) {
    $transactionStatus = 'Signature Verification Failure';
} elseif ($status !== 'OK') {
    $transactionStatus = 'Cancelled';
} elseif ($authority === '' || $amount <= 0 || !in_array($currency, ['IRT', 'IRR'], true)) {
    $transactionStatus = 'Invalid Callback Data';
} else {
    $client = new ZarinpalClient((string) $gatewayParams['merchantId'], $mode === 'sandbox');
    $verifyResponse = $client->verify($amount, $authority);
    $data = $verifyResponse['decoded']['data'] ?? [];
    $code = (int) ($data['code'] ?? 0);

    if ($verifyResponse['success'] && in_array($code, [100, 101], true)) {
        $transactionId = (string) ($data['ref_id'] ?? $authority);
        $success = true;
        $transactionStatus = $code === 100 ? 'Success' : 'Already Verified';
    } else {
        $transactionStatus = 'Verification Failed';
    }
}

logTransaction($gatewayParams['name'], [
    'invoiceid' => $invoiceId,
    'authority' => $authority,
    'status' => $status,
    'amount' => $amount,
    'currency' => $currency,
    'mode' => $mode,
    'verify_response' => $verifyResponse['decoded'] ?? $verifyResponse,
], $transactionStatus);

if ($success) {
    checkCbTransID($transactionId);
    addInvoicePayment(
        $invoiceId,
        $transactionId,
        '',
        0,
        $gatewayModuleName
    );
}

$returnUrl = rtrim((string) $gatewayParams['systemurl'], '/') . '/viewinvoice.php?id=' . $invoiceId;
header('Location: ' . $returnUrl);
exit;
