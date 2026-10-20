<x-layouts.public title="Crear cuenta" description="Crea tu cuenta de Kendex. El administrador de tu empresa te asignará un departamento y un rol.">
    <div class="mx-auto max-w-md px-4 py-12">
        <div class="kx-card">
            <h1 class="kx-h1">Crear cuenta</h1>
            <p class="kx-muted mt-2">Tu administrador te asignará un departamento y un rol después de registrarte.</p>

            <form method="POST" action="{{ route('register') }}" class="mt-5 space-y-4" novalidate>
                @csrf
                <x-honeypot />

                @foreach ([['name', 'Nombre completo', 'text', 'name'], ['email', 'Correo electrónico', 'email', 'username']] as [$field, $label, $type, $auto])
                    <div>
                        <label for="{{ $field }}" class="kx-label">{{ $label }}</label>
                        <input id="{{ $field }}" name="{{ $field }}" type="{{ $type }}" value="{{ old($field) }}" required autocomplete="{{ $auto }}" class="kx-input"
                               @error($field) aria-invalid="true" aria-describedby="{{ $field }}-error" @enderror>
                        @error($field) <p id="{{ $field }}-error" class="kx-error" role="alert">{{ $message }}</p> @enderror
                    </div>
                @endforeach

                <div>
                    <label for="password" class="kx-label">Contraseña</label>
                    <input id="password" name="password" type="password" required autocomplete="new-password" class="kx-input"
                           aria-describedby="password-hint @error('password') password-error @enderror">
                    <p id="password-hint" class="kx-hint">Mínimo 10 caracteres, con letras y números.</p>
                    @error('password') <p id="password-error" class="kx-error" role="alert">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="kx-label">Repite la contraseña</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="kx-input">
                </div>

                <p class="kx-hint">Al crear tu cuenta aceptas los <a class="kx-link" href="{{ route('terms') }}">términos y condiciones</a> y la <a class="kx-link" href="{{ route('privacy') }}">política de privacidad</a>.</p>

                <button type="submit" class="kx-btn w-full">Crear cuenta</button>
            </form>

            <p class="mt-5 text-sm">¿Ya tienes cuenta? <a class="kx-link" href="{{ route('login') }}">Inicia sesión</a></p>
        </div>
    </div>
</x-layouts.public>
