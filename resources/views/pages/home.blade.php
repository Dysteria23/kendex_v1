<x-layouts.public title="Kendex"
    description="Solicita, aprueba y paga gastos con control de presupuesto en pesos dominicanos, ITBIS, NCF y trazabilidad completa para tu empresa.">

    <section class="border-b border-line bg-white">
        <div class="mx-auto grid max-w-5xl gap-10 px-4 py-14 lg:grid-cols-2 lg:items-center">
            <div>
                <h1 class="font-serif text-4xl font-semibold leading-tight tracking-tight text-ink sm:text-5xl">
                    Cada gasto se aprueba antes de pagarse.
                </h1>
                <p class="mt-5 max-w-prose text-lg text-muted">
                    Kendex reemplaza el Excel y los recibos en papel: tus empleados solicitan, los supervisores aprueban dentro del presupuesto y Finanzas paga con todo el rastro registrado.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('login') }}" class="kx-btn">Iniciar sesión</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="kx-btn-ghost">Crear una cuenta</a>
                    @endif
                </div>
            </div>

            {{-- Ejemplo de la vista de presupuesto, hecho en HTML para no cargar imágenes --}}
            <figure class="kx-card" aria-labelledby="demo-cap">
                <figcaption id="demo-cap" class="kx-h2">Presupuesto del mes (ejemplo)</figcaption>
                <ul class="mt-4 space-y-4">
                    @foreach ([['Operaciones y Logística', 62, 'bg-harbor'], ['Ventas y Distribución', 87, 'bg-amber-600'], ['Almacén', 96, 'bg-red-700']] as [$name, $pct, $color])
                        <li>
                            <div class="flex justify-between text-sm"><span>{{ $name }}</span><span class="font-semibold tabular-nums">{{ $pct }} %</span></div>
                            <div class="mt-1 h-3 rounded-full bg-slate-200" role="img" aria-label="{{ $name }}: {{ $pct }} % del presupuesto usado">
                                <div class="h-3 rounded-full {{ $color }}" style="width: {{ $pct }}%"></div>
                            </div>
                        </li>
                    @endforeach
                </ul>
                <p class="kx-hint mt-4">Las barras cambian de color al llegar a los umbrales de alerta que defina el administrador.</p>
            </figure>
        </div>
    </section>

    <section class="mx-auto max-w-5xl px-4 py-14" aria-labelledby="how">
        <h2 id="how" class="kx-h1">Cómo funciona</h2>
        <ol class="mt-6 grid gap-4 sm:grid-cols-3">
            <li class="kx-card"><h3 class="kx-h2">1. Solicitar</h3><p class="kx-muted mt-2">El empleado registra el monto, el ITBIS, el RNC y el NCF del proveedor, y sube el comprobante desde el celular o la computadora.</p></li>
            <li class="kx-card"><h3 class="kx-h2">2. Aprobar</h3><p class="kx-muted mt-2">El supervisor aprueba, rechaza o pide cambios. Los montos grandes pasan a una segunda aprobación del administrador.</p></li>
            <li class="kx-card"><h3 class="kx-h2">3. Pagar</h3><p class="kx-muted mt-2">Finanzas registra el pago y el presupuesto pasa de comprometido a ejecutado, en tiempo real.</p></li>
        </ol>
    </section>

    <section class="mx-auto max-w-5xl px-4" aria-labelledby="why">
        <h2 id="why" class="kx-h1">Pensado para empresas dominicanas</h2>
        <ul class="mt-6 grid gap-x-8 gap-y-3 text-ink sm:grid-cols-2">
            <li>Montos en pesos dominicanos (RD$) con ITBIS desglosado.</li>
            <li>Validación del formato de RNC, cédula y NCF.</li>
            <li>Alertas al acercarse al límite del presupuesto de cada departamento.</li>
            <li>Historial de auditoría que nadie puede editar ni borrar.</li>
            <li>Reportes en PDF y Excel con los filtros que necesites.</li>
            <li>Funciona en computadora, tableta y celular.</li>
        </ul>
        <div class="mt-10">
            <a href="{{ route('login') }}" class="kx-btn">Entrar a Kendex</a>
        </div>
    </section>
</x-layouts.public>
