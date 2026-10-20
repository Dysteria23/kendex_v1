<?php

namespace App\Services;

use App\Models\AuditLog;

class AuditLogger
{
    public static function log(string $action, string $entity, ?int $entityId = null, array $details = [], ?int $userId = null): void
    {
        AuditLog::create([
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'entity' => $entity,
            'entity_id' => $entityId,
            'ip_address' => request()?->ip(),
            'details' => $details ?: null,
        ]);
    }
}
