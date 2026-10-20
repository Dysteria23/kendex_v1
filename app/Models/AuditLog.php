<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/** Inalterable (RN-10): no admite update ni delete, ni desde el modelo ni desde PostgreSQL. */
class AuditLog extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['details' => 'array', 'created_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::creating(fn (self $log) => $log->created_at ??= now('UTC'));
        static::updating(fn () => throw new LogicException('El registro de auditoría es inalterable.'));
        static::deleting(fn () => throw new LogicException('El registro de auditoría es inalterable.'));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
