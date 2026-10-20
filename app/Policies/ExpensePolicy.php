<?php

namespace App\Policies;

use App\Enums\ExpenseStatus;
use App\Enums\Role;
use App\Models\Expense;
use App\Models\User;

class ExpensePolicy
{
    public function view(User $user, Expense $expense): bool
    {
        return match ($user->role) {
            Role::Admin => true,
            Role::Supervisor => $expense->user_id === $user->id
                || in_array($expense->department_id, $user->supervisedDepartmentIds(), true),
            Role::Finance => $expense->user_id === $user->id
                || in_array($expense->status, [ExpenseStatus::Approved, ExpenseStatus::Paid], true),
            default => $expense->user_id === $user->id,
        };
    }

    /** RF-17 */
    public function update(User $user, Expense $expense): bool
    {
        return $expense->user_id === $user->id
            && in_array($expense->status, [ExpenseStatus::Pending, ExpenseStatus::ChangesRequested], true);
    }

    /** RF-18 */
    public function cancel(User $user, Expense $expense): bool
    {
        return $expense->user_id === $user->id && $expense->status === ExpenseStatus::Pending;
    }

    /** Aprobar, rechazar o pedir modificación. Nadie revisa su propia solicitud (RN-05). */
    public function review(User $user, Expense $expense): bool
    {
        if (! $user->active || $expense->status !== ExpenseStatus::Pending || $expense->user_id === $user->id) {
            return false;
        }

        return match ($user->role) {
            Role::Admin => true,
            Role::Supervisor => $expense->approval_stage === 'supervisor'
                && in_array($expense->department_id, $user->supervisedDepartmentIds(), true),
            default => false,
        };
    }

    public function comment(User $user, Expense $expense): bool
    {
        return $this->view($user, $expense)
            && in_array($user->role, [Role::Supervisor, Role::Finance, Role::Admin], true)
            && $expense->status !== ExpenseStatus::Cancelled;
    }

    /** RN-07: solo Finanzas, solo sobre solicitudes aprobadas. */
    public function pay(User $user, Expense $expense): bool
    {
        return $user->active && $user->role === Role::Finance && $expense->status === ExpenseStatus::Approved;
    }
}
