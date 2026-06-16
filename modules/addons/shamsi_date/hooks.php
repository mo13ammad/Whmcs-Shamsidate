<?php

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

require_once __DIR__ . '/lib/Settings.php';
require_once __DIR__ . '/lib/Assets.php';
require_once __DIR__ . '/lib/ShamsiDateConverter.php';

use ShamsiDate\Assets;
use ShamsiDate\Settings;
use ShamsiDate\ShamsiDateConverter;

add_hook('ClientAreaHeadOutput', 1, static function () {
    $settings = Settings::all();

    if (!$settings['enableClientArea']) {
        return '';
    }

    return Assets::render($settings, 'client');
});

add_hook('AdminAreaHeadOutput', 1, static function () {
    $settings = Settings::all();

    if (!$settings['enableAdminArea']) {
        return '';
    }

    return Assets::render($settings, 'admin');
});

add_hook('ClientAreaPage', 1, static function () {
    $settings = Settings::all();

    return [
        'shamsiToday' => ShamsiDateConverter::now($settings['dateFormat'], $settings['digitMode'], $settings['includeTime']),
    ];
});

add_hook('FormatDateForClientAreaOutput', 1, static function (array $vars) {
    $settings = Settings::all();

    if (!$settings['enableClientArea'] || empty($vars['date'])) {
        return null;
    }

    return ShamsiDateConverter::format($vars['date'], $settings['dateFormat'], $settings['digitMode'], false);
});

add_hook('FormatDateTimeForClientAreaOutput', 1, static function (array $vars) {
    $settings = Settings::all();

    if (!$settings['enableClientArea'] || empty($vars['date'])) {
        return null;
    }

    return ShamsiDateConverter::format($vars['date'], $settings['dateFormat'], $settings['digitMode'], $settings['includeTime']);
});
