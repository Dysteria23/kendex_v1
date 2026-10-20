<?php

namespace App\Models;

use App\Enums\ExpenseStatus;
use App\Enums\Role;
use App\Models\Concerns\LogsChanges;
use Database\Factories\ExpenseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Expense extends Model
{
    /** @use HasFactory<ExpenseFactory> */
    use HasFactory, LogsChanges;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => ExpenseStatus::class,
            'amount' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'itbis_amount' => 'decimal:2',
            'expense_date' => 'date',
            'receipt_path' => 'array',
            'last_reminded_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(Approval::class)->orderBy('id');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function receipts(): array
    {
        return $this->receipt_path ?? [];
    }

    /** Visibilidad por rol (RF-23 a RF-26). */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return match ($user->role) {
            Role::Admin => $query,
            Role::Supervisor => $query->where(fn ($w) => $w
                ->whereIn('department_id', $user->supervisedDepartmentIds())
                ->orWhere('user_id', $user->id)),
            Role::Finance => $query->where(fn ($w) => $w
                ->whereIn('status', [ExpenseStatus::Approved->value, ExpenseStatus::Paid->value])
                ->orWhere('user_id', $user->id)),
            default => $query->where('user_id', $user->id),
        };
    }

    /** Solicitudes que requieren una acción del usuario (RF-64). */
    public function scopeAwaitingReview(Builder $query, User $user): Builder
    {
        return match ($user->role) {
            Role::Admin => $query->where('status', ExpenseStatus::Pending->value)
                ->where('user_id', '!=', $user->id),
            Role::Supervisor => $query->where('status', ExpenseStatus::Pending->value)
                ->where('approval_stage', 'supervisor')
                ->where('user_id', '!=', $user->id)
                ->whereIn('department_id', $user->supervisedDepartmentIds()),
            Role::Finance => $query->where('status', ExpenseStatus::Approved->value),
            default => $query->whereRaw('1 = 0'),
        };
    }
}
