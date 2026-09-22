<?php

namespace App\Support;

class Money
{
    /** "Tk 1,250" or "Tk 1,250.50" — decimals only when there are any. */
    public static function tk(float|int|string|null $amount): string
    {
        $amount = round((float) $amount, 2);

        return 'Tk ' . number_format($amount, fmod($amount, 1.0) == 0.0 ? 0 : 2, '.', ',');
    }
}
