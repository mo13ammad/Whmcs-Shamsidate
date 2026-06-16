<?php

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

require_once __DIR__ . '/zarinpal.php';

function zarinpal_sandbox_MetaData(): array
{
    return [
        'DisplayName' => 'Zarinpal Sandbox',
        'APIVersion' => '1.1',
        'DisableLocalCredtCardInput' => true,
        'TokenisedStorage' => false,
    ];
}

function zarinpal_sandbox_config(): array
{
    $config = zarinpal_config();
    $config['FriendlyName']['Value'] = 'Zarinpal Sandbox';
    unset($config['sandbox']);
    $config['merchantId']['Description'] = 'برای sandbox زرین‌پال می‌توانید یک UUID دلخواه وارد کنید.';

    return $config;
}

function zarinpal_sandbox_link(array $params): string
{
    $params['sandbox'] = 'on';
    $params['_callbackModule'] = 'zarinpal_sandbox';

    return zarinpal_link($params);
}
