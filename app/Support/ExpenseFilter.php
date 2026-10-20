<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

/** Filtros combinados de solicitudes (RF-20, RF-21, RF-66). */
class ExpenseFilter
{
    public static function apply(Builder $query, array $f): Builder
    {
        return $query
            ->when($f['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($f['category_id'] ?? null, fn ($q, $v) => $q->where('category_id', $v))
            ->when($f['project_id'] ?? null, fn ($q, $v) => $q->where('project_id', $v))
            ->when($f['department_id'] ?? null, fn ($q, $v) => $q->where('department_id', $v))
            ->when($f['from'] ?? null, fn ($q, $v) => $q->whereDate('expense_date', '>=', $v))
            ->when($f['to'] ?? null, fn ($q, $v) => $q->whereDate('expense_date', '<=', $v))
            ->when(($f['min'] ?? '') !== '' && is_numeric($f['min'] ?? null), fn ($q) => $q->where('amount', '>=', (float) $f['min']))
            ->when(($f['max'] ?? '') !== '' && is_numeric($f['max'] ?? null), fn ($q) => $q->where('amount', '<=', (float) $f['max']))
            ->when($f['fiscal'] ?? null, fn ($q, $v) => $v === 'con_ncf' ? $q->whereNotNull('ncf') : $q->whereNull('ncf'))
            ->when($f['search'] ?? null, function ($q, $v) {
                $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], mb_strtolower(trim($v))) . '%';
                $q->where(function ($w) use ($like) {
                    $w->whereRaw('LOWER(description) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(number) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(rnc_provider) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(ncf) LIKE ?', [$like])
                        ->orWhereHas('user', fn ($u) => $u->whereRaw('LOWER(name) LIKE ?', [$like]));
                });
            });
    }
}
