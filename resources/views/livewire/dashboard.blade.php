<div>
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="kx-h1">Panel</h1>
            <p class="kx-muted">Período {{ $period }}</p>
        </div>
        @if ($canReview)
            <a href="{{ route('expenses.index', ['review' => 1]) }}" class="kx-btn" wire:navigate>
                {{ $pendingReview }} {{ $pendingReview === 1 ? 'solicitud requiere' : 'solicitudes requieren' }} tu acción
            </a>
        @endif
    </div>

    <section class="kx-card mt-5" aria-label="Filtros del panel">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
            <div><label for="f-from" class="kx-label">Desde</label><input id="f-from" type="date" wire:model.live="from" class="kx-input"></div>
            <div><label for="f-to" class="kx-label">Hasta</label><input id="f-to" type="date" wire:model.live="to" class="kx-input"></div>
            <div>
                <label for="f-dep" class="kx-label">Departamento</label>
                <select id="f-dep" wire:model.live="department_id" class="kx-input">
                    <option value="">Todos</option>
                    @foreach ($departments as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
                </select>
            </div>
            <div>
                <label for="f-cat" class="kx-label">Categoría</label>
                <select id="f-cat" wire:model.live="category_id" class="kx-input">
                    <option value="">Todas</option>
                    @foreach ($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                </select>
            </div>
            <div>
                <label for="f-fis" class="kx-label">Estado fiscal</label>
                <select id="f-fis" wire:model.live="fiscal" class="kx-input">
                    <option value="">Todos</option><option value="con_ncf">Con NCF</option><option value="sin_ncf">Sin NCF</option>
                </select>
            </div>
        </div>
    </section>

    {{-- Métricas del mes: comprometido vs. ejecutado (RF-56) y comparativa (RF-61) --}}
    @php($total = $committed + $executed)
    @php($delta = $previous > 0 ? round((($total - $previous) / $previous) * 100, 1) : null)
    <section class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4" aria-label="Resumen del mes">
        <div class="kx-card"><p class="kx-muted">Comprometido (aprobado)</p><p class="mt-1 text-2xl font-semibold tabular-nums">@money($committed)</p></div>
        <div class="kx-card"><p class="kx-muted">Ejecutado (pagado)</p><p class="mt-1 text-2xl font-semibold tabular-nums">@money($executed)</p></div>
        <div class="kx-card"><p class="kx-muted">Total del mes</p><p class="mt-1 text-2xl font-semibold tabular-nums">@money($total)</p></div>
        <div class="kx-card">
            <p class="kx-muted">Mes anterior</p>
            <p class="mt-1 text-2xl font-semibold tabular-nums">@money($previous)</p>
            <p class="kx-muted mt-1">
                @if ($delta === null) Sin datos para comparar
                @else {{ $delta >= 0 ? 'Subió' : 'Bajó' }} {{ abs($delta) }} % este mes
                @endif
            </p>
        </div>
    </section>

    <section class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4" aria-label="Contadores de solicitudes">
        @foreach ([['pendiente', 'Pendientes', 'kx-badge-amber'], ['aprobado', 'Aprobadas', 'kx-badge-teal'], ['rechazado', 'Rechazadas', 'kx-badge-red'], ['pagado', 'Pagadas', 'kx-badge-green']] as [$k, $label, $badge])
            <a href="{{ route('expenses.index', ['status' => $k]) }}" class="kx-card block hover:border-harbor" wire:navigate>
                <span class="kx-badge {{ $badge }}">{{ $label }}</span>
                <p class="mt-2 text-3xl font-semibold tabular-nums">{{ $counters[$k] }}</p>
            </a>
        @endforeach
    </section>

    {{-- Salud financiera por departamento (RF-60) --}}
    <section class="kx-card mt-5" aria-labelledby="health-h">
        <h2 id="health-h" class="kx-h2">Presupuesto usado por departamento</h2>
        <ul class="mt-4 space-y-5">
            @forelse ($health as $h)
                @php($color = $h['pct'] >= 90 ? 'bg-red-700' : ($h['pct'] >= 80 ? 'bg-amber-600' : 'bg-harbor'))
                <li>
                    <div class="flex flex-wrap justify-between gap-2 text-sm">
                        <span class="font-medium">{{ $h['name'] }}</span>
                        <span class="tabular-nums text-muted">
                            Asignado @money($h['assigned']) · Comprometido @money($h['committed']) · Ejecutado @money($h['executed']) · Disponible @money($h['available'])
                        </span>
                    </div>
                    <div class="mt-1 h-3 overflow-hidden rounded-full bg-slate-200" role="progressbar" aria-valuemin="0" aria-valuemax="100"
                         aria-valuenow="{{ min(100, $h['pct']) }}" aria-label="{{ $h['name'] }}: {{ $h['pct'] }} % del presupuesto">
                        <div class="h-3 rounded-full {{ $color }}" style="width: {{ min(100, $h['pct']) }}%"></div>
                    </div>
                    <p class="mt-1 text-xs font-semibold tabular-nums">{{ $h['pct'] }} % ejecutado</p>
                </li>
            @empty
                <li class="kx-muted">No hay presupuestos definidos para este período. El administrador puede crearlos en Presupuestos.</li>
            @endforelse
        </ul>
    </section>

    <section class="mt-5 grid gap-4 lg:grid-cols-2">
        <div class="kx-card">
            <h2 class="kx-h2">Gastos por departamento</h2>
            <div class="relative mt-3 h-64" wire:key="dept-{{ md5(json_encode($deptChart)) }}" x-data="kxChart(@js($deptChart))">
                <canvas x-ref="canvas" role="img" aria-label="Gráfico de barras de gastos por departamento"></canvas>
            </div>
        </div>
        <div class="kx-card">
            <h2 class="kx-h2">Gastos por categoría</h2>
            <div class="relative mt-3 h-64" wire:key="cat-{{ md5(json_encode($catChart)) }}" x-data="kxChart(@js($catChart))">
                <canvas x-ref="canvas" role="img" aria-label="Gráfico de anillo de gastos por categoría"></canvas>
            </div>
        </div>
    </section>

    <section class="kx-card mt-5">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="kx-h2">Tendencia de gastos</h2>
            <div class="flex gap-2" role="group" aria-label="Rango de la tendencia">
                <button type="button" wire:click="$set('months', 6)" class="{{ $months === 6 ? 'kx-btn' : 'kx-btn-ghost' }}" aria-pressed="{{ $months === 6 ? 'true' : 'false' }}">6 meses</button>
                <button type="button" wire:click="$set('months', 12)" class="{{ $months === 12 ? 'kx-btn' : 'kx-btn-ghost' }}" aria-pressed="{{ $months === 12 ? 'true' : 'false' }}">12 meses</button>
            </div>
        </div>
        <div class="relative mt-3 h-64" wire:key="trend-{{ md5(json_encode($trendChart)) }}" x-data="kxChart(@js($trendChart))">
            <canvas x-ref="canvas" role="img" aria-label="Gráfico de líneas de la tendencia mensual de gastos"></canvas>
        </div>
    </section>

    <section class="kx-card mt-5 overflow-x-auto" aria-labelledby="top-h">
        <h2 id="top-h" class="kx-h2">Los 5 gastos de mayor impacto del mes</h2>
        <table class="kx-table mt-3">
            <thead><tr><th scope="col">Solicitud</th><th scope="col">Departamento</th><th scope="col">Descripción</th><th scope="col" class="text-right">Monto</th></tr></thead>
            <tbody>
                @forelse ($top as $t)
                    <tr>
                        <td><a class="kx-link" href="{{ route('expenses.show', $t['id']) }}" wire:navigate>{{ $t['number'] }}</a></td>
                        <td>{{ $t['department'] }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($t['description'], 70) }}</td>
                        <td class="text-right">@money($t['amount'])</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-8 text-center text-muted">Todavía no hay gastos aprobados este mes.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
</div>
