<?php

namespace App\Exports;

use App\Support\Dates;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class ExpensesDetailSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize
{
    public function __construct(protected Collection $rows) {}

    public function title(): string
    {
        return 'Detalle';
    }

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return ['Número', 'Fecha del gasto', 'Responsable', 'Departamento', 'Categoría', 'Descripción',
            'Monto total (RD$)', 'Subtotal (RD$)', 'ITBIS (RD$)', 'RNC/Cédula', 'NCF', 'Estado', 'Creada'];
    }

    public function map($e): array
    {
        return [
            $e->number,
            $e->expense_date->format('d/m/Y'),
            $e->user->name,
            $e->department->name,
            $e->category->name,
            $e->description,
            (float) $e->amount,
            (float) $e->subtotal,
            (float) $e->itbis_amount,
            $e->rnc_provider,
            $e->ncf,
            $e->status->label(),
            Dates::display($e->created_at),
        ];
    }
}
