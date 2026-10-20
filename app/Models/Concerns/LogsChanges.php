<?php

namespace App\Models\Concerns;

use App\Services\AuditLogger;

/** Registra en audit_logs todo create/update/delete del modelo (RNF-11). */
trait LogsChanges
{
    public static function bootLogsChanges(): void
    {
        foreach (['created', 'updated', 'deleted'] as $event) {
            static::$event(function ($model) use ($event) {
                $ignored = ['password', 'remember_token', 'updated_at', 'created_at', 'current_session_id'];

                $details = $event === 'updated'
                    ? collect($model->getChanges())->except($ignored)->all()
                    : collect($model->getAttributes())->except($ignored)->all();

                if ($event === 'updated' && empty($details)) {
                    return;
                }

                AuditLogger::log($event, class_basename($model), $model->getKey(), $details);
            });
        }
    }
}
