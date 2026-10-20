<?php

namespace Database\Factories;

use App\Enums\ExpenseStatus;
use App\Models\Category;
use App\Models\Department;
use App\Models\User;
use App\Support\Dates;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    public function definition(): array
    {
        $amount = fake()->randomFloat(2, 100, 900);

        return [
            'user_id' => User::factory(),
            'department_id' => Department::factory(),
            'category_id' => Category::factory(),
            'amount' => $amount,
            'itbis_amount' => 0,
            'subtotal' => $amount,
            'description' => fake()->sentence(),
            'expense_date' => Dates::today(),
            'status' => ExpenseStatus::Pending,
            'approval_stage' => 'supervisor',
        ];
    }
}
