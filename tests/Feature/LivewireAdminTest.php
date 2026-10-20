<?php

use App\Livewire\Admin\AuditTrail;
use App\Livewire\Admin\Budgets;
use App\Livewire\Admin\Categories;
use App\Livewire\Admin\Departments;
use App\Livewire\Admin\Projects;
use App\Livewire\Admin\Rules;
use App\Livewire\Admin\Users;
use App\Livewire\Dashboard;
use App\Livewire\Notifications\Bell;
use App\Livewire\Profile;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Department;
use App\Models\Project;
use App\Models\User;
use App\Models\UserNotification;
use App\Support\Dates;
use App\Support\Settings;
use Livewire\Livewire;

it('el administrador crea y desactiva usuarios', function () {
    $ctx = kendex();

    Livewire::actingAs($ctx->admin)->test(Users::class)
        ->call('create')
        ->set('name', 'Nueva Persona')->set('email', 'nueva@example.com')->set('role', 'supervisor')
        ->set('department_id', (string) $ctx->dept->id)->set('password', 'ClaveSegura123')
        ->call('save')->assertHasNoErrors();

    $user = User::where('email', 'nueva@example.com')->first();
    expect($user)->not->toBeNull()->and($user->role->value)->toBe('supervisor');

    Livewire::actingAs($ctx->admin)->test(Users::class)->call('toggle', $user->id);
    expect($user->fresh()->active)->toBeFalse();
});

it('rechaza contraseñas débiles y correos repetidos', function () {
    $ctx = kendex();

    Livewire::actingAs($ctx->admin)->test(Users::class)->call('create')
        ->set('name', 'X')->set('email', $ctx->employee->email)->set('password', 'corta')
        ->call('save')->assertHasErrors(['email', 'password']);
});

it('el administrador no puede quitarse su propio rol', function () {
    $ctx = kendex();

    Livewire::actingAs($ctx->admin)->test(Users::class)->call('edit', $ctx->admin->id)
        ->set('role', 'empleado')->call('save')->assertHasErrors('role');

    expect($ctx->admin->fresh()->role->value)->toBe('administrador');
});

it('gestiona departamentos, categorías y proyectos', function () {
    $ctx = kendex();

    Livewire::actingAs($ctx->admin)->test(Departments::class)->call('create')
        ->set('name', 'Compras')->set('budget_monthly', '75,000.00')->set('supervisor_id', (string) $ctx->supervisor->id)
        ->call('save')->assertHasNoErrors();
    expect((float) Department::where('name', 'Compras')->value('budget_monthly'))->toBe(75000.0);

    Livewire::actingAs($ctx->admin)->test(Categories::class)->call('create')
        ->set('name', 'Peajes')->set('monthly_limit', '20000')->call('save')->assertHasNoErrors();
    $cat = Category::where('name', 'Peajes')->first();
    Livewire::actingAs($ctx->admin)->test(Categories::class)->call('toggle', $cat->id);
    expect($cat->fresh()->active)->toBeFalse();

    Livewire::actingAs($ctx->admin)->test(Projects::class)->call('create')
        ->set('code', 'p-77')->set('name', 'Flota nueva')->set('budget_total', '900000')->call('save')->assertHasNoErrors();
    expect(Project::where('name', 'Flota nueva')->exists())->toBeTrue();
});

it('abre el período, ajusta montos y respeta períodos cerrados', function () {
    $ctx = kendex();
    $other = Department::factory()->create(['budget_monthly' => 40000]);

    $c = Livewire::actingAs($ctx->admin)->test(Budgets::class)->call('openPeriod');
    expect(Budget::where('department_id', $other->id)->exists())->toBeTrue();

    $b = Budget::where('department_id', $ctx->dept->id)->first();
    $c->set("amounts.{$b->id}", '250,000')->call('saveAmount', $b->id);
    expect((float) $b->fresh()->amount_assigned)->toBe(250000.0);

    $b->update(['closed' => true]);
    Livewire::actingAs($ctx->admin)->test(Budgets::class)->call('saveAmount', $b->id)->assertForbidden();
});

