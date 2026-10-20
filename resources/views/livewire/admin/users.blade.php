<div>
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="kx-h1">Usuarios</h1>
        <button type="button" wire:click="create" class="kx-btn">Nuevo usuario</button>
    </div>
    @if (session('status'))<p class="kx-alert kx-alert-ok mt-4" role="status">{{ session('status') }}</p>@endif

    @if ($showForm)
        <form wire:submit="save" class="kx-card mt-5 grid gap-4 sm:grid-cols-2" novalidate aria-label="Formulario de usuario">
            <div><label for="u-name" class="kx-label">Nombre</label><input id="u-name" type="text" wire:model="name" class="kx-input">@error('name')<p class="kx-error" role="alert">{{ $message }}</p>@enderror</div>
            <div><label for="u-email" class="kx-label">Correo</label><input id="u-email" type="email" wire:model="email" class="kx-input">@error('email')<p class="kx-error" role="alert">{{ $message }}</p>@enderror</div>
            <div><label for="u-phone" class="kx-label">Teléfono</label><input id="u-phone" type="tel" wire:model="phone" class="kx-input"></div>
            <div><label for="u-pos" class="kx-label">Cargo</label><input id="u-pos" type="text" wire:model="position" class="kx-input"></div>
            <div>
                <label for="u-role" class="kx-label">Rol</label>
                <select id="u-role" wire:model="role" class="kx-input">@foreach ($roles as $r)<option value="{{ $r->value }}">{{ $r->label() }}</option>@endforeach</select>
                @error('role')<p class="kx-error" role="alert">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="u-dep" class="kx-label">Departamento</label>
                <select id="u-dep" wire:model="department_id" class="kx-input"><option value="">Sin departamento</option>@foreach ($departments as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach</select>
                @error('department_id')<p class="kx-error" role="alert">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="u-pw" class="kx-label">{{ $editingId ? 'Contraseña nueva (opcional)' : 'Contraseña' }}</label>
                <input id="u-pw" type="password" wire:model="password" autocomplete="new-password" class="kx-input">
                @error('password')<p class="kx-error" role="alert">{{ $message }}</p>@enderror
            </div>
            <div class="flex items-end"><label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="active" class="size-4"> Cuenta activa</label></div>
            <div class="flex gap-3 sm:col-span-2">
                <button type="submit" class="kx-btn">Guardar usuario</button>
                <button type="button" wire:click="$set('showForm', false)" class="kx-btn-ghost">Cancelar</button>
            </div>
        </form>
    @endif

    <div class="mt-5">
        <label for="u-search" class="kx-label">Buscar por nombre o correo</label>
        <input id="u-search" type="search" wire:model.live.debounce.400ms="search" class="kx-input max-w-sm">
    </div>

    <section class="kx-card mt-4 overflow-x-auto p-0" aria-label="Listado de usuarios">
        <table class="kx-table">
            <thead><tr><th scope="col">Nombre</th><th scope="col">Rol</th><th scope="col">Departamento</th><th scope="col">Estado</th><th scope="col"><span class="sr-only">Acciones</span></th></tr></thead>
            <tbody>
                @foreach ($users as $u)
                    <tr wire:key="u-{{ $u->id }}">
                        <td>{{ $u->name }}<span class="block text-xs text-muted">{{ $u->email }}</span></td>
                        <td>{{ $u->role->label() }}</td>
                        <td>{{ $u->department?->name ?? '—' }}</td>
                        <td><span class="kx-badge {{ $u->active ? 'kx-badge-green' : 'kx-badge-gray' }}">{{ $u->active ? 'Activo' : 'Desactivado' }}</span></td>
                        <td class="whitespace-nowrap text-right">
                            <button type="button" wire:click="edit({{ $u->id }})" class="kx-link">Editar</button>
                            @if ($u->id !== auth()->id())
                                <button type="button" wire:click="toggle({{ $u->id }})" class="kx-link ml-3">{{ $u->active ? 'Desactivar' : 'Activar' }}</button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
    <div class="mt-4">{{ $users->links() }}</div>
</div>
