<?php

namespace App\Livewire\Admin;

use App\Enums\Role;
use App\Models\Department;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

/** RF-11, RF-12 */
#[Title('Departamentos')]
class Departments extends Component
{
    public bool $showForm = false;
    public ?int $editingId = null;
    public string $name = '';
    public string $budget_monthly = '0';
    public string $supervisor_id = '';
    public bool $active = true;

    public function create(): void
    {
        $this->reset(['editingId', 'name', 'supervisor_id']);
        $this->budget_monthly = '0';
        $this->active = true;
        $this->resetErrorBag();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $d = Department::findOrFail($id);
        $this->editingId = $d->id;
        $this->name = $d->name;
        $this->budget_monthly = (string) $d->budget_monthly;
        $this->supervisor_id = (string) $d->supervisor_id;
        $this->active = $d->active;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->budget_monthly = str_replace(',', '', $this->budget_monthly);

        $data = $this->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('departments', 'name')->ignore($this->editingId)],
            'budget_monthly' => ['required', 'numeric', 'gte:0', 'max:99999999.99'],
            'supervisor_id' => ['nullable', Rule::exists('users', 'id')->where('role', Role::Supervisor->value)],
            'active' => ['boolean'],
        ], ['supervisor_id.exists' => 'Elige un usuario con rol Supervisor.']);

        $data['supervisor_id'] = $data['supervisor_id'] ?: null;

        $this->editingId ? Department::findOrFail($this->editingId)->update($data) : Department::create($data);

        $this->showForm = false;
        session()->flash('status', 'Departamento guardado.');
    }

    public function render()
    {
        return view('livewire.admin.departments', [
            'departments' => Department::with('supervisor:id,name')->withCount('users')->orderBy('name')->get(),
            'supervisors' => User::where('role', Role::Supervisor->value)->where('active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
