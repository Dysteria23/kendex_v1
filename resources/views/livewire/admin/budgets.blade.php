<div>
    <h1 class="kx-h1">Presupuestos</h1>
    @if (session('status'))<p class="kx-alert kx-alert-ok mt-4" role="status">{{ session('status') }}</p>@endif

    <section class="kx-card mt-5 flex flex-wrap items-end gap-4" aria-label="Período">
        <div>
            <label for="b-period" class="kx-label">Período</label>
            <input id="b-period" type="month" wire:model.live="period" class="kx-input">
            @error('period')<p class="kx-error" role="alert">{{ $message }}</p>@enderror
        </div>
        <button type="button" wire:click="openPeriod" class="kx-btn-ghost">Crear presupuesto de los departamentos que faltan</button>
    </section>

    <section class="kx-card mt-5 overflow-x-auto p-0" aria-label="Presupuestos del período">
        <table class="kx-table min-w-[56rem]">
            <thead>
                <tr><th scope="col">Departamento</th><th scope="col">Categoría</th><th scope="col">Asignado (RD$)</th><th scope="col" class="text-right">Comprometido</th><th scope="col" class="text-right">Ejecutado</th><th scope="col" class="text-right">Disponible</th><th scope="col" class="text-right">%</th></tr>
            </thead>
            <tbody>
                @forelse ($budgets as $b)
                    <tr wire:key="b-{{ $b->id }}">
                        <td>{{ $b->department->name }}</td>
                        <td>{{ $b->category?->name ?? 'Total del departamento' }}</td>
                        <td>
                            @if ($b->closed)
                                @money($b->amount_assigned) <span class="kx-badge kx-badge-gray ml-1">Cerrado</span>
                            @else
                                <div class="flex items-center gap-2">
                                    <label class="sr-only" for="amt-{{ $b->id }}">Monto asignado de {{ $b->department->name }}</label>
                                    <input id="amt-{{ $b->id }}" type="text" inputmode="decimal" wire:model="amounts.{{ $b->id }}" class="kx-input w-36">
                                    <button type="button" wire:click="saveAmount({{ $b->id }})" class="kx-link">Guardar</button>
                                </div>
                                @error('v')<p class="kx-error" role="alert">{{ $message }}</p>@enderror
                            @endif
                        </td>
                        <td class="text-right">@money($b->amount_committed)</td>
                        <td class="text-right">@money($b->amount_executed)</td>
                        <td class="text-right">@money($b->available)</td>
                        <td class="text-right">{{ $b->used_percent }} %</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-8 text-center text-muted">Este período no tiene presupuestos. Usa «Crear presupuesto de los departamentos que faltan».</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <form wire:submit="addCategoryBudget" class="kx-card mt-5 grid gap-4 sm:grid-cols-4" novalidate aria-label="Presupuesto por categoría">
        <h2 class="kx-h2 sm:col-span-4">Presupuesto por categoría dentro de un departamento (opcional)</h2>
        <div>
            <label for="n-dep" class="kx-label">Departamento</label>
            <select id="n-dep" wire:model="new_department" class="kx-input"><option value="">Selecciona…</option>@foreach ($departments as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach</select>
            @error('new_department')<p class="kx-error" role="alert">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="n-cat" class="kx-label">Categoría</label>
            <select id="n-cat" wire:model="new_category" class="kx-input"><option value="">Selecciona…</option>@foreach ($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select>
            @error('new_category')<p class="kx-error" role="alert">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="n-amt" class="kx-label">Monto (RD$)</label>
            <input id="n-amt" type="text" inputmode="decimal" wire:model="new_amount" class="kx-input">
            @error('new_amount')<p class="kx-error" role="alert">{{ $message }}</p>@enderror
        </div>
        <div class="flex items-end"><button type="submit" class="kx-btn">Agregar</button></div>
    </form>
</div>
