<div class="mx-auto max-w-3xl">
    <h1 class="kx-h1">Mi perfil</h1>
    @if (session('status'))<p class="kx-alert kx-alert-ok mt-4" role="status">{{ session('status') }}</p>@endif

    <form wire:submit="save" class="kx-card mt-5 grid gap-4 sm:grid-cols-2" novalidate>
        <div>
            <label for="name" class="kx-label">Nombre</label>
            <input id="name" type="text" wire:model="name" class="kx-input">
            @error('name')<p class="kx-error" role="alert">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="phone" class="kx-label">Teléfono</label>
            <input id="phone" type="tel" wire:model="phone" class="kx-input" placeholder="809-555-0100">
            @error('phone')<p class="kx-error" role="alert">{{ $message }}</p>@enderror
        </div>
        <div><span class="kx-label">Correo</span><p class="kx-input bg-slate-50">{{ $user->email }}</p></div>
        <div><span class="kx-label">Departamento y cargo</span><p class="kx-input bg-slate-50">{{ $user->department?->name ?? 'Sin asignar' }} · {{ $user->position ?: 'Sin cargo' }}</p></div>
        <div class="sm:col-span-2"><button type="submit" class="kx-btn">Guardar cambios</button></div>
    </form>

    <section class="kx-card mt-5 overflow-x-auto" aria-labelledby="act-h">
        <h2 id="act-h" class="kx-h2">Actividad reciente</h2>
        <table class="kx-table mt-3">
            <thead><tr><th scope="col">Fecha</th><th scope="col">Acción</th><th scope="col">IP</th></tr></thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr><td>@dt($log->created_at)</td><td>{{ $log->action }} · {{ $log->entity }}</td><td>{{ $log->ip_address ?? '—' }}</td></tr>
                @empty
                    <tr><td colspan="3" class="py-6 text-center text-muted">Sin actividad registrada.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
</div>
