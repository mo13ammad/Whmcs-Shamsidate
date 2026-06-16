# WHMCS Shamsi Date

افزونه شمسی‌ساز و درگاه زرین‌پال برای WHMCS 9 به بالا. این پروژه بدون تغییر دیتابیس یا فایل‌های اصلی WHMCS نصب می‌شود و شامل دو بخش مستقل است:

- شمسی‌سازی تاریخ‌های WHMCS در ناحیه کاربری و پنل مدیریت
- درگاه پرداخت زرین‌پال با پشتیبانی از live و sandbox کنار هم

## نصب

1. فایل release را از بخش GitHub Releases دانلود و extract کنید.
2. پوشه `modules` را داخل ریشه WHMCS کپی کنید.
3. در WHMCS به `System Settings > Addon Modules` بروید.
4. ماژول `شمسی‌ساز کامل WHMCS` را فعال کنید.
5. در صورت نیاز به درگاه، از مسیر `System Settings > Payment Gateways` گزینه‌های `Zarinpal` و/یا `Zarinpal Sandbox` را فعال کنید.

## نصب فقط شمسی‌ساز

1. پوشه `modules/addons/shamsi_date` را داخل ریشه WHMCS کپی کنید.
2. در WHMCS به `System Settings > Addon Modules` بروید.
3. ماژول `شمسی‌ساز کامل WHMCS` را فعال کنید.
4. تنظیمات مورد نیاز مثل فعال بودن در admin/client، نوع ارقام و فرمت تاریخ را همان‌جا تغییر دهید.

## امکانات

- سازگار با ساختار addon module و hooks در WHMCS 9+
- تبدیل تاریخ‌های `YYYY-MM-DD`، `YYYY/MM/DD`، `YYYY.MM.DD`
- تبدیل تاریخ‌های متنی انگلیسی مثل `16 Jun 2026` و `Jun 16, 2026`
- حفظ ساعت در صورت وجود، با امکان خاموش کردن
- پشتیبانی از ارقام فارسی، انگلیسی و عربی
- پشتیبانی از محتوای Ajax با `MutationObserver`
- عدم تبدیل فرم‌ها به‌صورت پیش‌فرض برای جلوگیری از اختلال در فیلترها و date pickerها

## فایل‌های شمسی‌ساز

- `modules/addons/shamsi_date/shamsi_date.php`
- `modules/addons/shamsi_date/hooks.php`
- `modules/addons/shamsi_date/lib/ShamsiDateConverter.php`
- `modules/addons/shamsi_date/assets/shamsi-date.js`

## جلوگیری از تبدیل یک بخش

کلاس زیر را به هر المانی اضافه کنید:

```html
<div class="no-shamsi-date">2026-06-16</div>
```

## متغیر Smarty

در ناحیه کاربری، متغیر زیر در قالب‌ها در دسترس است:

```smarty
{$shamsiToday}
```

## درگاه زرین‌پال

این پروژه درگاه پرداخت زرین‌پال را هم دارد:

- `modules/gateways/zarinpal.php`
- `modules/gateways/zarinpal_sandbox.php`
- `modules/gateways/callback/zarinpal.php`
- `modules/gateways/callback/zarinpal_sandbox.php`
- `modules/gateways/zarinpal/lib/ZarinpalClient.php`

برای فعال‌سازی، فایل‌ها را در ریشه WHMCS قرار دهید و از مسیر `System Settings > Payment Gateways` درگاه `Zarinpal` را فعال کنید.

اگر می‌خواهید درگاه تست و درگاه اصلی کنار هم باشند، هر دو گزینه `Zarinpal` و `Zarinpal Sandbox` را فعال کنید. `Zarinpal Sandbox` همیشه به `sandbox.zarinpal.com` وصل می‌شود؛ `Zarinpal` هم خودش گزینه `Sandbox Mode` دارد.

تنظیمات مهم:

- `Merchant ID`: کد پذیرنده زرین‌پال
- `Sandbox Mode`: ارسال درخواست‌ها به `sandbox.zarinpal.com`
- `Zarinpal Currency`: مقدار `IRT` برای تومان یا `IRR` برای ریال
- `Amount Multiplier`: ضریب تبدیل مبلغ WHMCS به واحد ارسالی زرین‌پال

اگر واحد پول WHMCS شما تومان است، مقدار پیش‌فرض `IRT` و ضریب `1` مناسب است. اگر می‌خواهید به زرین‌پال ریال بفرستید و WHMCS شما تومان است، `IRR` و ضریب `10` بگذارید.

## Callbackهای زرین‌پال

Callbackها به‌صورت خودکار ساخته می‌شوند و نیازی به تنظیم دستی ندارند:

- Live: `modules/gateways/callback/zarinpal.php`
- Sandbox: `modules/gateways/callback/zarinpal_sandbox.php`

برای امنیت، اطلاعات مهم callback مثل شماره فاکتور، مبلغ، واحد پول و حالت live/sandbox با HMAC امضا می‌شوند.

## نیازمندی‌ها

- WHMCS 9 یا بالاتر
- PHP سازگار با WHMCS 9
- فعال بودن افزونه PHP cURL برای درگاه زرین‌پال
- دسترسی HTTPS عمومی برای callback درگاه پرداخت

## Release

نسخه `v1.1.0` شامل شمسی‌ساز کامل WHMCS و درگاه زرین‌پال live/sandbox است.
