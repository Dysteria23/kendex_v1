<?php

use App\Enums\Role;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Department;
use App\Models\User;
use App\Support\Dates;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

pest()->extend(Tests\TestCase::class)->use(RefreshDatabase::class)->in('Feature');

/** Escenario base: un departamento con presupuesto, su supervisor, un empleado, Finanzas y dos administradores. */
function kendex(float $budget = 100000): object
{
    Mail::fake();
    config(['kendex.honeypot' => false]);

    $dept = Department::factory()->create();
    $supervisor = User::factory()->role(Role::Supervisor)->inDepartment($dept)->create();
    $dept->update(['supervisor_id' => $supervisor->id]);

    $now = Dates::now();
    Budget::create(['department_id' => $dept->id, 'year' => $now->year, 'month' => $now->month, 'amount_assigned' => $budget]);

    return (object) [
        'dept' => $dept,
        'category' => Category::factory()->create(),
        'supervisor' => $supervisor,
        'employee' => User::factory()->inDepartment($dept)->create(),
        'finance' => User::factory()->role(Role::Finance)->create(),
        'admin' => User::factory()->role(Role::Admin)->create(),
        'admin2' => User::factory()->role(Role::Admin)->create(),
    ];
}

function expenseData(object $ctx, float $amount = 500, array $override = []): array
{
    return array_merge([
        'category_id' => $ctx->category->id,
        'amount' => $amount,
        'itbis_amount' => 0,
        'description' => 'Compra de materiales de prueba',
        'expense_date' => Dates::today(),
    ], $override);
}

function svc(): \App\Services\ExpenseService
{
    return app(\App\Services\ExpenseService::class);
}

function fillForm($component, object $ctx, array $over = [])
{
    $data = array_merge([
        'category_id' => (string) $ctx->category->id,
        'amount' => '500',
        'description' => 'Compra de papel y tinta',
        'expense_date' => Dates::today(),
    ], $over);

    foreach ($data as $k => $v) {
        $component->set($k, $v);
    }

    return $component;
}
