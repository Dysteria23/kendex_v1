<div>
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="kx-h1">Departamentos</h1>
        <button type="button" wire:click="create" class="kx-btn">Nuevo departamento</button>
    </div>
    @if (session('status'))<p class="kx-alert kx-alert-ok mt-4" role="status">{{ session('status') }}</p>@endif

    @if ($showForm)
        <form wire:submit="save" class="kx-card mt-5 grid gap-4 sm:grid-cols-2" novalidate aria-label="Formulario de departamento">
            <div><label for="d-name" class="kx-label">Nombre</label><input id="d-name" type="text" wire:model="name" class="kx-input">@error('name')<p class="kx-error" role="alert">{{ $message }}</p>@enderror</div>
            <div>
                <label for="d-budget" class="kx-label">Presupuesto mensual base (RD$)</label>
                <input id="d-budget" type="text" inputmode="decimal" wire:model="budget_monthly" class="kx-input" aria-describedby="d-budget-h">
                <p id="d-budget-h" class="kx-hint">Se usa al abrir cada mes nuevo.</p>
                @error('budget_monthly')<p class="kx-error" role="alert">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="d-sup" class="kx-label">Supervisor</label>
                <select id="d-sup" wire:model="supervisor_id" class="kx-input"><option value="">Sin supervisor principal</option>@foreach ($supervisors as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select>
                @error('supervisor_id')<p class="kx-error" role="alert">{{ $message }}</p>@enderror
            </div>
            <div class="flex items-end"><label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="active" class="size-4"> Departamento activo</label></div>
            <div class="flex gap-3 sm:col-span-2">
                <button type="submit" class="kx-btn">Guardar departamento</button>
                <button type="button" wire:click="$set('showForm', false)" class="kx-btn-ghost">Cancelar</button>
            </div>
        </form>
    @endif

    <section class="kx-card mt-5 overflow-x-auto p-0" aria-label="Listado de departamentos">
        <table class="kx-table">
            <thead><tr><th scope="col">Departamento</th><th scope="col">Supervisor</th><th scope="col" class="text-right">Presupuesto base</th><th scope="col">Personas</th><th scope="col">Estado</th><th scope="col"><span class="sr-only">Acciones</span></th></tr></thead>
            <tbody>
                @foreach ($departments as $d)
                    <tr wire:key="d-{{ $d->id }}">
                        <td>{{ $d->name }}</td>
                        <td>{{ $d->supervisor?->name ?? '—' }}</td>
                        <td class="text-right">@money($d->budget_monthly)</td>
                        <td>{{ $d->users_count }}</td>
                        <td><span class="kx-badge {{ $d->active ? 'kx-badge-green' : 'kx-badge-gray' }}">{{ $d->active ? 'Activo' : 'Desactivado' }}</span></td>
                        <td class="text-right"><button type="button" wire:click="edit({{ $d->id }})" class="kx-link">Editar</button></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
</div>
