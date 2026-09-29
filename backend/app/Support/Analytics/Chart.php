<?php

namespace App\Support\Analytics;

/** Geometry for the server-rendered SVG charts in the analytics panel (no JS chart library). */
class Chart
{
    public const W = 720;
    public const H = 220;
    private const PAD_TOP = 12;
    private const PAD_BOTTOM = 8;

    public static function max(array ...$series): int
    {
        $max = 0;
        foreach ($series as $values) {
            $max = max($max, ...($values ?: [0]));
        }
        return self::niceCeil($max);
    }

    /**
     * X position of point $i of $n, at the centre of its hover column.
     * Time runs right-to-left to match the RTL page (and the flex columns laid over the SVG).
     */
    public static function x(int $i, int $n): float
    {
        return round(self::W - ($i + 0.5) * self::W / max($n, 1), 2);
    }

    public static function y(float $value, int $max): float
    {
        $usable = self::H - self::PAD_TOP - self::PAD_BOTTOM;
        return round(self::H - self::PAD_BOTTOM - ($max ? $value / $max : 0) * $usable, 2);
    }

    public static function line(array $values, int $max): string
    {
        $n = count($values);
        $points = [];
        foreach (array_values($values) as $i => $v) {
            $points[] = self::x($i, $n).','.self::y($v, $max);
        }
        return 'M'.implode(' L', $points);
    }

    public static function area(array $values, int $max): string
    {
        $n = count($values);
        return self::line($values, $max).' L'.self::x($n - 1, $n).','.(self::H - self::PAD_BOTTOM).' L'.self::x(0, $n).','.(self::H - self::PAD_BOTTOM).' Z';
    }

    private static function niceCeil(int $value): int
    {
        if ($value <= 4) {
            return 4;
        }
        $magnitude = 10 ** (int) floor(log10($value));
        foreach ([1, 2, 2.5, 5, 10] as $step) {
            if ($value <= $step * $magnitude) {
                return (int) ($step * $magnitude);
            }
        }
        return $value;
    }
}
