<?php

namespace App\Enums;

enum ExpenseStatus: string
{
    case Pending = 'pendiente';
    case Approved = 'aprobado';
    case Rejected = 'rechazado';
    case ChangesRequested = 'modificacion_solicitada';
    case Cancelled = 'anulado';
    case Paid = 'pagado';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente',
            self::Approved => 'Aprobado',
            self::Rejected => 'Rechazado',
            self::ChangesRequested => 'Modificación solicitada',
            self::Cancelled => 'Anulado',
            self::Paid => 'Pagado',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Pending => 'kx-badge kx-badge-amber',
            self::Approved => 'kx-badge kx-badge-teal',
            self::Rejected => 'kx-badge kx-badge-red',
            self::ChangesRequested => 'kx-badge kx-badge-blue',
            self::Cancelled => 'kx-badge kx-badge-gray',
            self::Paid => 'kx-badge kx-badge-green',
        };
    }

    /** Flujo estricto de estados (RN-06). */
    public function next(): array
    {
        return match ($this) {
            self::Pending => [self::Approved, self::Rejected, self::ChangesRequested, self::Cancelled],
            self::ChangesRequested => [self::Pending],
            self::Approved => [self::Paid],
            default => [], // Rechazado, Anulado y Pagado son finales (RN-08)
        };
    }

    public function canTransitionTo(self $to): bool
    {
        return in_array($to, $this->next(), true);
    }
}
