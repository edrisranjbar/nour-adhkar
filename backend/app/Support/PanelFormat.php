<?php

namespace App\Support;

use Carbon\Carbon;

class PanelFormat
{
    public static function digits($value): string
    {
        return strtr((string) $value, ['0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹']);
    }

    public static function number($value): string
    {
        return self::digits(number_format((int) $value));
    }

    // Solar Hijri date in Tehran time when ext-intl is available, otherwise Gregorian.
    public static function date($value): string
    {
        if (!$value) {
            return '—';
        }
        $date = Carbon::parse($value)->setTimezone('Asia/Tehran');
        if (class_exists(\IntlDateFormatter::class)) {
            $formatter = new \IntlDateFormatter('fa_IR@calendar=persian', \IntlDateFormatter::NONE, \IntlDateFormatter::NONE, 'Asia/Tehran', \IntlDateFormatter::TRADITIONAL, 'yyyy/MM/dd – HH:mm');
            $formatted = $formatter->format($date);
            if ($formatted !== false) {
                return self::digits($formatted);
            }
        }
        return self::digits($date->format('Y/m/d – H:i'));
    }

    // Short Solar Hijri day label (e.g. ۰۷/۰۵) for a Tehran Y-m-d key.
    public static function day(string $ymd): string
    {
        $date = Carbon::parse($ymd, 'Asia/Tehran');
        if (class_exists(\IntlDateFormatter::class)) {
            $formatter = new \IntlDateFormatter('fa_IR@calendar=persian', \IntlDateFormatter::NONE, \IntlDateFormatter::NONE, 'Asia/Tehran', \IntlDateFormatter::TRADITIONAL, 'd MMMM');
            $formatted = $formatter->format($date);
            if ($formatted !== false) {
                return self::digits($formatted);
            }
        }
        return self::digits($date->format('m/d'));
    }

    public static function decimal($value): string
    {
        return self::digits(str_replace('.', '٫', (string) $value));
    }

    /** Percentage change against the previous period, or null when there is nothing to compare. */
    public static function delta(?float $now, ?float $before): ?int
    {
        if ($before === null || $now === null || $before == 0) {
            return null;
        }
        return (int) round(($now - $before) * 100 / $before);
    }
}
