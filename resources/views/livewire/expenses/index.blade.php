<div>
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="kx-h1">{{ $review ? 'Solicitudes por atender' : 'Solicitudes de gasto' }}</h1>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('reports.pdf', $exportQuery) }}" class="kx-btn-ghost">Exportar PDF</a>
            <a href="{{ route('reports.xlsx', $exportQuery) }}" class="kx-btn-ghost">Exportar Excel</a>
            <a href="{{ route('expenses.create') }}" class="kx-btn" wire:navigate>Nueva solicitud</a>
        </div>
    </div>

    @if (session('status'))
        <p class="kx-alert kx-alert-ok mt-4" role="status">{{ session('status') }}</p>
    @endif

    <section class="kx-card mt-5" aria-label="Filtros">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="sm:col-span-2">
                <label for="search" class="kx-label">Buscar</label>
                <input id="search" type="search" wire:model.live.debounce.400ms="search" class="kx-input"
                       placeholder="Descripción, responsable, RNC, NCF o número">
            </div>
            <div>
                <label for="status" class="kx-label">Estado</label>
                <select id="status" wire:model.live="status" class="kx-input">
                    <option value="">Todos</option>
                    @foreach ($statuses as $s)<option value="{{ $s->value }}">{{ $s->label() }}</option>@endforeach
                </select>
            </div>
            <div>
                <label for="category" class="kx-label">Categoría</label>
                <select id="category" wire:model.live="category_id" class="kx-input">
                    <option value="">Todas</option>
                    @foreach ($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                </select>
            </div>
            <div>
                <label for="department" class="kx-label">Departamento</label>
                <select id="department" wire:model.live="department_id" class="kx-input">
                    <option value="">Todos</option>
                    @foreach ($departments as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
                </select>
            </div>
            <div>
                <label for="project" class="kx-label">Proyecto</label>
                <select id="project" wire:model.live="project_id" class="kx-input">
                    <option value="">Todos</option>
                    @foreach ($projects as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach
                </select>
            </div>
            <div>
                <label for="from" class="kx-label">Desde</label>
                <input id="from" type="date" wire:model.live="from" class="kx-input">
            </div>
            <div>
                <label for="to" class="kx-label">Hasta</label>
                <input id="to" type="date" wire:model.live="to" class="kx-input">
            </div>
            <div>
                <label for="fiscal" class="kx-label">Estado fiscal</label>
                <select id="fiscal" wire:model.live="fiscal" class="kx-input">
                    <option value="">Todos</option>
                    <option value="con_ncf">Con NCF</option>
                    <option value="sin_ncf">Sin NCF</option>
                </select>
            </div>
            <div>
                <label for="min" class="kx-label">Monto mínimo (RD$)</label>
                <input id="min" type="number" min="0" step="0.01" wire:model.live.debounce.500ms="min" class="kx-input">
            </div>
            <div>
                <label for="max" class="kx-label">Monto máximo (RD$)</label>
                <input id="max" type="number" min="0" step="0.01" wire:model.live.debounce.500ms="max" class="kx-input">
            </div>
        </div>
        <div class="mt-4 flex items-center justify-between gap-3">
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" wire:model.live="review" class="size-4 rounded border-slate-500">
                Solo las que requieren mi acción
            </label>
            <button type="button" wire:click="clear" class="kx-link text-sm">Limpiar filtros</button>
        </div>
    </section>

    <section class="kx-card mt-5 overflow-x-auto p-0" aria-label="Listado de solicitudes">
        <table class="kx-table">
            <thead>
                <tr>
                    <th scope="col">Número</th>
                    <th scope="col">Fecha</th>
                    <th scope="col">Responsable</th>
                    <th scope="col">Categoría</th>
                    <th scope="col" class="text-right">Monto</th>
                    <th scope="col">Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($expenses as $e)
                    <tr wire:key="exp-{{ $e->id }}">
                        <td><a class="kx-link" href="{{ route('expenses.show', $e) }}" wire:navigate>{{ $e->number }}</a><span class="block max-w-xs truncate text-xs text-muted">{{ $e->description }}</span></td>
                        <td>@d($e->expense_date)</td>
                        <td>{{ $e->user->name }}<span class="block text-xs text-muted">{{ $e->department->name }}</span></td>
                        <td>{{ $e->category->name }}</td>
                        <td class="text-right">@money($e->amount)</td>
                        <td><span class="{{ $e->status->badge() }}">{{ $e->status->label() }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-10 text-center text-muted">No hay solicitudes con esos filtros. Cambia los filtros o crea una nueva solicitud.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <div class="mt-4">{{ $expenses->links() }}</div>
</div>
