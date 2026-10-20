<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'administrador';
    case Employee = 'empleado';
    case Supervisor = 'supervisor';
    case Finance = 'finanzas';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::Employee => 'Empleado',
            self::Supervisor => 'Supervisor',
            self::Finance => 'Finanzas',
        };
    }
}
