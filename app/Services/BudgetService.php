<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Budget;
use App\Models\Department;
use App\Models\Expense;
use App\Enums\ExpenseStatus;
use App\Support\Dates;
use App\Support\Money;
use App\Support\Settings;
use Illuminate\Support\Facades\DB;

class BudgetService
{
    /** @return array{0:int,1:int} */
    protected function parse(string $period): array
    {
        return [(int) substr($period, 0, 4), (int) substr($period, 5, 2)];
    }

    public function departmentBudget(int $departmentId, string $period): ?Budget
    {
        [$y, $m] = $this->parse($period);

        return Budget::where('department_id', $departmentId)->whereNull('category_id')
            ->where('year', $y)->where('month', $m)->first();
    }

    public function categoryBudget(int $departmentId, int $categoryId, string $period): ?Budget
    {
        [$y, $m] = $this->parse($period);

        return Budget::where('department_id', $departmentId)->where('category_id', $categoryId)
            ->where('year', $y)->where('month', $m)->first();
    }

    /**
     * Motivo por el que una solicitud no puede crearse/aprobarse por presupuesto (RF-70, RF-48),
     * o null si puede continuar.
     */
    public function blockingReason(int $departmentId, int $categoryId, float $amount, ?string $period = null, ?int $projectId = null): ?string
    {
        $period ??= Dates::period();

        // Límite mensual de la categoría (RF-48), contando gasto aprobado y pagado de todos los departamentos.
        $category = \App\Models\Category::find($categoryId);
        if ($category && (float) $category->monthly_limit > 0) {
            $used = (float) Expense::where('category_id', $categoryId)->where('budget_period', $period)
                ->whereIn('status', [ExpenseStatus::Approved->value, ExpenseStatus::Paid->value])->sum('amount');

            if ($used + $amount > (float) $category->monthly_limit) {
                return 'La solicitud supera el límite mensual de la categoría «' . $category->name . '» (' . Money::format($category->monthly_limit) . ').';
            }
        }

        if (! Settings::get('block_enabled')) {
            return null;
        }

        $limitPct = (float) Settings::get('block_at_percent');

        $dept = $this->departmentBudget($departmentId, $period);
        if (! $dept || (float) $dept->amount_assigned <= 0) {
            return 'El departamento no tiene presupuesto asignado para ' . $period . '.';
        }

        $projected = (float) $dept->amount_committed + (float) $dept->amount_executed + $amount;
        if ($projected > (float) $dept->amount_assigned * $limitPct / 100) {
            return 'La solicitud excede el presupuesto disponible del departamento (' . Money::format($dept->available) . ').';
        }

        $cat = $this->categoryBudget($departmentId, $categoryId, $period);
        if ($cat && (float) $cat->amount_assigned > 0) {
            $projected = (float) $cat->amount_committed + (float) $cat->amount_executed + $amount;
            if ($projected > (float) $cat->amount_assigned * $limitPct / 100) {
                return 'La solicitud excede el presupuesto de la categoría en este departamento (' . Money::format($cat->available) . ').';
            }
        }

        // Presupuesto total del proyecto (RF-55).
        $project = $projectId ? \App\Models\Project::find($projectId) : null;
        if ($project && (float) $project->budget_total > 0) {
            $used = (float) Expense::where('project_id', $project->id)
                ->whereIn('status', [ExpenseStatus::Approved->value, ExpenseStatus::Paid->value])->sum('amount');

            if ($used + $amount > (float) $project->budget_total) {
                return 'La solicitud excede el presupuesto del proyecto «' . $project->name . '» (disponible: ' . Money::format((float) $project->budget_total - $used) . ').';
            }
        }

        return null;
    }

    /** Aprobación: el monto pasa a "Comprometido" (RF-31). */
    public function commit(Expense $expense): void
    {
        $period = Dates::period();
        $amount = (float) $expense->amount;

        DB::transaction(function () use ($expense, $period, $amount) {
            $expense->budget_period = $period;
            $expense->save();

            foreach ($this->rows($expense, $period, create: true) as $row) {
                $row = Budget::whereKey($row->id)->lockForUpdate()->first();
                $row->amount_committed = (float) $row->amount_committed + $amount;
                $row->save();
            }
        });

        [$y, $m] = $this->parse($period);
        $this->evaluateThresholds($expense->department_id, $y, $m);
    }

    /** Pago: el monto pasa de "Comprometido" a "Ejecutado" (RF-44). */
    public function execute(Expense $expense): void
    {
        $period = $expense->budget_period ?? Dates::period();
        $amount = (float) $expense->amount;

        DB::transaction(function () use ($expense, $period, $amount) {
            foreach ($this->rows($expense, $period, create: true) as $row) {
                $row = Budget::whereKey($row->id)->lockForUpdate()->first();
                $row->amount_committed = max(0, (float) $row->amount_committed - $amount);
                $row->amount_executed = (float) $row->amount_executed + $amount;
                $row->save();
            }
        });
    }

    /** Presupuesto del departamento + el de la categoría si existe. */
    protected function rows(Expense $expense, string $period, bool $create = false): array
    {
        [$y, $m] = $this->parse($period);

        $dept = $create
            ? Budget::firstOrCreate(
                ['department_id' => $expense->department_id, 'category_id' => null, 'year' => $y, 'month' => $m],
                ['amount_assigned' => 0]
            )
            : $this->departmentBudget($expense->department_id, $period);

        $cat = $this->categoryBudget($expense->department_id, $expense->category_id, $period);

        return array_values(array_filter([$dept, $cat]));
    }

    /** Niveles configurables de alerta (RF-67 a RF-69, RF-74). */
    public function levels(): array
    {
        return array_values(array_filter([
            ['type' => 'preventiva', 'pct' => (int) Settings::get('alert_preventive')],
            ['type' => 'advertencia', 'pct' => (int) Settings::get('alert_warning')],
            ['type' => 'critica', 'pct' => (int) Settings::get('alert_critical')],
        ], fn ($l) => $l['pct'] > 0));
    }

    public function evaluateThresholds(int $departmentId, int $year, int $month): void
    {
        $budget = $this->departmentBudget($departmentId, sprintf('%04d-%02d', $year, $month));
        if (! $budget || (float) $budget->amount_assigned <= 0) {
            return;
        }

        $period = $budget->period;
        $percent = $budget->used_percent;
        $department = Department::find($departmentId);

        foreach ($this->levels() as $level) {
            if ($percent < $level['pct']) {
                continue;
            }

            $exists = Alert::where('department_id', $departmentId)->where('period', $period)
                ->where('threshold', $level['pct'])->exists();
            if ($exists) {
                continue;
            }

            $message = sprintf(
                'Alerta %s: el departamento %s alcanzó el %s %% de su presupuesto de %s (%s de %s).',
                $level['type'],
                $department?->name,
                rtrim(rtrim(number_format($percent, 1), '0'), '.'),
                $period,
                Money::format((float) $budget->amount_committed + (float) $budget->amount_executed),
                Money::format($budget->amount_assigned)
            );

            Alert::create([
                'department_id' => $departmentId,
                'type' => $level['type'],
                'threshold' => $level['pct'],
                'period' => $period,
                'message' => $message,
            ]);

            NotificationService::send(
                NotificationService::supervisorsOf($departmentId)->merge(NotificationService::admins()),
                'budget.alert',
                $message,
                '/panel'
            );
        }
    }
}
