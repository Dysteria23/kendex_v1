<?php

namespace App\Services;

use App\Enums\ExpenseStatus;
use App\Enums\PaymentMethod;
use App\Enums\Role;
use App\Models\Approval;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\User;
use App\Support\Dates;
use App\Support\Money;
use App\Support\Settings;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

/** Ciclo de vida de una solicitud: crear, editar, aprobar, rechazar, pedir cambios, anular y pagar. */
class ExpenseService
{
    public function __construct(
        protected BudgetService $budgets,
        protected ReceiptStorage $receipts,
    ) {}

    public function create(User $user, array $data, array $files = []): Expense
    {
        return DB::transaction(function () use ($user, $data, $files) {
            $departmentId = $this->resolveDepartment($user, $data);
            $this->assertBudget($departmentId, (int) $data['category_id'], (float) $data['amount'], $this->projectId($data));

            $expense = new Expense($this->fields($data));
            $expense->user_id = $user->id;
            $expense->department_id = $departmentId;
            $expense->status = ExpenseStatus::Pending;
            $expense->approval_stage = $this->initialStage($user);
            $expense->receipt_path = $this->receipts->storeMany($files);
            $expense->save();

            $expense->update(['number' => sprintf('GS-%s-%06d', Dates::now()->format('Y'), $expense->id)]);

            $this->notifyReviewers($expense, 'expense.created');
            $this->flush();

            return $expense;
        });
    }

    /**
     * Edita una solicitud en estado Pendiente o Modificación solicitada (RF-17).
     * Si estaba en Modificación solicitada, queda reenviada (RF-34).
     *
     * @param  array  $keep  comprobantes existentes que se conservan
     */
    public function update(User $user, Expense $expense, array $data, array $newFiles = [], ?array $keep = null): Expense
    {
        $removed = [];

        $expense = DB::transaction(function () use ($user, $expense, $data, $newFiles, $keep, &$removed) {
            $expense = Expense::whereKey($expense->id)->lockForUpdate()->firstOrFail();
            Gate::forUser($user)->authorize('update', $expense);

            $this->assertBudget($expense->department_id, (int) $data['category_id'], (float) $data['amount'], $this->projectId($data));

            $current = $expense->receipts();
            $kept = $keep ?? $current;
            $keptPaths = array_column($kept, 'path');
            $removed = array_values(array_filter($current, fn ($r) => ! in_array($r['path'], $keptPaths, true)));

            $wasChangesRequested = $expense->status === ExpenseStatus::ChangesRequested;

            $expense->fill($this->fields($data));
            $expense->receipt_path = array_merge(array_values($kept), $this->receipts->storeMany($newFiles));
            $expense->approval_stage = $this->initialStage($user);

            if ($wasChangesRequested) {
                $this->moveTo($expense, ExpenseStatus::Pending);
            }

            $expense->save();

            if ($wasChangesRequested) {
                $this->record($expense, $user, 'resubmit');
                $this->notifyReviewers($expense, 'expense.resubmitted');
            }

            $this->flush();

            return $expense;
        });

        $this->receipts->delete($removed);

        return $expense;
    }

    /** Aprobación, con segunda aprobación del Administrador sobre el umbral (RF-31, RF-35, RF-72). */
    public function approve(User $by, Expense $expense, ?string $comment = null): Expense
    {
        return DB::transaction(function () use ($by, $expense, $comment) {
            $expense = $this->lock($expense);
            Gate::forUser($by)->authorize('review', $expense);

            $needsSecondApproval = $expense->approval_stage === 'supervisor'
                && $by->role === Role::Supervisor
                && (float) $expense->amount > (float) Settings::get('supervisor_threshold');

            if ($needsSecondApproval) {
                $expense->update(['approval_stage' => 'admin']);
                $this->record($expense, $by, 'approve_first', $comment);
                $this->notifyReviewers($expense, 'expense.escalated');
                $this->flush();

                return $expense;
            }

            $reason = $this->budgets->blockingReason(
                $expense->department_id,
                $expense->category_id,
                (float) $expense->amount,
                null,
                $expense->project_id
            );
            if ($reason) {
                throw ValidationException::withMessages(['budget' => $reason]);
            }

            $this->moveTo($expense, ExpenseStatus::Approved);
            $expense->save();
            $this->budgets->commit($expense);
            $this->record($expense, $by, 'approve', $comment);

            NotificationService::send([$expense->user], 'expense.approved', "Tu solicitud {$expense->number} fue aprobada.", $this->url($expense));
            NotificationService::send(NotificationService::finance(), 'expense.to_pay',
                "La solicitud {$expense->number} por " . Money::format($expense->amount) . ' está lista para pago.', $this->url($expense));

            $this->flush();

            return $expense;
        });
    }

