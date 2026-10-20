<?php

namespace App\Services;

use App\Enums\Role;
use App\Mail\KendexMail;
use App\Models\Department;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

/** Notificación in-app + correo en paralelo, enviado por cola (RF-37, RF-75, RF-76). */
class NotificationService
{
    public const SUBJECTS = [
        'expense.created' => 'Nueva solicitud de gasto',
        'expense.escalated' => 'Solicitud pendiente de segunda aprobación',
        'expense.approved' => 'Solicitud aprobada',
        'expense.rejected' => 'Solicitud rechazada',
        'expense.changes' => 'Se requiere modificar tu solicitud',
        'expense.resubmitted' => 'Solicitud corregida y reenviada',
        'expense.comment' => 'Nuevo comentario en una solicitud',
        'expense.to_pay' => 'Solicitud aprobada para pago',
        'expense.paid' => 'Solicitud pagada',
        'expense.reminder' => 'Recordatorio: solicitud pendiente',
        'budget.alert' => 'Alerta de presupuesto',
    ];

    public static function send(iterable $users, string $type, string $message, ?string $url = null): void
    {
        $subject = self::SUBJECTS[$type] ?? 'Notificación';

        foreach (collect($users)->filter()->unique('id') as $user) {
            if (! $user->active) {
                continue;
            }

            UserNotification::create([
                'user_id' => $user->id,
                'type' => $type,
                'message' => $message,
                'url' => $url,
            ]);

            Mail::to($user->email)->queue(new KendexMail($subject, $message, $url ? url($url) : null));
        }
    }

    public static function admins(): Collection
    {
        return User::where('active', true)->where('role', Role::Admin->value)->get();
    }

    public static function finance(): Collection
    {
        return User::where('active', true)->where('role', Role::Finance->value)->get();
    }

    public static function supervisorsOf(int $departmentId): Collection
    {
        $supervisorId = Department::whereKey($departmentId)->value('supervisor_id');

        return User::where('active', true)
            ->where(function ($q) use ($departmentId, $supervisorId) {
                $q->where(fn ($w) => $w->where('role', Role::Supervisor->value)->where('department_id', $departmentId));

                if ($supervisorId) {
                    $q->orWhere('id', $supervisorId);
                }
            })
            ->get();
    }
}
