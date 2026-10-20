<div>
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="kx-h1">Categorías de gasto</h1>
        <button type="button" wire:click="create" class="kx-btn">Nueva categoría</button>
    </div>
    @if (session('status'))<p class="kx-alert kx-alert-ok mt-4" role="status">{{ session('status') }}</p>@endif

    @if ($showForm)
        <form wire:submit="save" class="kx-card mt-5 grid gap-4 sm:grid-cols-2" novalidate aria-label="Formulario de categoría">
            <div><label for="c-name" class="kx-label">Nombre</label><input id="c-name" type="text" wire:model="name" class="kx-input">@error('name')<p class="kx-error" role="alert">{{ $message }}</p>@enderror</div>
            <div>
                <label for="c-parent" class="kx-label">Categoría principal (para crear una subcategoría)</label>
                <select id="c-parent" wire:model="parent_id" class="kx-input"><option value="">Ninguna</option>@foreach ($parents as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select>
                @error('parent_id')<p class="kx-error" role="alert">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="c-limit" class="kx-label">Límite mensual (RD$, opcional)</label>
                <input id="c-limit" type="text" inputmode="decimal" wire:model="monthly_limit" class="kx-input">
                @error('monthly_limit')<p class="kx-error" role="alert">{{ $message }}</p>@enderror
            </div>
            <div class="flex items-end"><label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="active" class="size-4"> Categoría activa</label></div>
            <div class="flex gap-3 sm:col-span-2">
                <button type="submit" class="kx-btn">Guardar categoría</button>
                <button type="button" wire:click="$set('showForm', false)" class="kx-btn-ghost">Cancelar</button>
            </div>
        </form>
    @endif

    <section class="kx-card mt-5 overflow-x-auto p-0" aria-label="Listado de categorías">
        <table class="kx-table">
            <thead><tr><th scope="col">Categoría</th><th scope="col" class="text-right">Límite mensual</th><th scope="col">Gastos</th><th scope="col">Estado</th><th scope="col"><span class="sr-only">Acciones</span></th></tr></thead>
            <tbody>
                @foreach ($categories as $c)
                    <tr wire:key="c-{{ $c->id }}">
                        <td>{{ $c->parent ? $c->parent->name . ' › ' : '' }}{{ $c->name }}</td>
                        <td class="text-right">{{ $c->monthly_limit ? \App\Support\Money::format($c->monthly_limit) : 'Sin límite' }}</td>
                        <td>{{ $c->expenses_count }}</td>
                        <td><span class="kx-badge {{ $c->active ? 'kx-badge-green' : 'kx-badge-gray' }}">{{ $c->active ? 'Activa' : 'Desactivada' }}</span></td>
                        <td class="whitespace-nowrap text-right">
                            <button type="button" wire:click="edit({{ $c->id }})" class="kx-link">Editar</button>
                            <button type="button" wire:click="toggle({{ $c->id }})" class="kx-link ml-3">{{ $c->active ? 'Desactivar' : 'Activar' }}</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
</div>
