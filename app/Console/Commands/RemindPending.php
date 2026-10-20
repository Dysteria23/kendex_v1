<?php

namespace App\Console\Commands;

use App\Enums\ExpenseStatus;
use App\Models\Expense;
use App\Services\NotificationService;
use App\Support\Settings;
use Illuminate\Console\Command;

class RemindPending extends Command
{
    protected $signature = 'kendex:remind-pending';
    protected $description = 'Recuerda las solicitudes que llevan más de X días en estado Pendiente (RF-38)';

    public function handle(): int
    {
        $days = (int) Settings::get('pending_reminder_days');
        if ($days <= 0) {
            return self::SUCCESS;
        }

        $cutoff = now()->subDays($days);
        $count = 0;

        Expense::with('user')
            ->where('status', ExpenseStatus::Pending->value)
            ->where('created_at', '<=', $cutoff)
            ->where(fn ($q) => $q->whereNull('last_reminded_at')->orWhere('last_reminded_at', '<=', $cutoff))
            ->each(function (Expense $expense) use ($days, &$count) {
                $recipients = $expense->approval_stage === 'admin'
                    ? NotificationService::admins()
                    : NotificationService::supervisorsOf($expense->department_id);

                NotificationService::send(
                    $recipients->reject(fn ($u) => $u->id === $expense->user_id),
                    'expense.reminder',
                    "La solicitud {$expense->number} de {$expense->user->name} lleva más de {$days} días pendiente de revisión.",
                    '/gastos/' . $expense->id
                );

                $expense->forceFill(['last_reminded_at' => now()])->saveQuietly();
                $count++;
            });

        $this->info("Recordatorios enviados: {$count}");

        return self::SUCCESS;
    }
}
