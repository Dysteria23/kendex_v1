<?php

namespace App\Models;

use App\Models\Concerns\LogsChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Budget extends Model
{
    use LogsChanges;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'amount_assigned' => 'decimal:2',
            'amount_committed' => 'decimal:2',
            'amount_executed' => 'decimal:2',
            'closed' => 'boolean',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function getAvailableAttribute(): float
    {
        return (float) $this->amount_assigned - (float) $this->amount_committed - (float) $this->amount_executed;
    }

    /** % de ejecución = (comprometido + ejecutado) / asignado (RF-51). */
    public function getUsedPercentAttribute(): float
    {
        $assigned = (float) $this->amount_assigned;

        return $assigned > 0
            ? round((((float) $this->amount_committed + (float) $this->amount_executed) / $assigned) * 100, 1)
            : 0.0;
    }

    public function getPeriodAttribute(): string
    {
        return sprintf('%04d-%02d', $this->year, $this->month);
    }
}
