<?php

use Illuminate\Support\Facades\Schedule;

$tz = config('kendex.timezone', 'America/Santo_Domingo');

Schedule::command('kendex:remind-pending')->dailyAt('08:00')->timezone($tz);   // RF-38, RF-82
Schedule::command('kendex:open-month')->monthlyOn(1, '00:10')->timezone($tz);  // RN-09
Schedule::command('kendex:open-month')->dailyAt('00:20')->timezone($tz);       // por si el día 1 el servidor estuvo apagado
