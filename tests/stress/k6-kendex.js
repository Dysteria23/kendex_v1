// Prueba de estrés de Kendex con k6  (https://k6.io)
//
//   k6 run -e BASE_URL=http://localhost:8000 tests/stress/k6-kendex.js
//   k6 run -e BASE_URL=https://staging.kendex.example.com -e USERS=200 tests/stress/k6-kendex.js
//
// Requisitos previos (ver docs/PRUEBA_DE_ESTRES.md):
//   php artisan kendex:load-data --users=200 --expenses=5000
//   .env del entorno de prueba con KENDEX_HONEYPOT=false
//
// Cada usuario virtual inicia sesión con su propia cuenta (carga1..N@solocar.test), porque Kendex
// permite una sola sesión activa por usuario (RF-08).
import http from 'k6/http';
import { check, group, sleep } from 'k6';
import { Trend } from 'k6/metrics';

const BASE = (__ENV.BASE_URL || 'http://localhost:8000').replace(/\/$/, '');
const USERS = parseInt(__ENV.USERS || '200', 10);
const PASSWORD = __ENV.PASSWORD || 'Kendex#2026';

const loginTime = new Trend('kendex_login_ms', true);
const dashboardTime = new Trend('kendex_dashboard_ms', true);
const listTime = new Trend('kendex_list_ms', true);

export const options = {
  scenarios: {
    carga: {
      executor: 'ramping-vus',
      startVUs: 0,
      stages: [
        { duration: '1m', target: 50 },   // calentamiento
        { duration: '2m', target: USERS }, // subida hasta 200 usuarios concurrentes (RNF-06)
        { duration: '5m', target: USERS }, // meseta sostenida
        { duration: '1m', target: 0 },    // bajada
      ],
      gracefulRampDown: '30s',
    },
  },
  thresholds: {
    http_req_duration: ['p(95)<2000'],   // RNF-01: 95 % de las peticiones en menos de 2 s
    http_req_failed: ['rate<0.01'],      // menos de 1 % de errores
    checks: ['rate>0.99'],
  },
};

function csrf(res) {
  const el = res.html().find('input[name=_token]').first();
  return el.size() ? el.attr('value') : null;
}

export default function () {
  const n = ((__VU - 1) % USERS) + 1;
  const email = `carga${n}@solocar.test`;

  let token;
  group('login', () => {
    const page = http.get(`${BASE}/login`);
    token = csrf(page);
    const res = http.post(`${BASE}/login`, { _token: token, email, password: PASSWORD });
    loginTime.add(res.timings.duration);
    check(res, {
      'login redirige al panel': (r) => r.status === 200 && r.url.includes('/panel'),
    });
  });

  group('panel', () => {
    const res = http.get(`${BASE}/panel`);
    dashboardTime.add(res.timings.duration);
    check(res, { 'panel 200': (r) => r.status === 200 && r.body.includes('Panel') });
    token = csrf(res) || token;
  });
  sleep(Math.random() * 2 + 1);

  group('listados', () => {
    for (const path of ['/gastos', '/gastos?status=pendiente', '/gastos?search=materiales', '/gastos?page=2']) {
      const res = http.get(`${BASE}${path}`);
      listTime.add(res.timings.duration);
      check(res, { [`${path} 200`]: (r) => r.status === 200 });
      sleep(Math.random() + 0.5);
    }
  });

  group('otras pantallas', () => {
    for (const path of ['/gastos/nuevo', '/perfil']) {
      check(http.get(`${BASE}${path}`), { [`${path} 200`]: (r) => r.status === 200 });
    }
  });

  group('cierre de sesión', () => {
    if (token) {
      const res = http.post(`${BASE}/logout`, { _token: token });
      check(res, { 'logout ok': (r) => r.status === 200 });
    }
  });

  sleep(1);
}
