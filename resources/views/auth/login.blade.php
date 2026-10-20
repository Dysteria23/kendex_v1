<x-layouts.public title="Iniciar sesión" description="Accede a Kendex para solicitar, aprobar y pagar gastos de tu empresa.">
    <div class="mx-auto max-w-md px-4 py-12">
        <div class="kx-card">
            <h1 class="kx-h1">Iniciar sesión</h1>

            @if (session('status'))
                <p class="kx-alert kx-alert-ok mt-4" role="status">{{ session('status') }}</p>
            @endif

            <form method="POST" action="{{ route('login') }}" class="mt-5 space-y-4" novalidate>
                @csrf
                <x-honeypot />

                <div>
                    <label for="email" class="kx-label">Correo electrónico</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                           class="kx-input" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                    @error('email') <p id="email-error" class="kx-error" role="alert">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password" class="kx-label">Contraseña</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password" class="kx-input"
                           @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
                    @error('password') <p id="password-error" class="kx-error" role="alert">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="kx-btn w-full">Entrar</button>
            </form>

            <div class="mt-5 flex flex-col gap-2 text-sm">
                <a class="kx-link" href="{{ route('password.request') }}">Olvidé mi contraseña</a>
                @if (Route::has('register'))
                    <a class="kx-link" href="{{ route('register') }}">Crear una cuenta</a>
                @endif
            </div>
        </div>
    </div>
</x-layouts.public>
