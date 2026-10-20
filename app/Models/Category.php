<?php

namespace App\Models;

use App\Models\Concerns\LogsChanges;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory, LogsChanges;

    protected $fillable = ['name', 'parent_id', 'monthly_limit', 'active'];

    protected function casts(): array
    {
        return ['monthly_limit' => 'decimal:2', 'active' => 'boolean'];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function getFullNameAttribute(): string
    {
        return $this->parent ? $this->parent->name . ' › ' . $this->name : $this->name;
    }
}
