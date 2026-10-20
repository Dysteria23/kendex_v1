<x-layouts.public title="Recuperar contraseña" description="Te enviamos un enlace por correo para restablecer tu contraseña de Kendex.">
    <div class="mx-auto max-w-md px-4 py-12">
        <div class="kx-card">
            <h1 class="kx-h1">Recuperar contraseña</h1>
            <p class="kx-muted mt-2">Escribe tu correo y te enviamos un enlace para crear una contraseña nueva.</p>

            @if (session('status'))
                <p class="kx-alert kx-alert-ok mt-4" role="status">{{ session('status') }}</p>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="mt-5 space-y-4" novalidate>
                @csrf
                <x-honeypot />
                <div>
                    <label for="email" class="kx-label">Correo electrónico</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="kx-input">
                    @error('email') <p class="kx-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="kx-btn w-full">Enviar enlace</button>
            </form>
            <p class="mt-5 text-sm"><a class="kx-link" href="{{ route('login') }}">Volver a iniciar sesión</a></p>
        </div>
    </div>
</x-layouts.public>