    public function reject(User $by, Expense $expense, string $reason): Expense
    {
        return $this->decide($by, $expense, ExpenseStatus::Rejected, 'reject', $reason, 'expense.rejected',
            'fue rechazada');
    }

    public function requestChanges(User $by, Expense $expense, string $instructions): Expense
    {
        return $this->decide($by, $expense, ExpenseStatus::ChangesRequested, 'modify', $instructions, 'expense.changes',
            'requiere modificaciones');
    }

    protected function decide(User $by, Expense $expense, ExpenseStatus $to, string $action, string $comment, string $type, string $verb): Expense
    {
        if (blank($comment)) {
            throw ValidationException::withMessages(['comment' => 'Escribe el motivo o las indicaciones.']);
        }

        return DB::transaction(function () use ($by, $expense, $to, $action, $comment, $type, $verb) {
            $expense = $this->lock($expense);
            Gate::forUser($by)->authorize('review', $expense);

            $this->moveTo($expense, $to);
            $expense->save();
            $this->record($expense, $by, $action, $comment);

            NotificationService::send([$expense->user], $type, "Tu solicitud {$expense->number} {$verb}: {$comment}", $this->url($expense));
            $this->flush();

            return $expense;
        });
    }

    /** Anulación por el dueño, solo en estado Pendiente (RF-18). */
    public function cancel(User $by, Expense $expense): Expense
    {
        return DB::transaction(function () use ($by, $expense) {
            $expense = $this->lock($expense);
            Gate::forUser($by)->authorize('cancel', $expense);

            $this->moveTo($expense, ExpenseStatus::Cancelled);
            $expense->save();
            $this->record($expense, $by, 'cancel');
            $this->flush();

            return $expense;
        });
    }

    /** Observaciones de Supervisor, Finanzas y Admin (RF-30). */
    public function comment(User $by, Expense $expense, string $text): void
    {
        if (blank($text)) {
            throw ValidationException::withMessages(['comment' => 'Escribe un comentario.']);
        }

        Gate::forUser($by)->authorize('comment', $expense);
        $this->record($expense, $by, 'comment', $text);

        if ($expense->user_id !== $by->id) {
            NotificationService::send([$expense->user], 'expense.comment', "Nuevo comentario en {$expense->number}: {$text}", $this->url($expense));
        }
    }

    /** Solo Finanzas registra pagos (RN-07, RF-39 a RF-44). */
    public function pay(User $by, Expense $expense, array $data): Expense
    {
        $method = PaymentMethod::tryFrom($data['method'] ?? '');
        if (! $method) {
            throw ValidationException::withMessages(['method' => 'Selecciona un método de pago.']);
        }
        if ($method->requiresReference() && blank($data['reference'] ?? null)) {
            throw ValidationException::withMessages(['reference' => 'Indica la referencia o número de transacción.']);
        }

        return DB::transaction(function () use ($by, $expense, $data, $method) {
            $expense = $this->lock($expense);
            Gate::forUser($by)->authorize('pay', $expense);

            Payment::create([
                'expense_id' => $expense->id,
                'method' => $method,
                'reference' => $data['reference'] ?? null,
                'paid_at' => $data['paid_at'] ?? Dates::today(),
                'recorded_by' => $by->id,
            ]);

            $this->moveTo($expense, ExpenseStatus::Paid);
            $expense->save();
            $this->budgets->execute($expense);
            $this->record($expense, $by, 'pay', $data['reference'] ?? null);

            NotificationService::send([$expense->user], 'expense.paid', "Tu solicitud {$expense->number} fue pagada.", $this->url($expense));
            $this->flush();

            return $expense;
        });
    }

