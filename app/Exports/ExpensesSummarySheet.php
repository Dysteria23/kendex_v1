<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class ExpensesSummarySheet implements FromArray, WithTitle, ShouldAutoSize
{
    public function __construct(protected Collection $rows) {}

    public function title(): string
    {
        return 'Resumen';
    }

    public function array(): array
    {
        $out = [['Total por departamento', 'Solicitudes', 'Monto (RD$)']];
        foreach ($this->rows->groupBy(fn ($e) => $e->department->name)->sortKeys() as $name => $g) {
            $out[] = [$name, $g->count(), (float) $g->sum('amount')];
        }

        $out[] = [];
        $out[] = ['Total por categoría', 'Solicitudes', 'Monto (RD$)'];
        foreach ($this->rows->groupBy(fn ($e) => $e->category->name)->sortKeys() as $name => $g) {
            $out[] = [$name, $g->count(), (float) $g->sum('amount')];
        }

        $out[] = [];
        $out[] = ['Total por estado', 'Solicitudes', 'Monto (RD$)'];
        foreach ($this->rows->groupBy(fn ($e) => $e->status->label())->sortKeys() as $name => $g) {
            $out[] = [$name, $g->count(), (float) $g->sum('amount')];
        }

        $out[] = [];
        $out[] = ['TOTAL GENERAL', $this->rows->count(), (float) $this->rows->sum('amount')];

        return $out;
    }
}
