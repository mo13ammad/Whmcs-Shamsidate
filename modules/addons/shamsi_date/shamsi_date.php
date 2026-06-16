<?php

use ShamsiDate\ShamsiDateConverter;

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

require_once __DIR__ . '/lib/ShamsiDateConverter.php';

function shamsi_date_config(): array
{
    return [
        'name' => 'شمسی‌ساز کامل WHMCS',
        'description' => 'تبدیل تاریخ‌های میلادی WHMCS به تاریخ شمسی در ناحیه کاربری و مدیریت، سازگار با WHMCS 9 به بالا.',
        'version' => '1.0.0',
        'author' => 'WHMCS Plugin',
        'language' => 'farsi',
        'fields' => [
            'enableClientArea' => [
                'FriendlyName' => 'فعال در ناحیه کاربری',
                'Type' => 'yesno',
                'Description' => 'تاریخ‌های قابل تشخیص در صفحات کاربری به شمسی تبدیل شوند.',
                'Default' => 'on',
            ],
            'enableAdminArea' => [
                'FriendlyName' => 'فعال در پنل مدیریت',
                'Type' => 'yesno',
                'Description' => 'تاریخ‌های قابل تشخیص در پنل مدیریت به شمسی تبدیل شوند.',
                'Default' => 'on',
            ],
            'convertTextNodes' => [
                'FriendlyName' => 'تبدیل متن صفحه',
                'Type' => 'yesno',
                'Description' => 'متن‌های عادی شامل تاریخ میلادی تبدیل شوند.',
                'Default' => 'on',
            ],
            'convertInputs' => [
                'FriendlyName' => 'تبدیل مقدار inputها',
                'Type' => 'yesno',
                'Description' => 'برای جلوگیری از اختلال در فرم‌ها پیش‌فرض خاموش است.',
                'Default' => '',
            ],
            'digitMode' => [
                'FriendlyName' => 'نوع ارقام',
                'Type' => 'dropdown',
                'Options' => 'persian,english,arabic',
                'Default' => 'persian',
                'Description' => 'persian = ۱۲۳، english = 123، arabic = ١٢٣',
            ],
            'dateFormat' => [
                'FriendlyName' => 'فرمت تاریخ شمسی',
                'Type' => 'text',
                'Size' => '20',
                'Default' => 'Y/m/d',
                'Description' => 'مانند Y/m/d یا l d F Y',
            ],
            'includeTime' => [
                'FriendlyName' => 'نمایش ساعت',
                'Type' => 'yesno',
                'Description' => 'اگر تاریخ میلادی شامل ساعت باشد، ساعت حفظ شود.',
                'Default' => 'on',
            ],
            'observeAjax' => [
                'FriendlyName' => 'پشتیبانی از Ajax',
                'Type' => 'yesno',
                'Description' => 'تغییرات محتوای صفحه پس از بارگذاری نیز بررسی شود.',
                'Default' => 'on',
            ],
        ],
    ];
}

function shamsi_date_activate(): array
{
    return [
        'status' => 'success',
        'description' => 'افزونه شمسی‌ساز فعال شد. تنظیمات را از همین صفحه بررسی کنید.',
    ];
}

function shamsi_date_deactivate(): array
{
    return [
        'status' => 'success',
        'description' => 'افزونه شمسی‌ساز غیرفعال شد.',
    ];
}

function shamsi_date_output(array $vars): void
{
    $today = ShamsiDateConverter::now($vars['dateFormat'] ?? 'Y/m/d', $vars['digitMode'] ?? 'persian', !empty($vars['includeTime']));

    echo '<div class="shamsi-date-admin" dir="rtl" style="max-width:900px;line-height:1.9">';
    echo '<h2>شمسی‌ساز کامل WHMCS</h2>';
    echo '<p>افزونه فعال است. نمونه تاریخ امروز: <strong>' . htmlspecialchars($today, ENT_QUOTES, 'UTF-8') . '</strong></p>';
    echo '<p>برای تغییر رفتار افزونه به مسیر <strong>System Settings > Addon Modules</strong> بروید و تنظیمات همین ماژول را ویرایش کنید.</p>';
    echo '<h3>نکات مهم</h3>';
    echo '<ul>';
    echo '<li>تبدیل مقدار inputها به‌صورت پیش‌فرض خاموش است تا فرم‌های WHMCS، فیلترها و date pickerها خراب نشوند.</li>';
    echo '<li>تاریخ‌های ذخیره‌شده در دیتابیس تغییر نمی‌کنند؛ تبدیل فقط در خروجی نمایش انجام می‌شود.</li>';
    echo '<li>برای قالب‌های سفارشی، می‌توانید کلاس <code>no-shamsi-date</code> را روی بخشی بگذارید که نباید تبدیل شود.</li>';
    echo '</ul>';
    echo '</div>';
}
