<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Transfer = 'transferencia';
    case Cash = 'efectivo';
    case Check = 'cheque';
    case Card = 'tarjeta_corporativa';

    public function label(): string
    {
        return match ($this) {
            self::Transfer => 'Transferencia',
            self::Cash => 'Efectivo',
            self::Check => 'Cheque',
            self::Card => 'Tarjeta corporativa',
        };
    }

    public function requiresReference(): bool
    {
        return $this !== self::Cash;
    }
}
