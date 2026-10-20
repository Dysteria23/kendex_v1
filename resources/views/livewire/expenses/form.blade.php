<div class="mx-auto max-w-3xl">
    <h1 class="kx-h1">{{ $editing ? 'Editar solicitud ' . $expense->number : 'Nueva solicitud de gasto' }}</h1>
    @if ($editing && $expense->status === \App\Enums\ExpenseStatus::ChangesRequested)
        <p class="kx-alert kx-alert-warn mt-4" role="status">El revisor pidió cambios. Al guardar, la solicitud se reenvía para su revisión.</p>
    @endif

    @error('budget')<p class="kx-alert kx-alert-error mt-4" role="alert">{{ $message }}</p>@enderror
    @error('department_id')<p class="kx-alert kx-alert-error mt-4" role="alert">{{ $message }}</p>@enderror

    <form wire:submit="save" class="kx-card mt-5 space-y-5" novalidate>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="category_id" class="kx-label">Categoría</label>
                <select id="category_id" wire:model="category_id" class="kx-input" @error('category_id') aria-invalid="true" @enderror>
                    <option value="">Selecciona…</option>
                    @foreach ($categories as $c)<option value="{{ $c->id }}">{{ $c->full_name }}</option>@endforeach
                </select>
                @error('category_id')<p class="kx-error" role="alert">{{ $message }}</p>@enderror
            </div>

            @if ($isAdmin)
                <div>
                    <label for="department_id" class="kx-label">Departamento</label>
                    <select id="department_id" wire:model="department_id" class="kx-input">
                        @foreach ($departments as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
                    </select>
                </div>
            @else
                <div>
                    <span class="kx-label">Responsable y departamento</span>
                    <p class="kx-input bg-slate-50">{{ auth()->user()->name }} · {{ auth()->user()->department?->name ?? 'Sin departamento' }}</p>
                </div>
            @endif

            <div>
                <label for="project_id" class="kx-label">Proyecto</label>
                <select id="project_id" wire:model="project_id" class="kx-input">
                    <option value="">Sin proyecto</option>
                    @foreach ($projects as $p)<option value="{{ $p->id }}">{{ $p->code }} · {{ $p->name }}</option>@endforeach
                </select>
                @error('project_id')<p class="kx-error" role="alert">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="expense_date" class="kx-label">Fecha del gasto</label>
                <input id="expense_date" type="date" wire:model="expense_date" max="{{ \App\Support\Dates::today() }}" class="kx-input" @error('expense_date') aria-invalid="true" @enderror>
                @error('expense_date')<p class="kx-error" role="alert">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="amount" class="kx-label">Monto total (RD$)</label>
                <input id="amount" type="text" inputmode="decimal" wire:model.blur="amount" class="kx-input" placeholder="0.00" @error('amount') aria-invalid="true" @enderror>
                @error('amount')<p class="kx-error" role="alert">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="itbis_amount" class="kx-label">ITBIS facturado (RD$)</label>
                <input id="itbis_amount" type="text" inputmode="decimal" wire:model.blur="itbis_amount" class="kx-input" @error('itbis_amount') aria-invalid="true" @enderror>
                @error('itbis_amount')<p class="kx-error" role="alert">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="rnc_provider" class="kx-label">RNC o cédula del proveedor</label>
                <input id="rnc_provider" type="text" inputmode="numeric" wire:model.blur="rnc_provider" class="kx-input" aria-describedby="rnc-hint" @error('rnc_provider') aria-invalid="true" @enderror>
                <p id="rnc-hint" class="kx-hint">Opcional. 9 dígitos (RNC) u 11 (cédula), sin guiones.</p>
                @error('rnc_provider')<p class="kx-error" role="alert">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="ncf" class="kx-label">NCF</label>
                <input id="ncf" type="text" wire:model.blur="ncf" class="kx-input uppercase" placeholder="B0100000001" aria-describedby="ncf-hint" @error('ncf') aria-invalid="true" @enderror>
                <p id="ncf-hint" class="kx-hint">Obligatorio si registras el RNC del proveedor.</p>
                @error('ncf')<p class="kx-error" role="alert">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label for="description" class="kx-label">Descripción</label>
            <textarea id="description" rows="3" wire:model="description" class="kx-input" @error('description') aria-invalid="true" @enderror></textarea>
            @error('description')<p class="kx-error" role="alert">{{ $message }}</p>@enderror
        </div>

        <fieldset>
            <legend class="kx-label">Comprobantes</legend>
            <p class="kx-hint mb-2">PDF, JPG o PNG, hasta 10 MB cada uno. Obligatorio para montos mayores a @money($threshold).</p>

            <ul class="mb-3 space-y-2">
                @foreach ($existing as $i => $r)
                    <li class="flex items-center justify-between rounded-md border border-line px-3 py-2 text-sm" wire:key="ex-{{ $i }}">
                        <span>{{ $r['name'] }}</span>
                        <button type="button" wire:click="removeExisting({{ $i }})" class="kx-link">Quitar</button>
                    </li>
                @endforeach
                @foreach ($uploads as $i => $file)
                    <li class="flex items-center justify-between rounded-md border border-line px-3 py-2 text-sm" wire:key="up-{{ $i }}">
                        <span>{{ $file->getClientOriginalName() }}</span>
                        <button type="button" wire:click="removeUpload({{ $i }})" class="kx-link">Quitar</button>
                    </li>
                @endforeach
            </ul>

            <label for="upload" class="kx-btn-ghost cursor-pointer">Agregar comprobante</label>
            <input id="upload" type="file" wire:model="upload" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" class="sr-only">
            <span wire:loading wire:target="upload" class="kx-muted ml-3">Subiendo…</span>
            @error('upload')<p class="kx-error" role="alert">{{ $message }}</p>@enderror
            @error('uploads')<p class="kx-error" role="alert">{{ $message }}</p>@enderror
        </fieldset>

        <div class="flex flex-wrap gap-3 border-t border-line pt-5">
            <button type="submit" class="kx-btn" wire:loading.attr="disabled" wire:target="save">
                {{ $editing ? 'Guardar cambios' : 'Enviar solicitud' }}
            </button>
            <a href="{{ $editing ? route('expenses.show', $expense) : route('expenses.index') }}" class="kx-btn-ghost" wire:navigate>Cancelar</a>
        </div>
    </form>
</div>
