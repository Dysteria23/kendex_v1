<div>
    <h1 class="kx-h1">Registro de auditoría</h1>
    <p class="kx-muted mt-1">Solo lectura. Estos registros no pueden editarse ni eliminarse. Las horas se muestran en horario de Santo Domingo.</p>

    <section class="kx-card mt-5 grid gap-3 sm:grid-cols-3" aria-label="Filtros">
        <div>
            <label for="a-user" class="kx-label">Usuario</label>
            <select id="a-user" wire:model.live="user_id" class="kx-input"><option value="">Todos</option>@foreach ($users as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach</select>
        </div>
        <div>
            <label for="a-ent" class="kx-label">Entidad</label>
            <select id="a-ent" wire:model.live="entity" class="kx-input"><option value="">Todas</option>@foreach ($entities as $e)<option value="{{ $e }}">{{ $e }}</option>@endforeach</select>
        </div>
        <div>
            <label for="a-act" class="kx-label">Acción que empieza con</label>
            <input id="a-act" type="text" wire:model.live.debounce.400ms="action" class="kx-input" placeholder="expense, login…">
        </div>
    </section>

    <section class="kx-card mt-5 overflow-x-auto p-0" aria-label="Registros">
        <table class="kx-table min-w-[56rem]">
            <thead><tr><th scope="col">Fecha</th><th scope="col">Usuario</th><th scope="col">Acción</th><th scope="col">Entidad</th><th scope="col">IP</th><th scope="col">Detalle</th></tr></thead>
            <tbody>
                @foreach ($logs as $log)
                    <tr wire:key="l-{{ $log->id }}">
                        <td class="whitespace-nowrap">@dt($log->created_at)</td>
                        <td>{{ $log->user?->name ?? '—' }}</td>
                        <td>{{ $log->action }}</td>
                        <td>{{ $log->entity }}{{ $log->entity_id ? ' #' . $log->entity_id : '' }}</td>
                        <td>{{ $log->ip_address ?? '—' }}</td>
                        <td class="max-w-xs break-words text-xs text-muted">{{ $log->details ? \Illuminate\Support\Str::limit(json_encode($log->details, JSON_UNESCAPED_UNICODE), 160) : '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
    <div class="mt-4">{{ $logs->links() }}</div>
</div>
