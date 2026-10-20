<?php

namespace App\Livewire\Admin;

use App\Services\AuditLogger;
use App\Support\Settings;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Title;
use Livewire\Component;

/** RF-74: umbrales y reglas de negocio configurables. */
#[Title('Reglas y alertas')]
class Rules extends Component
{
    public const FIELDS = [
        'supervisor_threshold' => ['Monto que exige segunda aprobación del administrador (RD$)', 'numeric|min:0'],
        'receipt_threshold' => ['Monto desde el cual el comprobante es obligatorio (RD$)', 'numeric|min:0'],
        'max_age_days' => ['Antigüedad máxima de un gasto en días (0 = sin límite)', 'integer|min:0|max:365'],
        'pending_reminder_days' => ['Días pendientes antes de enviar un recordatorio (0 = sin recordatorios)', 'integer|min:0|max:60'],
        'max_failed_attempts' => ['Intentos fallidos antes de bloquear una cuenta', 'integer|min:3|max:20'],
        'lockout_minutes' => ['Minutos de bloqueo de una cuenta', 'integer|min:1|max:1440'],
        'block_enabled' => ['Bloquear solicitudes cuando se agota el presupuesto (1 = sí, 0 = no)', 'integer|in:0,1'],
        'block_at_percent' => ['Porcentaje del presupuesto en que se bloquea', 'numeric|min:1|max:200'],
        'alert_preventive' => ['Alerta preventiva al llegar a (%, 0 = desactivada)', 'integer|min:0|max:100'],
        'alert_warning' => ['Alerta de advertencia al llegar a (%)', 'integer|min:0|max:100'],
        'alert_critical' => ['Alerta crítica al llegar a (%)', 'integer|min:0|max:100'],
    ];

    public array $values = [];

    public function mount(): void
    {
        foreach (array_keys(self::FIELDS) as $key) {
            $this->values[$key] = (string) Settings::get($key);
        }
    }

    public function save(): void
    {
        $rules = [];
        foreach (self::FIELDS as $key => [$label, $rule]) {
            $rules["values.$key"] = 'required|' . $rule;
        }
        $attributes = collect(self::FIELDS)->mapWithKeys(fn ($f, $k) => ["values.$k" => mb_strtolower(mb_substr($f[0], 0, 40))])->all();

        $this->validate($rules, [], $attributes);

        foreach ($this->values as $key => $value) {
            Settings::set($key, $value);
        }

        AuditLogger::log('settings.update', 'Setting', null, $this->values);
        Cache::tags(['metrics'])->flush();
        session()->flash('status', 'Reglas guardadas.');
    }

    public function render()
    {
        return view('livewire.admin.rules', ['fields' => self::FIELDS]);
    }
}
