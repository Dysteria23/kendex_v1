<?php

namespace App\Http\Controllers;

use App\Exports\ExpensesExport;
use App\Models\Expense;
use App\Support\ExpenseFilter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/** RF-65: exportación a PDF y Excel respetando la visibilidad por rol. */
class ReportController extends Controller
{
    protected function rows(Request $request)
    {
        $filters = $request->only(['search', 'status', 'category_id', 'department_id', 'project_id', 'from', 'to', 'min', 'max', 'fiscal']);

        $rows = ExpenseFilter::apply(
            Expense::query()->visibleTo($request->user())->with(['user:id,name', 'department:id,name', 'category:id,name']),
            $filters
        )->orderByDesc('id')->limit(5000)->get();

        return [$rows, array_filter($filters, fn ($v) => $v !== null && $v !== '')];
    }

    public function pdf(Request $request)
    {
        [$rows, $filters] = $this->rows($request);

        $byDepartment = $rows->groupBy(fn ($e) => $e->department->name)->map(fn ($g) => $g->sum('amount'))->sortDesc();
        $byCategory = $rows->groupBy(fn ($e) => $e->category->name)->map(fn ($g) => $g->sum('amount'))->sortDesc();

        return Pdf::loadView('reports.expenses', [
            'rows' => $rows,
            'filters' => $filters,
            'byDepartment' => $byDepartment,
            'byCategory' => $byCategory,
            'total' => $rows->sum('amount'),
        ])->setPaper('letter', 'landscape')->download('kendex-gastos-' . now()->format('Ymd-His') . '.pdf');
    }

    public function xlsx(Request $request)
    {
        [$rows] = $this->rows($request);

        return Excel::download(new ExpensesExport($rows), 'kendex-gastos-' . now()->format('Ymd-His') . '.xlsx');
    }
}
