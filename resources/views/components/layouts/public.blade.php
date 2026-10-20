@props(['title' => 'Kendex', 'description' => null])
<!DOCTYPE html>
<html lang="es">
<head>
    <x-seo :title="$title === 'Kendex' ? 'Kendex · Control de gastos empresariales' : $title . ' · Kendex'"
           :description="$description ?? 'Kendex ordena las solicitudes de gasto de tu empresa: aprobación por niveles, control de presupuesto y pagos con trazabilidad.'" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-paper text-ink antialiased">
    <a href="#main" class="skip">Saltar al contenido</a>

    <header class="border-b border-line bg-white">
        <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-3">
            <a href="{{ route('home') }}" class="flex items-center gap-2 font-serif text-xl font-semibold text-ink">
                <x-img src="{{ asset('favicon.svg') }}" alt="Logotipo de Kendex" width="28" height="28" :lazy="false" />
                Kendex
            </a>
            <nav aria-label="Principal" class="flex items-center gap-2">
                @auth
                    <a href="{{ route('dashboard') }}" class="kx-btn">Ir al panel</a>
                @else
                    <a href="{{ route('login') }}" class="kx-btn">Iniciar sesión</a>
                @endauth
            </nav>
        </div>
    </header>

    <main id="main">{{ $slot }}</main>

    <footer class="mt-16 border-t border-line bg-white">
        <div class="mx-auto flex max-w-5xl flex-col gap-3 px-4 py-6 text-sm text-muted sm:flex-row sm:items-center sm:justify-between">
            <p>© {{ now()->year }} Kendex · {{ config('kendex.company.name') }}</p>
            <nav aria-label="Legal" class="flex flex-wrap gap-4">
                <a class="kx-link" href="{{ route('privacy') }}">Política de privacidad</a>
                <a class="kx-link" href="{{ route('terms') }}">Términos y condiciones</a>
                <button type="button" class="kx-link" onclick="kxResetConsent()">Preferencias de cookies</button>
            </nav>
        </div>
    </footer>

    <x-cookie-banner />
    @livewireScripts
</body>
</html>
