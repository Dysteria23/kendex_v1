<div class="mx-auto max-w-3xl">
    <h1 class="kx-h1">Reglas y alertas</h1>
    <p class="kx-muted mt-1">Estos valores se aplican de inmediato a todo el sistema.</p>
    @if (session('status'))<p class="kx-alert kx-alert-ok mt-4" role="status">{{ session('status') }}</p>@endif

    <form wire:submit="save" class="kx-card mt-5 space-y-4" novalidate>
        @foreach ($fields as $key => [$label])
            <div>
                <label for="r-{{ $key }}" class="kx-label">{{ $label }}</label>
                <input id="r-{{ $key }}" type="text" inputmode="decimal" wire:model="values.{{ $key }}" class="kx-input max-w-xs">
                @error("values.$key")<p class="kx-error" role="alert">{{ $message }}</p>@enderror
            </div>
        @endforeach
        <button type="submit" class="kx-btn">Guardar reglas</button>
    </form>
</div>
