<x-layouts.public title="Términos y condiciones"
    description="Condiciones de uso de Kendex: cuentas, responsabilidades, uso aceptable, registro de actividad y limitaciones del servicio.">
    <article class="mx-auto max-w-3xl space-y-6 px-4 py-12 leading-relaxed">
        <header>
            <h1 class="kx-h1">Términos y condiciones</h1>
            <p class="kx-muted mt-1">Última actualización: {{ now()->translatedFormat('j \d\e F \d\e Y') }}</p>
        </header>

        <section>
            <h2 class="kx-h2">1. Aceptación</h2>
            <p>Al crear una cuenta o usar Kendex aceptas estos términos y la <a class="kx-link" href="{{ route('privacy') }}">política de privacidad</a>. Si no estás de acuerdo, no uses el sistema.</p>
        </section>

        <section>
            <h2 class="kx-h2">2. Qué es Kendex</h2>
            <p>Kendex es una herramienta de {{ config('kendex.company.name') }} para registrar, aprobar y pagar solicitudes de gasto y controlar presupuestos por departamento. No sustituye la contabilidad oficial de la Empresa ni constituye asesoría fiscal.</p>
        </section>

        <section>
            <h2 class="kx-h2">3. Cuentas y roles</h2>
            <ul class="list-disc space-y-1 pl-6">
                <li>Eres responsable de mantener en secreto tu contraseña y de lo que ocurra con tu cuenta.</li>
                <li>Solo se permite una sesión activa por usuario; iniciar sesión en otro dispositivo cierra la anterior.</li>
                <li>El administrador asigna tu rol y departamento, y puede cambiarlos o desactivar tu cuenta.</li>
                <li>Las cuentas se bloquean temporalmente tras varios intentos fallidos de inicio de sesión.</li>
            </ul>
        </section>

        <section>
            <h2 class="kx-h2">4. Uso aceptable</h2>
            <p>Debes registrar solo gastos reales y con información veraz: montos, ITBIS, RNC, NCF y comprobantes auténticos. Está prohibido subir documentos falsos o ajenos, intentar acceder a información fuera de tu rol, alterar el registro de auditoría, saturar el servicio o cargar archivos que contengan código malicioso.</p>
        </section>

        <section>
            <h2 class="kx-h2">5. Aprobaciones y pagos</h2>
            <p>Una solicitud solo se considera aprobada cuando su estado cambia a «Aprobado» dentro del sistema, y solo se considera pagada cuando Finanzas la marca como «Pagado». Una solicitud rechazada no puede reabrirse; debe crearse una nueva. Las solicitudes pueden bloquearse si el presupuesto disponible no alcanza.</p>
        </section>

        <section>
            <h2 class="kx-h2">6. Registro de actividad</h2>
            <p>Kendex registra quién hizo qué, cuándo y desde qué dirección IP. Estos registros son inalterables y pueden usarse en auditorías internas y externas.</p>
        </section>

        <section>
            <h2 class="kx-h2">7. Disponibilidad</h2>
            <p>Procuramos que el servicio esté disponible de forma continua, pero puede haber mantenimientos o interrupciones. No garantizamos un funcionamiento ininterrumpido.</p>
        </section>

        <section>
            <h2 class="kx-h2">8. Responsabilidad</h2>
            <p>En la medida que permita la ley, la Empresa no responde por pérdidas derivadas del uso indebido de una cuenta por parte de su titular, por información incorrecta registrada por los usuarios ni por interrupciones fuera de su control.</p>
        </section>

        <section>
            <h2 class="kx-h2">9. Cambios y ley aplicable</h2>
            <p>Podemos actualizar estos términos y publicaremos la versión vigente en esta página. Se rigen por las leyes de la República Dominicana, y cualquier controversia se someterá a los tribunales competentes de Santo Domingo.</p>
        </section>

        <section>
            <h2 class="kx-h2">10. Contacto</h2>
            <p>Para dudas sobre estos términos escribe a <a class="kx-link" href="mailto:{{ config('kendex.company.email') }}">{{ config('kendex.company.email') }}</a>.</p>
        </section>
    </article>
</x-layouts.public>
