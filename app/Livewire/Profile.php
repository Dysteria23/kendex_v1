<?php

namespace App\Livewire;

use App\Models\AuditLog;
use Livewire\Attributes\Title;
use Livewire\Component;

/** Perfil (RF-13) e historial de actividad e inicios de sesión (RF-14). */
#[Title('Mi perfil')]
class Profile extends Component
{
    public string $name = '';
    public string $phone = '';

    public function mount(): void
    {
        $this->name = auth()->user()->name;
        $this->phone = (string) auth()->user()->phone;
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+()\-\s]{7,30}$/'],
        ], ['phone.regex' => 'Escribe un teléfono válido, por ejemplo 809-555-0100.']);

        auth()->user()->update(['name' => $data['name'], 'phone' => $data['phone'] ?: null]);
        session()->flash('status', 'Perfil actualizado.');
    }

    public function render()
    {
        return view('livewire.profile', [
            'user' => auth()->user()->load('department'),
            'logs' => AuditLog::where('user_id', auth()->id())->orderByDesc('id')->limit(25)->get(),
        ]);
    }
}
