<?php

namespace App\Livewire\Admin;

use App\Enums\ExpenseStatus;
use App\Models\Department;
use App\Models\Project;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

/** RF-55: control de presupuesto por proyectos. */
#[Title('Proyectos')]
class Projects extends Component
{
    public bool $showForm = false;
    public ?int $editingId = null;
    public string $code = '';
    public string $name = '';
    public string $department_id = '';
    public string $budget_total = '';
    public bool $active = true;

    public function create(): void
    {
        $this->reset(['editingId', 'code', 'name', 'department_id', 'budget_total']);
        $this->active = true;
        $this->resetErrorBag();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $p = Project::findOrFail($id);
        $this->editingId = $p->id;
        $this->code = $p->code;
        $this->name = $p->name;
        $this->department_id = (string) $p->department_id;
        $this->budget_total = $p->budget_total !== null ? (string) $p->budget_total : '';
        $this->active = $p->active;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->budget_total = str_replace(',', '', $this->budget_total);

        $data = $this->validate([
            'code' => ['required', 'string', 'max:30', Rule::unique('projects', 'code')->ignore($this->editingId)],
            'name' => ['required', 'string', 'max:150'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'budget_total' => ['nullable', 'numeric', 'gt:0', 'max:99999999.99'],
            'active' => ['boolean'],
        ], [], ['code' => 'código', 'name' => 'nombre', 'budget_total' => 'presupuesto total']);

        $data['department_id'] = $data['department_id'] ?: null;
        $data['budget_total'] = $data['budget_total'] !== '' ? $data['budget_total'] : null;

        $this->editingId ? Project::findOrFail($this->editingId)->update($data) : Project::create($data);

        $this->showForm = false;
        session()->flash('status', 'Proyecto guardado.');
    }

    public function render()
    {
        $spent = \App\Models\Expense::whereNotNull('project_id')
            ->whereIn('status', [ExpenseStatus::Approved->value, ExpenseStatus::Paid->value])
            ->selectRaw('project_id, sum(amount) as total')->groupBy('project_id')->pluck('total', 'project_id');

        return view('livewire.admin.projects', [
            'projects' => Project::with('department:id,name')->orderBy('name')->get(),
            'departments' => Department::where('active', true)->orderBy('name')->get(['id', 'name']),
            'spent' => $spent,
        ]);
    }
}