    // ---------------------------------------------------------------- helpers

    protected function lock(Expense $expense): Expense
    {
        return Expense::whereKey($expense->id)->lockForUpdate()->firstOrFail();
    }

    /** Aplica una transición validada por el flujo estricto (RN-06, RN-08). */
    protected function moveTo(Expense $expense, ExpenseStatus $to): void
    {
        if (! $expense->status->canTransitionTo($to)) {
            throw ValidationException::withMessages([
                'status' => "No se puede pasar de «{$expense->status->label()}» a «{$to->label()}».",
            ]);
        }

        $expense->status = $to;
    }

    /** Los gastos de Supervisores y Administradores los revisa un Administrador (RN-05). */
    protected function initialStage(User $creator): string
    {
        return in_array($creator->role, [Role::Supervisor, Role::Admin], true) ? 'admin' : 'supervisor';
    }

    protected function resolveDepartment(User $user, array $data): int
    {
        $departmentId = ($user->isAdmin() && ! empty($data['department_id']))
            ? (int) $data['department_id']
            : $user->department_id;

        if (! $departmentId) {
            throw ValidationException::withMessages([
                'department_id' => 'Aún no tienes un departamento asignado. Pídele al administrador que te asigne uno.',
            ]);
        }

        return $departmentId;
    }

    protected function projectId(array $data): ?int
    {
        return filled($data['project_id'] ?? null) ? (int) $data['project_id'] : null;
    }

    protected function assertBudget(int $departmentId, int $categoryId, float $amount, ?int $projectId = null): void
    {
        if ($reason = $this->budgets->blockingReason($departmentId, $categoryId, $amount, null, $projectId)) {
            throw ValidationException::withMessages(['budget' => $reason]);
        }
    }

    protected function fields(array $d): array
    {
        $amount = round((float) $d['amount'], 2);
        $itbis = round((float) ($d['itbis_amount'] ?? 0), 2);

        return [
            'category_id' => (int) $d['category_id'],
            'project_id' => $this->projectId($d),
            'amount' => $amount,
            'itbis_amount' => $itbis,
            'subtotal' => round($amount - $itbis, 2),
            'rnc_provider' => filled($d['rnc_provider'] ?? null) ? $d['rnc_provider'] : null,
            'ncf' => filled($d['ncf'] ?? null) ? strtoupper($d['ncf']) : null,
            'description' => trim($d['description']),
            'expense_date' => $d['expense_date'],
        ];
    }

    /** Historial de aprobación + auditoría con quién, cuándo y comentarios (RF-36). */
    protected function record(Expense $expense, User $by, string $action, ?string $comment = null): void
    {
        Approval::create([
            'expense_id' => $expense->id,
            'user_id' => $by->id,
            'action' => $action,
            'comment' => $comment,
        ]);

        AuditLogger::log('expense.' . $action, 'Expense', $expense->id,
            array_filter(['number' => $expense->number, 'comment' => $comment]), $by->id);
    }

    protected function notifyReviewers(Expense $expense, string $type): void
    {
        $expense->loadMissing('user');

        $recipients = $expense->approval_stage === 'admin'
            ? NotificationService::admins()
            : NotificationService::supervisorsOf($expense->department_id);

        $recipients = $recipients->reject(fn ($u) => $u->id === $expense->user_id);

        $text = match ($type) {
            'expense.escalated' => "La solicitud {$expense->number} superó el umbral del supervisor y necesita tu aprobación.",
            'expense.resubmitted' => "{$expense->user->name} corrigió y reenvió la solicitud {$expense->number}.",
            default => "{$expense->user->name} creó la solicitud {$expense->number} por " . Money::format($expense->amount) . '.',
        };

        NotificationService::send($recipients, $type, $text, $this->url($expense));
    }

    protected function url(Expense $expense): string
    {
        return '/gastos/' . $expense->id;
    }

    protected function flush(): void
    {
        Cache::tags(['metrics'])->flush();
    }
}
