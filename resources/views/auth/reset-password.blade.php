<x-layouts.public title="Nueva contraseña" description="Crea una contraseña nueva para tu cuenta de Kendex.">
    <div class="mx-auto max-w-md px-4 py-12">
        <div class="kx-card">
            <h1 class="kx-h1">Crear contraseña nueva</h1>

            <form method="POST" action="{{ route('password.update') }}" class="mt-5 space-y-4" novalidate>
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div>
                    <label for="email" class="kx-label">Correo electrónico</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $email) }}" required autocomplete="username" class="kx-input">
                    @error('email') <p class="kx-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password" class="kx-label">Contraseña nueva</label>
                    <input id="password" name="password" type="password" required autocomplete="new-password" class="kx-input" aria-describedby="pw-hint">
                    <p id="pw-hint" class="kx-hint">Mínimo 10 caracteres, con letras y números.</p>
                    @error('password') <p class="kx-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="kx-label">Repite la contraseña</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="kx-input">
                </div>
                <button type="submit" class="kx-btn w-full">Guardar contraseña</button>
            </form>
        </div>
    </div>
</x-layouts.public>
