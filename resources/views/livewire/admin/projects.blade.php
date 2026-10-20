<div>
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="kx-h1">Proyectos</h1>
        <button type="button" wire:click="create" class="kx-btn">Nuevo proyecto</button>
    </div>
    <p class="kx-muted mt-1">Un proyecto agrupa gastos de varios meses y puede tener un tope total. Las solicitudes que lo superen se bloquean.</p>
    @if (session('status'))<p class="kx-alert kx-alert-ok mt-4" role="status">{{ session('status') }}</p>@endif

    @if ($showForm)
        <form wire:submit="save" class="kx-card mt-5 grid gap-4 sm:grid-cols-2" novalidate aria-label="Formulario de proyecto">
            <div><label for="p-code" class="kx-label">Código</label><input id="p-code" type="text" wire:model="code" class="kx-input uppercase">@error('code')<p class="kx-error" role="alert">{{ $message }}</p>@enderror</div>
            <div><label for="p-name" class="kx-label">Nombre</label><input id="p-name" type="text" wire:model="name" class="kx-input">@error('name')<p class="kx-error" role="alert">{{ $message }}</p>@enderror</div>
            <div>
                <label for="p-dep" class="kx-label">Departamento (opcional)</label>
                <select id="p-dep" wire:model="department_id" class="kx-input"><option value="">Cualquier departamento</option>@foreach ($departments as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach</select>
            </div>
            <div>
                <label for="p-budget" class="kx-label">Presupuesto total (RD$, opcional)</label>
                <input id="p-budget" type="text" inputmode="decimal" wire:model="budget_total" class="kx-input">
                @error('budget_total')<p class="kx-error" role="alert">{{ $message }}</p>@enderror
            </div>
            <div class="flex items-end"><label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="active" class="size-4"> Proyecto activo</label></div>
            <div class="flex gap-3 sm:col-span-2">
                <button type="submit" class="kx-btn">Guardar proyecto</button>
                <button type="button" wire:click="$set('showForm', false)" class="kx-btn-ghost">Cancelar</button>
            </div>
        </form>
    @endif

    <section class="kx-card mt-5 overflow-x-auto p-0" aria-label="Listado de proyectos">
        <table class="kx-table">
            <thead><tr><th scope="col">Código</th><th scope="col">Proyecto</th><th scope="col">Departamento</th><th scope="col" class="text-right">Presupuesto</th><th scope="col" class="text-right">Gastado</th><th scope="col">Estado</th><th scope="col"><span class="sr-only">Acciones</span></th></tr></thead>
            <tbody>
                @forelse ($projects as $p)
                    <tr wire:key="p-{{ $p->id }}">
                        <td>{{ $p->code }}</td>
                        <td>{{ $p->name }}</td>
                        <td>{{ $p->department?->name ?? 'Todos' }}</td>
                        <td class="text-right">{{ $p->budget_total ? \App\Support\Money::format($p->budget_total) : 'Sin tope' }}</td>
                        <td class="text-right">@money($spent[$p->id] ?? 0)</td>
                        <td><span class="kx-badge {{ $p->active ? 'kx-badge-green' : 'kx-badge-gray' }}">{{ $p->active ? 'Activo' : 'Cerrado' }}</span></td>
                        <td class="text-right"><button type="button" wire:click="edit({{ $p->id }})" class="kx-link">Editar</button></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-8 text-center text-muted">Aún no hay proyectos. Crea el primero con «Nuevo proyecto».</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
</div>
