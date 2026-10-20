<?php

namespace App\Models;

use App\Models\Concerns\LogsChanges;
use Database\Factories\DepartmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    /** @use HasFactory<DepartmentFactory> */
    use HasFactory, LogsChanges;

    protected $fillable = ['name', 'budget_monthly', 'supervisor_id', 'active'];

    protected function casts(): array
    {
        return ['budget_monthly' => 'decimal:2', 'active' => 'boolean'];
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }
}