it('guarda las reglas y valida los valores', function () {
    $ctx = kendex();

    Livewire::actingAs($ctx->admin)->test(Rules::class)
        ->set('values.alert_critical', '95')->set('values.receipt_threshold', '500')
        ->call('save')->assertHasNoErrors();
    expect(Settings::get('alert_critical'))->toBe(95)->and(Settings::get('receipt_threshold'))->toBe(500);

    Livewire::actingAs($ctx->admin)->test(Rules::class)->set('values.max_failed_attempts', '1')
        ->call('save')->assertHasErrors('values.max_failed_attempts');
});

it('muestra la auditoría solo al administrador', function () {
    $ctx = kendex();

    Livewire::actingAs($ctx->admin)->test(AuditTrail::class)->assertOk();
    $this->actingAs($ctx->employee)->get('/admin/auditoria')->assertForbidden();
    $this->actingAs($ctx->finance)->get('/admin/proyectos')->assertForbidden();
});

it('renderiza el panel para cada rol', function () {
    $ctx = kendex();
    svc()->approve($ctx->supervisor, svc()->create($ctx->employee, expenseData($ctx, 700)));

    foreach (['employee', 'supervisor', 'finance', 'admin'] as $role) {
        Livewire::actingAs($ctx->$role)->test(Dashboard::class)->assertOk()->assertSee('Presupuesto usado por departamento');
    }

    Livewire::actingAs($ctx->admin)->test(Dashboard::class)->set('months', 12)->assertOk();
});

it('el centro de notificaciones marca como leídas', function () {
    $ctx = kendex();
    $n = UserNotification::create(['user_id' => $ctx->employee->id, 'type' => 'x', 'message' => 'Hola', 'url' => '/gastos']);
    UserNotification::create(['user_id' => $ctx->employee->id, 'type' => 'x', 'message' => 'Otra']);

    $c = Livewire::actingAs($ctx->employee)->test(Bell::class)->assertSee('Hola');
    $c->call('markRead', $n->id)->assertRedirect();
    $c->call('markAll');

    expect(UserNotification::whereNull('read_at')->count())->toBe(0);
});

it('el usuario edita su perfil', function () {
    $ctx = kendex();

    Livewire::actingAs($ctx->employee)->test(Profile::class)
        ->set('name', 'Nombre Nuevo')->set('phone', '809-555-0100')->call('save')->assertHasNoErrors();
    expect($ctx->employee->fresh()->name)->toBe('Nombre Nuevo');

    Livewire::actingAs($ctx->employee)->test(Profile::class)->set('phone', 'abc')->call('save')->assertHasErrors('phone');
});

it('exporta PDF y Excel con la visibilidad del rol', function () {
    $ctx = kendex();
    svc()->create($ctx->employee, expenseData($ctx));

    $this->actingAs($ctx->employee)->get('/reportes/gastos.pdf')->assertOk()->assertHeader('content-type', 'application/pdf');
    $this->actingAs($ctx->employee)->get('/reportes/gastos.xlsx')->assertOk();
});

it('los comandos programados recuerdan pendientes y abren el mes', function () {
    $ctx = kendex();
    $e = svc()->create($ctx->employee, expenseData($ctx));
    $e->forceFill(['created_at' => now()->subDays(5)])->saveQuietly();

    $this->artisan('kendex:remind-pending')->assertSuccessful();
    expect(UserNotification::where('type', 'expense.reminder')->count())->toBeGreaterThan(0);

    $prev = Dates::now()->subMonthNoOverflow();
    Budget::create(['department_id' => $ctx->dept->id, 'year' => $prev->year, 'month' => $prev->month, 'amount_assigned' => 5000]);
    Budget::where('department_id', $ctx->dept->id)->where('year', Dates::now()->year)->where('month', Dates::now()->month)->delete();

    $this->artisan('kendex:open-month')->assertSuccessful();
    expect(Budget::where('department_id', $ctx->dept->id)->where('year', Dates::now()->year)->where('month', Dates::now()->month)->exists())->toBeTrue()
        ->and(Budget::where('year', $prev->year)->where('month', $prev->month)->first()->closed)->toBeTrue();
});

it('un gasto fuera del rango de antigüedad es rechazado', function () {
    $ctx = kendex();

    fillForm(Livewire::actingAs($ctx->employee)->test(\App\Livewire\Expenses\Form::class), $ctx, [
        'expense_date' => now()->subDays(30)->toDateString(),
    ])->call('save')->assertHasErrors('expense_date');
});
