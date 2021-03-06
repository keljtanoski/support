<?php

namespace Helldar\Support\Helpers;

final class Digit
{
    /**
     * Calculating the factorial of a number.
     *
     * @param  int  $n
     *
     * @return int
     */
    public function factorial(int $n = 0): int
    {
        if ($n === 0) {
            return 1;
        }

        return $n * $this->factorial($n - 1);
    }

    /**
     * Converts a number into a short version.
     * eg: 1000 >> 1K.
     *
     * @param  float  $number
     * @param  int  $precision
     *
     * @return string
     */
    public function toShort(float $number, int $precision = 1): string
    {
        $length = strlen((string) ((int) $number));
        $length = ceil($length / 3) * 3 + 1;

        $suffix = $this->suffix($length);
        $value  = $this->rounded($number, $length, $precision);

        return $value . $suffix;
    }

    /**
     * Create short unique identifier from number.
     * Actually using in short URL.
     *
     * @param  int  $number
     * @param  string  $chars
     *
     * @return string
     */
    public function shortKey(int $number, string $chars = 'abcdefghijklmnopqrstuvwxyz'): string
    {
        $length = strlen($chars);
        $mod    = $number % $length;

        if ($number - $mod === 0) {
            return substr($chars, $number, 1);
        }

        $result = '';

        while ($mod > 0 || $number > 0) {
            $result = substr($chars, $mod, 1) . $result;
            $number = ($number - $mod) / $length;
            $mod    = $number % $length;
        }

        return $result;
    }

    /**
     * Format a number with grouped with divider.
     *
     * @param  float  $number
     * @param  int  $length
     * @param  int  $precision
     *
     * @return float
     */
    public function rounded(float $number, int $length = 4, int $precision = 1): float
    {
        $divided = (float) bcpow(10, $length - 4, 2);

        return round($number / $divided, $precision);
    }

    protected function suffix(int $length = 0): string
    {
        $available = [
            4  => '',
            7  => 'K',
            10 => 'M',
            13 => 'B',
            16 => 'T+',
        ];

        return $available[$length] ?? end($available);
    }
}
