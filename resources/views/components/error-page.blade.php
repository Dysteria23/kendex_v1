@props(['code', 'title', 'message'])
<div class="mx-auto max-w-xl px-4 py-24 text-center">
    <p class="font-serif text-6xl font-semibold text-harbor">{{ $code }}</p>
    <h1 class="kx-h1 mt-4">{{ $title }}</h1>
    <p class="mt-3 text-muted">{{ $message }}</p>
    <div class="mt-8 flex justify-center gap-3">
        <a href="{{ url('/') }}" class="kx-btn">Volver al inicio</a>
        @auth
            <a href="{{ url('/panel') }}" class="kx-btn-ghost">Ir al panel</a>
        @endauth
    </div>
</div>
