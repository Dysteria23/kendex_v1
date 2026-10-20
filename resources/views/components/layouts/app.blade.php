@props(['title' => 'Panel'])
@php
    $user = auth()->user();
    $role = $user->role;
    $isAdmin = $role === \App\Enums\Role::Admin;
    $canReview = in_array($role, [\App\Enums\Role::Admin, \App\Enums\Role::Supervisor, \App\Enums\Role::Finance], true);
    $links = [
        ['dashboard', 'Panel', true],
        ['expenses.index', 'Solicitudes', true],
        ['expenses.create', 'Nueva solicitud', true],
    ];
    $admin = [
        ['admin.users', 'Usuarios'],
        ['admin.departments', 'Departamentos'],
        ['admin.categories', 'Categorías'],
        ['admin.budgets', 'Presupuestos'],
        ['admin.projects', 'Proyectos'],
        ['admin.rules', 'Reglas y alertas'],
        ['admin.audit', 'Auditoría'],
    ];
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <x-seo :title="$title . ' · Kendex'" :noindex="true" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-paper text-ink antialiased" x-data="{ nav: false }">
    <a href="#main" class="skip">Saltar al contenido</a>

    {{-- Barra superior (móvil) --}}
    <header class="flex items-center justify-between bg-ink px-4 py-3 text-white lg:hidden">
        <a href="{{ route('dashboard') }}" class="font-serif text-lg font-semibold">Kendex</a>
        <div class="flex items-center gap-2">
            <livewire:notifications.bell :key="'bell-m'" />
            <button type="button" class="rounded-md border border-white/40 px-3 py-1.5 text-sm" @click="nav = !nav"
                    :aria-expanded="nav" aria-controls="sidebar">Menú</button>
        </div>
    </header>

    <div class="lg:flex">
        <aside id="sidebar" class="bg-ink text-white lg:sticky lg:top-0 lg:block lg:h-screen lg:w-64 lg:shrink-0 lg:overflow-y-auto"
               :class="nav ? 'block' : 'hidden'">
            <div class="hidden items-center justify-between px-5 py-5 lg:flex">
                <a href="{{ route('dashboard') }}" class="font-serif text-xl font-semibold">Kendex</a>
                <livewire:notifications.bell :key="'bell-d'" />
            </div>

            <nav class="kx-nav space-y-1 px-3 pb-4 pt-2 lg:pt-0" aria-label="Principal">
                @foreach ($links as [$name, $label])
                    <a href="{{ route($name) }}" @if (request()->routeIs($name)) aria-current="page" @endif>{{ $label }}</a>
                @endforeach

                @if ($canReview)
                    <a href="{{ route('expenses.index', ['review' => 1]) }}">
                        {{ $role === \App\Enums\Role::Finance ? 'Por pagar' : 'Por revisar' }}
                    </a>
                @endif

                @if ($isAdmin)
                    <p class="px-3 pb-1 pt-4 text-xs font-semibold text-slate-300">Administración</p>
                    @foreach ($admin as [$name, $label])
                        <a href="{{ route($name) }}" @if (request()->routeIs($name)) aria-current="page" @endif>{{ $label }}</a>
                    @endforeach
                @endif
            </nav>

            <div class="border-t border-white/15 px-3 py-4 text-sm">
                <a href="{{ route('profile') }}" class="block rounded-md px-3 py-2 hover:bg-white/10">
                    <span class="block font-semibold">{{ $user->name }}</span>
                    <span class="text-slate-300">{{ $role->label() }}</span>
                </a>
                <form method="POST" action="{{ route('logout') }}" class="mt-1">
                    @csrf
                    <button type="submit" class="w-full rounded-md px-3 py-2 text-left text-slate-200 hover:bg-white/10">Cerrar sesión</button>
                </form>
            </div>
        </aside>

        <main id="main" class="min-w-0 flex-1 px-4 py-6 lg:px-8 lg:py-8">
            {{ $slot }}
        </main>
    </div>

    <x-cookie-banner />
    @livewireScripts
</body>
</html>
