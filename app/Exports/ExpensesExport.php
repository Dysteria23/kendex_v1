<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/** Libro de Excel con dos hojas: Detalle y Resumen (RF-65). */
class ExpensesExport implements WithMultipleSheets
{
    public function __construct(protected Collection $rows) {}

    public function sheets(): array
    {
        return [new ExpensesDetailSheet($this->rows), new ExpensesSummarySheet($this->rows)];
    }
}
