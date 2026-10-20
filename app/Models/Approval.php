<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Approval extends Model
{
    const UPDATED_AT = null;

    protected $fillable = ['expense_id', 'user_id', 'action', 'comment'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    public function actionLabel(): string
    {
        return match ($this->action) {
            'approve' => 'Aprobó',
            'approve_first' => 'Aprobó (primera aprobación)',
            'reject' => 'Rechazó',
            'modify' => 'Solicitó modificación',
            'cancel' => 'Anuló',
            'comment' => 'Comentó',
            'resubmit' => 'Reenvió corregida',
            'pay' => 'Registró el pago',
            default => $this->action,
        };
    }
}
