<?php

return [
    'company' => [
        'name' => env('KENDEX_COMPANY', 'SOLOCAR, S.R.L.'),
        'email' => env('KENDEX_CONTACT_EMAIL', 'privacidad@solocar.test'),
        'address' => env('KENDEX_ADDRESS', 'Zona Industrial de Herrera, Santo Domingo, República Dominicana'),
    ],

    // Zona horaria de presentación y de cálculo de fechas de negocio (RNF-13).
    // La base de datos guarda todo en UTC (APP_TIMEZONE=UTC).
    'timezone' => env('KENDEX_TIMEZONE', 'America/Santo_Domingo'),

    'registration_enabled' => env('KENDEX_REGISTRATION', true),
    'honeypot' => env('KENDEX_HONEYPOT', true),
    'ga4_id' => env('GA4_MEASUREMENT_ID'),

    // Valores por defecto de las reglas de negocio. El Administrador puede
    // sobrescribirlos desde Admin > Reglas (tabla settings) — RF-74.
    'defaults' => [
        'supervisor_threshold' => 15000, // sobre este monto se exige segunda aprobación del Admin
        'receipt_threshold' => 1000,     // comprobante obligatorio sobre este monto
        'max_age_days' => 7,             // antigüedad máxima del gasto (0 = sin límite)
        'max_failed_attempts' => 5,      // bloqueo de cuenta
        'lockout_minutes' => 15,
        'pending_reminder_days' => 3,
        'block_enabled' => 1,
        'block_at_percent' => 100,
        'alert_preventive' => 0,         // 0 = desactivada (el documento sugiere 75)
        'alert_warning' => 80,           // documento: 85
        'alert_critical' => 90,          // documento: 95
    ],

    'receipts' => [
        'disk' => env('KENDEX_RECEIPTS_DISK', 'local'),
        'max_kb' => 10240,
        'image_max_px' => 1600,
        'image_quality' => 80,
    ],
];
