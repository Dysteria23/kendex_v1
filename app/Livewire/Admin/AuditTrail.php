<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\User;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/** Consulta del registro de auditoría. Solo lectura: no existe ninguna acción de edición (RN-10). */
#[Title('Auditoría')]
class AuditTrail extends Component
{
    use WithPagination;

    public string $user_id = '';
    public string $entity = '';
    public string $action = '';

    public function updated(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.admin.audit-trail', [
            'logs' => AuditLog::with('user:id,name')
                ->when($this->user_id, fn ($q, $v) => $q->where('user_id', $v))
                ->when($this->entity, fn ($q, $v) => $q->where('entity', $v))
                ->when($this->action, fn ($q, $v) => $q->where('action', 'like', $v . '%'))
                ->orderByDesc('id')->paginate(25),
            'users' => User::orderBy('name')->get(['id', 'name']),
            'entities' => AuditLog::select('entity')->distinct()->orderBy('entity')->pluck('entity'),
        ]);
    }
}
