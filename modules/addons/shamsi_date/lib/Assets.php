<?php

namespace ShamsiDate;

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

class Assets
{
    public static function render(array $settings, string $area): string
    {
        $config = [
            'area' => $area,
            'convertTextNodes' => (bool) $settings['convertTextNodes'],
            'convertInputs' => (bool) $settings['convertInputs'],
            'digitMode' => $settings['digitMode'],
            'dateFormat' => $settings['dateFormat'],
            'includeTime' => (bool) $settings['includeTime'],
            'observeAjax' => (bool) $settings['observeAjax'],
        ];

        $json = json_encode($config, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $base = rtrim(\App::getSystemURL(), '/');

        return "\n<link rel=\"stylesheet\" href=\"{$base}/modules/addons/shamsi_date/assets/shamsi-date.css\">\n"
            . "<script>window.ShamsiDateConfig={$json};</script>\n"
            . "<script src=\"{$base}/modules/addons/shamsi_date/assets/shamsi-date.js\" defer></script>\n";
    }
}
