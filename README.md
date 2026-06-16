# WHMCS Shamsi Date

افزونه شمسی‌ساز برای WHMCS 9 به بالا. این افزونه تاریخ‌های میلادی قابل تشخیص را در ناحیه کاربری و پنل مدیریت به تاریخ جلالی تبدیل می‌کند، بدون تغییر دیتابیس یا فایل‌های اصلی WHMCS.

## نصب

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
