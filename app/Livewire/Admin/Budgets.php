<?php

namespace App\Livewire\Admin;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Department;
use App\Support\Dates;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Title;
use Livewire\Component;

/** RF-49 a RF-54: presupuestos mensuales por departamento y por categoría. */
#[Title('Presupuestos')]
class Budgets extends Component
{
    public string $period = '';
    public array $amounts = [];

    public string $new_department = '';
    public string $new_category = '';
    public string $new_amount = '';

    public function mount(): void
    {
        $this->period = Dates::period();
        $this->load();
    }

    public function updatedPeriod(): void
    {
        $this->load();
    }

    protected function parts(): array
    {
        return [(int) substr($this->period, 0, 4), (int) substr($this->period, 5, 2)];
    }

    protected function load(): void
    {
        [$y, $m] = $this->parts();
        $this->amounts = Budget::where('year', $y)->where('month', $m)->pluck('amount_assigned', 'id')
            ->map(fn ($v) => (string) $v)->all();
    }

    /** Crea el presupuesto del período para los departamentos que aún no lo tienen. */
    public function openPeriod(): void
    {
        $this->validate(['period' => ['required', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/']]);
        [$y, $m] = $this->parts();

        foreach (Department::where('active', true)->get() as $d) {
            Budget::firstOrCreate(
                ['department_id' => $d->id, 'category_id' => null, 'year' => $y, 'month' => $m],
                ['amount_assigned' => $d->budget_monthly]
            );
        }

        $this->load();
        session()->flash('status', 'Presupuesto del período creado a partir del monto mensual de cada departamento.');
    }

    /** Ajuste o adenda sobre un período vigente (RF-53); los períodos cerrados no se tocan (RF-54). */
    public function saveAmount(int $id): void
    {
        $budget = Budget::findOrFail($id);
        abort_if($budget->closed, 403);

        $value = str_replace(',', '', $this->amounts[$id] ?? '');
        validator(['v' => $value], ['v' => ['required', 'numeric', 'gte:0', 'max:99999999.99']], [], ['v' => 'monto'])->validate();

        $budget->update(['amount_assigned' => $value]);
        Cache::tags(['metrics'])->flush();
        session()->flash('status', 'Presupuesto actualizado.');
    }

    public function addCategoryBudget(): void
    {
        $this->new_amount = str_replace(',', '', $this->new_amount);
        $this->validate([
            'period' => ['required', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'new_department' => ['required', 'exists:departments,id'],
            'new_category' => ['required', 'exists:categories,id'],
            'new_amount' => ['required', 'numeric', 'gt:0'],
        ], [], ['new_department' => 'departamento', 'new_category' => 'categoría', 'new_amount' => 'monto']);

        [$y, $m] = $this->parts();

        $closed = Budget::where('year', $y)->where('month', $m)->where('closed', true)->exists();
        abort_if($closed, 403);

        Budget::updateOrCreate(
            ['department_id' => $this->new_department, 'category_id' => $this->new_category, 'year' => $y, 'month' => $m],
            ['amount_assigned' => $this->new_amount]
        );

        $this->reset(['new_department', 'new_category', 'new_amount']);
        $this->load();
        Cache::tags(['metrics'])->flush();
    }

    public function render()
    {
        [$y, $m] = $this->parts();

        return view('livewire.admin.budgets', [
            'budgets' => Budget::with(['department:id,name', 'category:id,name'])->where('year', $y)->where('month', $m)->get()
                ->sortBy(fn ($b) => $b->department->name . ($b->category_id ? '1' : '0') . $b->category?->name)->values(),
            'departments' => Department::where('active', true)->orderBy('name')->get(['id', 'name']),
            'categories' => Category::where('active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
