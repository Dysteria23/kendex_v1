<?php

namespace App\Models;

use App\Models\Concerns\LogsChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use LogsChanges;

    protected $fillable = ['code', 'name', 'department_id', 'budget_total', 'active'];

    protected function casts(): array
    {
        return ['budget_total' => 'decimal:2', 'active' => 'boolean'];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}
