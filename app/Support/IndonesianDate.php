<?php

namespace App\Support;

use Carbon\CarbonInterface;

final class IndonesianDate
{
    private const MONTHS = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
    ];

    public static function format(?CarbonInterface $date, bool $withTime = false): string
    {
        if ($date === null) {
            return '—';
        }

        $formatted = $date->format('j').' '.self::MONTHS[(int) $date->format('n')].' '.$date->format('Y');

        return $withTime ? $formatted.', '.$date->format('H:i') : $formatted;
    }
}
