# Prueba de estrés de Kendex

Objetivo (requisitos): **200 usuarios concurrentes** sin degradación (RNF-06) y **95 % de las peticiones en menos de 2 s** (RNF-01).

## Qué mide y qué no
Mide lectura intensiva autenticada: inicio de sesión, panel (con consultas agregadas), listados con filtros y paginación,
formulario y perfil. **No** simula escrituras masivas (crear/aprobar solicitudes), porque eso pasa por Livewire y exige
reproducir sus peticiones internas. Las escrituras concurrentes se protegen con bloqueo de filas (`lockForUpdate`) y se
cubren con las pruebas automáticas, pero no se probaron bajo concurrencia real.

## Pasos
1. **Usa un servidor de pruebas, no producción**, con las mismas especificaciones (mínimo 2 vCPU / 4 GB RAM).
2. En su `.env`: `APP_ENV=staging`, `APP_DEBUG=false`, `KENDEX_HONEYPOT=false`, Redis para sesión/caché/colas, `LOG_LEVEL=warning`.
3. Instala k6: https://grafana.com/docs/k6/latest/set-up/install-k6/
4. Prepara los datos: `bash tests/stress/preflight.sh` (crea 200 usuarios `carga1..200@solocar.test` y 5,000 solicitudes).
5. Ejecuta: `k6 run -e BASE_URL=https://tu-servidor tests/stress/k6-kendex.js`
6. Para más usuarios: `php artisan kendex:load-data --users=500` y `k6 run -e USERS=500 ...`

## Cómo leer el resultado
| Métrica k6 | Meta |
|---|---|
| `http_req_duration` p(95) | < 2000 ms |
| `http_req_failed` | < 1 % |
| `kendex_dashboard_ms` p(95) | idealmente < 1500 ms |
| `checks` | > 99 % |

k6 marca `✓` o `✗` en cada umbral al final de la ejecución.

## Si no se cumple la meta (en este orden)
1. Verifica `php artisan config:cache`, `route:cache`, `view:cache` y `npm run build`; confirma que OPcache está activo.
2. **PHP-FPM:** sube `pm.max_children` (con 4 GB y ~60 MB por proceso: unos 40) y usa `pm = dynamic`.
3. **PostgreSQL:** revisa conexiones (`max_connections`), y con `EXPLAIN ANALYZE` los listados y el panel.
4. **Panel:** el caché de métricas dura 60 s por usuario y filtros; súbelo a 120–300 s en `Dashboard::render` si hace falta.
5. **Sesiones y caché** en Redis (no en archivo ni base de datos).
6. Si el cuello es el CPU, escala a 4 vCPU antes de optimizar más código.

## Errores comunes durante la prueba
- *login redirige al panel falla en todos:* el honeypot sigue activo o el CSRF no se lee; revisa `KENDEX_HONEYPOT=false`.
- *Muchos 419:* el dominio/`SESSION_DOMAIN` o el esquema http/https no coinciden con `BASE_URL`.
- *Muchos 429:* el límite de 5 intentos fallidos por IP se activó; solo ocurre si el login falla (por ejemplo, usuarios no creados).
