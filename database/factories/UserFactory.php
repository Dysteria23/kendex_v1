<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => Role::Employee,
            'active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    public function role(Role $role): static
    {
        return $this->state(fn () => ['role' => $role]);
    }

    public function inDepartment(Department $department): static
    {
        return $this->state(fn () => ['department_id' => $department->id]);
    }
}
