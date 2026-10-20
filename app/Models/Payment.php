<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    public $timestamps = false;

    protected $fillable = ['expense_id', 'method', 'reference', 'paid_at', 'recorded_by'];

    protected function casts(): array
    {
        return ['method' => PaymentMethod::class, 'paid_at' => 'date'];
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
