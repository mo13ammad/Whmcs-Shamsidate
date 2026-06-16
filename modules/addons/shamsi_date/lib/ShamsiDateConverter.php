<?php

namespace ShamsiDate;

use DateTimeInterface;
use Exception;

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

class ShamsiDateConverter
{
    private const JALALI_MONTHS = [
        1 => 'فروردین',
        2 => 'اردیبهشت',
        3 => 'خرداد',
        4 => 'تیر',
        5 => 'مرداد',
        6 => 'شهریور',
        7 => 'مهر',
        8 => 'آبان',
        9 => 'آذر',
        10 => 'دی',
        11 => 'بهمن',
        12 => 'اسفند',
    ];

    private const JALALI_WEEKDAYS = [
        0 => 'یکشنبه',
        1 => 'دوشنبه',
        2 => 'سه‌شنبه',
        3 => 'چهارشنبه',
        4 => 'پنجشنبه',
        5 => 'جمعه',
        6 => 'شنبه',
    ];

    private const PERSIAN_DIGITS = ['0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹'];
    private const ARABIC_DIGITS = ['0' => '٠', '1' => '١', '2' => '٢', '3' => '٣', '4' => '٤', '5' => '٥', '6' => '٦', '7' => '٧', '8' => '٨', '9' => '٩'];

    public static function now(string $format = 'Y/m/d', string $digitMode = 'persian', bool $includeTime = false): string
    {
        return self::format(new \DateTimeImmutable('now'), $format, $digitMode, $includeTime);
    }

    public static function format($date, string $format = 'Y/m/d', string $digitMode = 'persian', bool $includeTime = false): string
    {
        if (!$date instanceof DateTimeInterface) {
            $date = self::parseDate($date);
        }

        if (!$date instanceof DateTimeInterface) {
            return '';
        }

        [$jy, $jm, $jd] = self::gregorianToJalali((int) $date->format('Y'), (int) $date->format('n'), (int) $date->format('j'));

        $replacements = [
            'Y' => sprintf('%04d', $jy),
            'y' => substr((string) $jy, -2),
            'm' => sprintf('%02d', $jm),
            'n' => (string) $jm,
            'd' => sprintf('%02d', $jd),
            'j' => (string) $jd,
            'F' => self::JALALI_MONTHS[$jm],
            'M' => mb_substr(self::JALALI_MONTHS[$jm], 0, 3, 'UTF-8'),
            'l' => self::JALALI_WEEKDAYS[(int) $date->format('w')],
            'D' => mb_substr(self::JALALI_WEEKDAYS[(int) $date->format('w')], 0, 3, 'UTF-8'),
            'H' => $date->format('H'),
            'i' => $date->format('i'),
            's' => $date->format('s'),
        ];

        $output = preg_replace_callback('/\\\\?.|./u', static function ($matches) use ($replacements) {
            $token = $matches[0];

            if (strlen($token) > 1 && $token[0] === '\\') {
                return substr($token, 1);
            }

            return $replacements[$token] ?? $token;
        }, $format);

        if ($includeTime && !preg_match('/[His]/', $format)) {
            $output .= ' ' . $date->format('H:i');
        }

        return self::convertDigits($output, $digitMode);
    }

    public static function gregorianToJalali(int $gy, int $gm, int $gd): array
    {
        $gDaysInMonth = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        $jDaysInMonth = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29];

        $gy -= 1600;
        $gm -= 1;
        $gd -= 1;

        $gDayNo = 365 * $gy + intdiv($gy + 3, 4) - intdiv($gy + 99, 100) + intdiv($gy + 399, 400);

        for ($i = 0; $i < $gm; $i++) {
            $gDayNo += $gDaysInMonth[$i];
        }

        if ($gm > 1 && (($gy % 4 === 0 && $gy % 100 !== 0) || ($gy % 400 === 0))) {
            $gDayNo++;
        }

        $gDayNo += $gd;
        $jDayNo = $gDayNo - 79;

        $jNp = intdiv($jDayNo, 12053);
        $jDayNo %= 12053;

        $jy = 979 + 33 * $jNp + 4 * intdiv($jDayNo, 1461);
        $jDayNo %= 1461;

        if ($jDayNo >= 366) {
            $jy += intdiv($jDayNo - 1, 365);
            $jDayNo = ($jDayNo - 1) % 365;
        }

        for ($i = 0; $i < 11 && $jDayNo >= $jDaysInMonth[$i]; $i++) {
            $jDayNo -= $jDaysInMonth[$i];
        }

        return [$jy, $i + 1, $jDayNo + 1];
    }

    public static function convertDigits(string $value, string $digitMode = 'persian'): string
    {
        if ($digitMode === 'english') {
            return strtr($value, array_flip(self::PERSIAN_DIGITS) + array_flip(self::ARABIC_DIGITS));
        }

        if ($digitMode === 'arabic') {
            return strtr($value, self::ARABIC_DIGITS);
        }

        return strtr($value, self::PERSIAN_DIGITS);
    }

    private static function parseDate($date): ?DateTimeInterface
    {
        if (!is_string($date) || trim($date) === '' || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
            return null;
        }

        try {
            return new \DateTimeImmutable($date);
        } catch (Exception $exception) {
            return null;
        }
    }
}
