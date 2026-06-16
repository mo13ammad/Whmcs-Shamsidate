<?php

namespace ShamsiDate;

use WHMCS\Database\Capsule;

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

class Settings
{
    public static function all(): array
    {
        $defaults = [
            'enableClientArea' => true,
            'enableAdminArea' => true,
            'convertTextNodes' => true,
            'convertInputs' => false,
            'digitMode' => 'persian',
            'dateFormat' => 'Y/m/d',
            'includeTime' => true,
            'observeAjax' => true,
        ];

        if (!class_exists(Capsule::class)) {
            return $defaults;
        }

        try {
            $rows = Capsule::table('tbladdonmodules')
                ->where('module', 'shamsi_date')
                ->pluck('value', 'setting');
        } catch (\Throwable $exception) {
            return $defaults;
        }

        $settings = is_array($rows) ? $rows : $rows->all();

        return [
            'enableClientArea' => self::bool($settings['enableClientArea'] ?? $defaults['enableClientArea']),
            'enableAdminArea' => self::bool($settings['enableAdminArea'] ?? $defaults['enableAdminArea']),
            'convertTextNodes' => self::bool($settings['convertTextNodes'] ?? $defaults['convertTextNodes']),
            'convertInputs' => self::bool($settings['convertInputs'] ?? $defaults['convertInputs']),
            'digitMode' => self::choice($settings['digitMode'] ?? $defaults['digitMode'], ['persian', 'english', 'arabic'], 'persian'),
            'dateFormat' => trim((string) ($settings['dateFormat'] ?? $defaults['dateFormat'])) ?: 'Y/m/d',
            'includeTime' => self::bool($settings['includeTime'] ?? $defaults['includeTime']),
            'observeAjax' => self::bool($settings['observeAjax'] ?? $defaults['observeAjax']),
        ];
    }

    private static function bool($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower((string) $value), ['1', 'yes', 'on', 'true'], true);
    }

    private static function choice(string $value, array $allowed, string $fallback): string
    {
        return in_array($value, $allowed, true) ? $value : $fallback;
    }
}
