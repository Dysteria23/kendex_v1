# Kendex — Sistema de gestión de gastos

Capa de código para **Laravel 12 (PHP 8.3) + Livewire 3 + Alpine + Tailwind 4 + PostgreSQL 16 + Redis 7**.



## 1. Instalación

```bash
composer create-project laravel/laravel kendex && cd kendex
# copia el contenido de esta carpeta encima del proyecto (sobrescribe archivos)

composer require livewire/livewire predis/predis league/flysystem-aws-s3-v3 \
  barryvdh/laravel-dompdf maatwebsite/excel intervention/image
composer require pestphp/pest pestphp/pest-plugin-laravel --dev
php artisan pest:install          # si pregunta por tests/Pest.php, conserva el de esta carpeta
npm install chart.js && npm install

rm public/robots.txt              # lo genera la ruta /robots.txt con tu dominio
rm database/migrations/0001_01_01_000001_create_cache_table.php 2>/dev/null   # solo si usas Redis para caché
cat deploy/env.kendex.example     # copia estas variables a .env y .env.example

docker compose up -d              # PostgreSQL, Redis, MinIO, Mailpit (desarrollo)
php artisan key:generate
php artisan migrate --seed        # el seeder SOLO corre fuera de producción
npm run dev   # y en otra terminal:  php artisan serve
php artisan queue:work            # correos y notificaciones
php artisan schedule:work         # recordatorios y apertura de mes
php artisan test
```

Prueba de estrés: ver `docs/PRUEBA_DE_ESTRES.md` (`kendex:load-data` + `tests/stress/k6-kendex.js`).

Usuarios de prueba (contraseña `Kendex#2026`): `admin@solocar.test`, `admin2@solocar.test`, `finanzas@solocar.test`,
`supervisor.operaciones-y-logistica@solocar.test`, `empleado.operaciones-y-logistica@solocar.test`, etc.

## 2. Cómo se cumplen los requisitos

| Área | Dónde está |
|---|---|
| RF-01 a RF-08 autenticación, roles, bloqueo, sesión única | `Http/Controllers/Auth/*`, `Middleware/EnforceSession`, `EnsureRole` |
| RF-09 a RF-14 usuarios, departamentos, perfil, historial | `Livewire/Admin/Users`, `Departments`, `Livewire/Profile` |
| RF-15 a RF-26 solicitudes, comprobantes, filtros, visibilidad | `Livewire/Expenses/*`, `Support/ExpenseFilter`, `Models/Expense::scopeVisibleTo`, `Services/ReceiptStorage` |
| RF-27 a RF-38 aprobaciones, escalamiento, auditoría, recordatorios | `Services/ExpenseService`, `Policies/ExpensePolicy`, `Console/Commands/RemindPending` |
| RF-39 a RF-44 pagos | `ExpenseService::pay` |
| RF-45 a RF-55 categorías, presupuestos y proyectos | `Livewire/Admin/Categories`, `Budgets`, `Projects`, `Services/BudgetService` |
| RF-56 a RF-66 dashboard y reportes | `Livewire/Dashboard`, `Http/Controllers/ReportController`, `js/charts.js` |
| RF-67 a RF-74 alertas y reglas | `BudgetService::evaluateThresholds`, `Livewire/Admin/Rules`, `Support/Settings` |
| RF-75 a RF-82 notificaciones | `Services/NotificationService`, `Livewire/Notifications/Bell` |
| RN-01 a RN-12 | `Enums/ExpenseStatus` (flujo), `ExpensePolicy` (RN-05, RN-07), `Models/AuditLog` + trigger PostgreSQL (RN-10) |
| RNF-03/04/05/11 | `bootstrap/app.php`, `LoginController`, `ReceiptStorage`, `Models/Concerns/LogsChanges` |
| RNF-09/10, RT-04 a RT-12 | `deploy/*`, `docker-compose.yml`, `.github/workflows/ci.yml` |

### Valores configurables (Admin > Reglas y alertas)
Los valores por defecto siguen las reglas de SOLOCAR, **no** el documento de requisitos: comprobante desde RD$1,000
(documento: RD$500), alertas al 80 % y 90 % (documento: 75/85/95 — activa la preventiva en el 75 % y ajusta los demás),
segunda aprobación sobre RD$15,000, gastos de máximo 7 días de antigüedad.

### Reglas de negocio que conviene conocer
- Un Supervisor o Administrador **no puede aprobar su propia solicitud**; las de Supervisores las revisa un Administrador y
  las de un Administrador, otro Administrador (por eso el seeder crea dos).
