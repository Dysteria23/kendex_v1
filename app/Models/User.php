<?php

namespace App\Models;

use App\Enums\Role;
use App\Models\Concerns\LogsChanges;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, LogsChanges;

    protected $fillable = ['name', 'email', 'password', 'role', 'department_id', 'active', 'phone', 'position'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
            'active' => 'boolean',
            'locked_until' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function supervisedDepartments(): HasMany
    {
        return $this->hasMany(Department::class, 'supervisor_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function hasRole(Role ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isAdmin(): bool
    {
        return $this->role === Role::Admin;
    }

    /** Departamentos propios y los que supervisa explícitamente (RF-12, RF-24). */
    public function supervisedDepartmentIds(): array
    {
        return collect([$this->department_id])
            ->merge($this->supervisedDepartments()->pluck('id'))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
