<?php

namespace App\Support;

use Carbon\CarbonInterface;

class Dates
{
    public static function now(): CarbonInterface
    {
        return now(config('kendex.timezone'));
    }

    public static function today(): string
    {
        return static::now()->toDateString();
    }

    /** Período presupuestario YYYY-MM (RN-09). */
    public static function period(?CarbonInterface $date = null): string
    {
        return ($date ?? static::now())->format('Y-m');
    }

    public static function display(?CarbonInterface $date, string $format = 'd/m/Y h:i A'): string
    {
        return $date ? $date->copy()->timezone(config('kendex.timezone'))->format($format) : '—';
    }

    /** Fecha sin hora (expense_date, paid_at): no se convierte de zona horaria para no correr el día. */
    public static function date(?CarbonInterface $date): string
    {
        return $date ? $date->format('d/m/Y') : '—';
    }
}
