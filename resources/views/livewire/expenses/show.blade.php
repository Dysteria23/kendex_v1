<div class="mx-auto max-w-4xl">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <p class="kx-muted"><a class="kx-link" href="{{ route('expenses.index') }}" wire:navigate>Solicitudes</a> / {{ $expense->number }}</p>
            <h1 class="kx-h1 mt-1">{{ $expense->number }}</h1>
        </div>
        <span class="{{ $expense->status->badge() }} text-sm">{{ $expense->status->label() }}</span>
    </div>

    @if (session('status'))<p class="kx-alert kx-alert-ok mt-4" role="status">{{ session('status') }}</p>@endif
    @error('budget')<p class="kx-alert kx-alert-error mt-4" role="alert">{{ $message }}</p>@enderror
    @error('status')<p class="kx-alert kx-alert-error mt-4" role="alert">{{ $message }}</p>@enderror

    @if ($expense->status === \App\Enums\ExpenseStatus::Pending && $expense->approval_stage === 'admin')
        <p class="kx-alert kx-alert-warn mt-4">Esta solicitud espera la aprobación de un administrador.</p>
    @endif

    <section class="kx-card mt-5" aria-label="Datos de la solicitud">
        <dl class="grid gap-x-6 gap-y-4 sm:grid-cols-2">
            <div><dt class="kx-muted">Responsable</dt><dd class="font-medium">{{ $expense->user->name }}</dd></div>
            <div><dt class="kx-muted">Departamento</dt><dd class="font-medium">{{ $expense->department->name }}</dd></div>
            <div><dt class="kx-muted">Categoría</dt><dd class="font-medium">{{ $expense->category->full_name }}</dd></div>
            <div><dt class="kx-muted">Proyecto</dt><dd class="font-medium">{{ $expense->project ? $expense->project->code . ' · ' . $expense->project->name : '—' }}</dd></div>
            <div><dt class="kx-muted">Fecha del gasto</dt><dd class="font-medium">@d($expense->expense_date)</dd></div>
            <div><dt class="kx-muted">Monto total</dt><dd class="text-xl font-semibold tabular-nums">@money($expense->amount)</dd></div>
            <div><dt class="kx-muted">Subtotal / ITBIS</dt><dd class="font-medium tabular-nums">@money($expense->subtotal) / @money($expense->itbis_amount)</dd></div>
            <div><dt class="kx-muted">RNC o cédula del proveedor</dt><dd class="font-medium">{{ $expense->rnc_provider ?: '—' }}</dd></div>
            <div><dt class="kx-muted">NCF</dt><dd class="font-medium">{{ $expense->ncf ?: '—' }}</dd></div>
            <div class="sm:col-span-2"><dt class="kx-muted">Descripción</dt><dd class="whitespace-pre-line">{{ $expense->description }}</dd></div>
            <div class="sm:col-span-2">
                <dt class="kx-muted">Comprobantes</dt>
                <dd>
                    @forelse ($expense->receipts() as $i => $r)
                        <a class="kx-link mr-4 inline-block" target="_blank" rel="noopener" href="{{ route('receipts.show', [$expense, $i]) }}">{{ $r['name'] }}</a>
                    @empty
                        <span class="text-muted">Sin comprobantes</span>
                    @endforelse
                </dd>
            </div>
            @if ($expense->payment)
                <div class="sm:col-span-2 rounded-md bg-emerald-50 p-3 text-emerald-900">
                    Pagado el @d($expense->payment->paid_at) por {{ $expense->payment->method->label() }}
                    @if ($expense->payment->reference) · Ref. {{ $expense->payment->reference }} @endif
                    · registrado por {{ $expense->payment->recorder->name }}
                </div>
            @endif
        </dl>

        @if ($canUpdate || $canCancel)
            <div class="mt-5 flex flex-wrap gap-3 border-t border-line pt-4">
                @if ($canUpdate)<a href="{{ route('expenses.edit', $expense) }}" class="kx-btn-ghost" wire:navigate>Editar</a>@endif
                @if ($canCancel)
                    <button type="button" class="kx-btn-ghost" wire:click="cancel"
                            wire:confirm="¿Anular esta solicitud? Quedará en el historial y no podrás reactivarla.">Anular solicitud</button>
                @endif
            </div>
        @endif
    </section>

    @if ($canReview || $canComment || $canPay)
        <section class="kx-card mt-5 space-y-4" aria-label="Acciones">
            @if ($canPay)
                <h2 class="kx-h2">Registrar pago</h2>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label for="method" class="kx-label">Método de pago</label>
                        <select id="method" wire:model="method" class="kx-input">
                            <option value="">Selecciona…</option>
                            @foreach ($methods as $m)<option value="{{ $m->value }}">{{ $m->label() }}</option>@endforeach
                        </select>
                        @error('method')<p class="kx-error" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="paid_at" class="kx-label">Fecha real de pago</label>
                        <input id="paid_at" type="date" wire:model="paid_at" class="kx-input">
                        @error('paid_at')<p class="kx-error" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="reference" class="kx-label">Referencia o transacción</label>
                        <input id="reference" type="text" wire:model="reference" class="kx-input">
                        @error('reference')<p class="kx-error" role="alert">{{ $message }}</p>@enderror
                    </div>
                </div>
                <button type="button" class="kx-btn" wire:click="pay" wire:loading.attr="disabled">Registrar pago</button>
            @endif

            @if ($canReview || $canComment)
                <h2 class="kx-h2">{{ $canReview ? 'Revisión' : 'Comentarios' }}</h2>
                <div>
                    <label for="comment" class="kx-label">{{ $canReview ? 'Comentario (obligatorio para rechazar o pedir cambios)' : 'Comentario' }}</label>
                    <textarea id="comment" rows="3" wire:model="comment" class="kx-input"></textarea>
                    @error('comment')<p class="kx-error" role="alert">{{ $message }}</p>@enderror
                </div>
                <div class="flex flex-wrap gap-3">
                    @if ($canReview)
                        <button type="button" class="kx-btn" wire:click="approve" wire:loading.attr="disabled">Aprobar</button>
                        <button type="button" class="kx-btn-ghost" wire:click="requestChanges" wire:loading.attr="disabled">Pedir modificación</button>
                        <button type="button" class="kx-btn-danger" wire:click="reject" wire:loading.attr="disabled"
                                wire:confirm="Al rechazar, la solicitud no se podrá reabrir. ¿Continuar?">Rechazar</button>
                    @endif
                    @if ($canComment)
                        <button type="button" class="kx-btn-ghost" wire:click="addComment">Solo comentar</button>
                    @endif
                </div>
            @endif
        </section>
    @endif

    <section class="kx-card mt-5" aria-label="Historial">
        <h2 class="kx-h2">Historial</h2>
        <ol class="mt-3 divide-y divide-line">
            @forelse ($expense->approvals as $a)
                <li class="py-3 text-sm">
                    <p><strong>{{ $a->user->name }}</strong> {{ mb_strtolower($a->actionLabel()) }}
                        <span class="text-muted">· @dt($a->created_at)</span></p>
                    @if ($a->comment)<p class="mt-1 whitespace-pre-line text-muted">{{ $a->comment }}</p>@endif
                </li>
            @empty
                <li class="py-3 text-sm text-muted">Aún no hay acciones registradas. Creada el @dt($expense->created_at).</li>
            @endforelse
        </ol>
    </section>
</div>
