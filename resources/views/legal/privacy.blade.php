<x-layouts.public title="Política de privacidad"
    description="Cómo Kendex recopila, usa y protege los datos personales de los usuarios, conforme a la Ley núm. 172-13 de la República Dominicana.">
    <article class="mx-auto max-w-3xl space-y-6 px-4 py-12 leading-relaxed">
        <header>
            <h1 class="kx-h1">Política de privacidad</h1>
            <p class="kx-muted mt-1">Última actualización: {{ now()->translatedFormat('j \d\e F \d\e Y') }}</p>
        </header>

        <section>
            <h2 class="kx-h2">1. Quién es responsable de tus datos</h2>
            <p>{{ config('kendex.company.name') }} (en adelante, «la Empresa»), con domicilio en {{ config('kendex.company.address') }}, es la responsable del tratamiento de los datos personales que se registran en Kendex. Puedes escribirnos a <a class="kx-link" href="mailto:{{ config('kendex.company.email') }}">{{ config('kendex.company.email') }}</a>.</p>
        </section>

        <section>
            <h2 class="kx-h2">2. Qué datos recopilamos</h2>
            <ul class="list-disc space-y-1 pl-6">
                <li><strong>Datos de cuenta:</strong> nombre, correo electrónico, teléfono, cargo, departamento y rol.</li>
                <li><strong>Datos de uso del sistema:</strong> solicitudes de gasto, comprobantes, comentarios, aprobaciones y pagos.</li>
                <li><strong>Datos técnicos de seguridad:</strong> dirección IP, fecha y hora de cada inicio de sesión y de cada acción relevante.</li>
                <li><strong>Datos de analítica (solo si los aceptas):</strong> páginas visitadas y dispositivo, mediante Google Analytics 4.</li>
            </ul>
        </section>

        <section>
            <h2 class="kx-h2">3. Para qué los usamos</h2>
            <p>Usamos tus datos para identificarte, aplicar los permisos de tu rol, gestionar el flujo de solicitudes, aprobaciones y pagos, mantener el control presupuestario, enviarte notificaciones del sistema, proteger la cuenta frente a accesos indebidos y cumplir obligaciones contables y fiscales de la Empresa. No vendemos tus datos ni los usamos para publicidad.</p>
        </section>

        <section>
            <h2 class="kx-h2">4. Base legal</h2>
            <p>El tratamiento se basa en la relación laboral o comercial entre tú y la Empresa, en el cumplimiento de obligaciones legales y en tu consentimiento cuando se trata de cookies de analítica, de acuerdo con la Ley núm. 172-13 sobre Protección Integral de los Datos Personales.</p>
        </section>

        <section>
            <h2 class="kx-h2">5. Con quién los compartimos</h2>
            <p>Solo con proveedores que hacen posible el servicio (alojamiento, almacenamiento de archivos y envío de correo) y con autoridades cuando una ley lo exija. Si aceptas la analítica, Google recibe datos de navegación anonimizados.</p>
        </section>

        <section>
            <h2 class="kx-h2">6. Cuánto tiempo los conservamos</h2>
            <p>Las solicitudes, los pagos y los registros de auditoría se conservan mientras la Empresa los necesite para fines contables, fiscales y de auditoría. Cuando una cuenta se desactiva, su historial se conserva íntegro. Las copias de seguridad se guardan durante 30 días.</p>
        </section>

        <section>
            <h2 class="kx-h2">7. Cómo los protegemos</h2>
            <p>Las contraseñas se guardan cifradas, la comunicación usa HTTPS, los comprobantes pasan por validación de tipo de archivo y el acceso a cada dato depende del rol. Hay límites de intentos de inicio de sesión y un registro de auditoría que no puede modificarse.</p>
        </section>

        <section>
            <h2 class="kx-h2">8. Tus derechos</h2>
            <p>Puedes pedir acceso a tus datos, su rectificación, actualización o supresión cuando proceda, y oponerte a ciertos usos. Escribe a <a class="kx-link" href="mailto:{{ config('kendex.company.email') }}">{{ config('kendex.company.email') }}</a> indicando qué necesitas. Algunos datos, como los registros de auditoría y fiscales, no pueden eliminarse mientras exista una obligación legal de conservarlos.</p>
        </section>

        <section>
            <h2 class="kx-h2">9. Cookies</h2>
            <p>Kendex usa cookies esenciales para mantener tu sesión y proteger los formularios; no se pueden desactivar sin que el sistema deje de funcionar. Las cookies de analítica solo se activan si pulsas «Aceptar analítica» en el aviso, y puedes cambiar tu decisión en cualquier momento desde «Preferencias de cookies», al final de la página.</p>
        </section>

        <section>
            <h2 class="kx-h2">10. Cambios en esta política</h2>
            <p>Si cambiamos esta política, publicaremos la nueva versión en esta misma página con su fecha de actualización.</p>
        </section>
    </article>
</x-layouts.public>
