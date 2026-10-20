<?php

namespace App\Livewire\Admin;

use App\Enums\Role;
use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/** RF-06, RF-07, RF-09, RF-10: usuarios, roles y departamentos. */
#[Title('Usuarios')]
class Users extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showForm = false;
    public ?int $editingId = null;

    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $position = '';
    public string $role = 'empleado';
    public string $department_id = '';
    public string $password = '';
    public bool $active = true;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $u = User::findOrFail($id);
        $this->editingId = $u->id;
        $this->name = $u->name;
        $this->email = $u->email;
        $this->phone = (string) $u->phone;
        $this->position = (string) $u->position;
        $this->role = $u->role->value;
        $this->department_id = (string) $u->department_id;
        $this->active = $u->active;
        $this->password = '';
        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($this->editingId)],
            'phone' => ['nullable', 'string', 'max:30'],
            'position' => ['nullable', 'string', 'max:120'],
            'role' => ['required', Rule::enum(Role::class)],
            'department_id' => ['nullable', 'exists:departments,id'],
            'active' => ['boolean'],
            'password' => [$this->editingId ? 'nullable' : 'required', Password::min(10)->letters()->numbers()],
        ]);

        if ($this->editingId === auth()->id() && ($data['role'] !== Role::Admin->value || ! $data['active'])) {
            $this->addError('role', 'No puedes quitarte el rol de administrador ni desactivar tu propia cuenta.');

            return;
        }

        $payload = [
            'name' => $data['name'], 'email' => Str::lower($data['email']), 'phone' => $data['phone'] ?: null,
            'position' => $data['position'] ?: null, 'role' => $data['role'],
            'department_id' => $data['department_id'] ?: null, 'active' => $data['active'],
        ];
        if (filled($data['password'])) {
            $payload['password'] = $data['password'];
        }

        $user = $this->editingId ? tap(User::findOrFail($this->editingId))->update($payload) : User::create($payload);

        // Un cambio de rol, de estado o de contraseña cierra la sesión vigente del usuario.
        if ($this->editingId) {
            $user->forceFill(['current_session_id' => null])->saveQuietly();
        }

        $this->showForm = false;
        session()->flash('status', 'Usuario guardado.');
    }

    public function toggle(int $id): void
    {
        abort_if($id === auth()->id(), 403);

        $user = User::findOrFail($id);
        $user->update(['active' => ! $user->active]);
        $user->forceFill(['current_session_id' => null])->saveQuietly();
        session()->flash('status', $user->active ? 'Usuario activado.' : 'Usuario desactivado. Su historial se conserva.');
    }

    protected function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'email', 'phone', 'position', 'department_id', 'password']);
        $this->role = 'empleado';
        $this->active = true;
        $this->resetErrorBag();
    }

    public function render()
    {
        $like = '%' . mb_strtolower($this->search) . '%';

        return view('livewire.admin.users', [
            'users' => User::with('department:id,name')
                ->when($this->search, fn ($q) => $q->where(fn ($w) => $w->whereRaw('LOWER(name) LIKE ?', [$like])->orWhereRaw('LOWER(email) LIKE ?', [$like])))
                ->orderBy('name')->paginate(15),
            'roles' => Role::cases(),
            'departments' => Department::where('active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