- «Comprometido» se registra en el período del mes en que se aprueba; el pago lo mueve a «Ejecutado» en ese mismo período.
- Solo **Finanzas** paga (también el Administrador queda fuera, por RN-07).
- Las fechas se guardan en UTC y se muestran en America/Santo_Domingo.

## 3. Checklist de lanzamiento web (20 puntos)

| # | Punto | Implementación |
|---|---|---|
| 1 | Política de privacidad | `/privacidad` (`resources/views/legal/privacy.blade.php`) |
| 2 | Términos y condiciones | `/terminos` |
| 3 | API y secretos | Todo en `.env`; ningún secreto en el código; `.env` ya está en `.gitignore` |
| 4 | Forzar HTTPS | `URL::forceScheme` en producción, HSTS (`SecurityHeaders`), redirección 80→443 en `deploy/nginx.conf` |
| 5 | Cookie banner | `components/cookie-banner.blade.php` + `js/consent.js` |
| 6 | Metadescripciones | `components/seo.blade.php` (recorta a 158 caracteres) |
| 7 | Vista previa al compartir | Open Graph + Twitter Card, `public/og-image.png` (1200×630) |
| 8 | Favicon | `favicon.svg`, `favicon.ico`, `apple-touch-icon.png` |
| 9 | Sitemap y robots.txt | `/sitemap.xml` y `/robots.txt` dinámicos (`SeoController`) |
| 10 | ALT en imágenes | `<x-img>` exige `alt`, `width` y `height` |
| 11 | Comprimir imágenes | Comprobantes: redimensionado a 1600 px + calidad 80 al subir. Recursos del sitio: SVG/PNG ligeros |
| 12 | Velocidad de carga | Vite, caché de rutas/config/vistas en `deploy.sh`, caché Nginx, Redis, `loading="lazy"` |
| 13 | Contraste de color | Paleta verificada para WCAG 2.1 AA, foco visible, `prefers-reduced-motion` |
| 14 | Web en móvil | Diseño adaptativo con menú colapsable y tablas con scroll horizontal |
| 15 | Página 404 | `resources/views/errors/404.blade.php` (también 403, 419, 429, 500, 503) |
| 16 | Enlaces rotos | `php artisan kendex:check-links --base=https://tu-dominio` |
| 17 | Validar formularios | Reglas del servidor con mensajes en español, `aria-invalid` y `role="alert"` |
| 18 | SPAM safe | Honeypot + tiempo mínimo (`Middleware/Honeypot`), límite de intentos |
| 19 | Analytics GA4 | `GA4_MEASUREMENT_ID` en `.env`; solo se carga si la persona acepta las cookies |
| 20 | CTA claro | Landing con «Iniciar sesión» / «Crear una cuenta» |

Revisa con un abogado los textos legales antes de publicar: son plantillas basadas en la Ley 172-13.
Mide el rendimiento real (Lighthouse/PageSpeed) una vez desplegado; no se pudo medir aquí.

## 4. Pendiente / limitaciones conocidas
- **Sin ejecutar.** Corre `php artisan test` y navega cada pantalla; puede haber errores de sintaxis o de sintaxis de Livewire.
- **Cobertura de pruebas:** hay ~45 pruebas (dominio, seguridad, pantallas Livewire, comandos, exportaciones). No se midió la cobertura:
  corre `php artisan test --coverage` y, si supera 70 %, sube `COVERAGE_MIN` en el CI (RT-09).
- **RF-55:** presupuesto por subcategoría (categorías con padre) y por proyecto (tope total acumulado, no mensual).
- **RF-12:** varios supervisores por departamento funcionan con usuarios Supervisor del mismo departamento; `departments.supervisor_id` es el principal.
- **RF-75 «tiempo real»:** se hace con sondeo cada 20 s (`wire:poll`). Para tiempo real verdadero, agrega Laravel Reverb.
- **RF-65:** el PDF incluye resumen por departamento y categoría; el Excel trae las hojas Detalle y Resumen.
- **Caché del dashboard:** usa etiquetas de caché (`Cache::tags`), que requieren Redis, Memcached o `array` (no `database` ni `file`).
- **Registro abierto:** `KENDEX_REGISTRATION=false` lo desactiva (los usuarios nuevos quedan como Empleado sin departamento).
- **Argon2id:** para usarlo en lugar de Bcrypt pon `HASH_DRIVER=argon2id` en `.env`.
- **Backups S3:** `deploy/backup.sh` respalda comprobantes en disco local; con S3 usa versionado/réplica del bucket.
