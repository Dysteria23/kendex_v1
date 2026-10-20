<?php

use App\Enums\ExpenseStatus;
use App\Models\Budget;
use App\Services\ExpenseService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

function deptBudget($ctx): Budget
{
    return Budget::where('department_id', $ctx->dept->id)->whereNull('category_id')->firstOrFail()->fresh();
}

it('crea una solicitud pendiente con número correlativo', function () {
    $ctx = kendex();
    $e = svc()->create($ctx->employee, expenseData($ctx));

    expect($e->status)->toBe(ExpenseStatus::Pending)
        ->and($e->number)->toStartWith('GS-')
        ->and($e->approval_stage)->toBe('supervisor');
});

it('compromete el presupuesto al aprobar y lo ejecuta al pagar', function () {
    $ctx = kendex();
    $e = svc()->create($ctx->employee, expenseData($ctx, 800));

    svc()->approve($ctx->supervisor, $e);
    expect((float) deptBudget($ctx)->amount_committed)->toBe(800.0);

    svc()->pay($ctx->finance, $e->fresh(), ['method' => 'transferencia', 'reference' => 'TRX-1']);
    $b = deptBudget($ctx);

    expect((float) $b->amount_committed)->toBe(0.0)
        ->and((float) $b->amount_executed)->toBe(800.0)
        ->and($e->fresh()->status)->toBe(ExpenseStatus::Paid);
});

it('exige motivo para rechazar y no permite reabrir una solicitud rechazada', function () {
    $ctx = kendex();
    $e = svc()->create($ctx->employee, expenseData($ctx));

    expect(fn () => svc()->reject($ctx->supervisor, $e, ''))->toThrow(ValidationException::class);

    svc()->reject($ctx->supervisor, $e, 'Falta la factura');
    expect($e->fresh()->status)->toBe(ExpenseStatus::Rejected);

    expect(fn () => svc()->approve($ctx->admin, $e->fresh()))->toThrow(AuthorizationException::class);
});

it('un supervisor no puede aprobar su propia solicitud y la revisa un administrador', function () {
    $ctx = kendex();
    $e = svc()->create($ctx->supervisor, expenseData($ctx));

    expect($e->approval_stage)->toBe('admin');
    expect(fn () => svc()->approve($ctx->supervisor, $e))->toThrow(AuthorizationException::class);

    svc()->approve($ctx->admin, $e);
    expect($e->fresh()->status)->toBe(ExpenseStatus::Approved);
});

it('exige segunda aprobación del administrador sobre el umbral', function () {
    $ctx = kendex(500000);
    $e = svc()->create($ctx->employee, expenseData($ctx, 20000, ['description' => 'Equipo de montacargas']));

    svc()->approve($ctx->supervisor, $e);
    expect($e->fresh()->status)->toBe(ExpenseStatus::Pending)
        ->and($e->fresh()->approval_stage)->toBe('admin');

    svc()->approve($ctx->admin, $e->fresh());
    expect($e->fresh()->status)->toBe(ExpenseStatus::Approved);
});

it('bloquea solicitudes que exceden el presupuesto', function () {
    $ctx = kendex(1000);

    expect(fn () => svc()->create($ctx->employee, expenseData($ctx, 1500)))
        ->toThrow(ValidationException::class);
});

it('solo Finanzas puede pagar', function () {
    $ctx = kendex();
    $e = svc()->create($ctx->employee, expenseData($ctx));
    svc()->approve($ctx->supervisor, $e);

    expect(fn () => svc()->pay($ctx->admin, $e->fresh(), ['method' => 'efectivo']))
        ->toThrow(AuthorizationException::class);
});

it('solo permite anular en estado pendiente y al dueño', function () {
    $ctx = kendex();
    $e = svc()->create($ctx->employee, expenseData($ctx));

    expect(fn () => svc()->cancel($ctx->supervisor, $e))->toThrow(AuthorizationException::class);

    svc()->cancel($ctx->employee, $e);
    expect($e->fresh()->status)->toBe(ExpenseStatus::Cancelled);
});

it('al pedir modificación y reenviar vuelve a pendiente', function () {
    $ctx = kendex();
    $e = svc()->create($ctx->employee, expenseData($ctx));

    svc()->requestChanges($ctx->supervisor, $e, 'Corrige el monto');
    expect($e->fresh()->status)->toBe(ExpenseStatus::ChangesRequested);

    svc()->update($ctx->employee, $e->fresh(), expenseData($ctx, 450));
    expect($e->fresh()->status)->toBe(ExpenseStatus::Pending)->and((float) $e->fresh()->amount)->toBe(450.0);
});

it('dispara la alerta crítica una sola vez y notifica', function () {
    $ctx = kendex(1000);
    $e = svc()->create($ctx->employee, expenseData($ctx, 950));
    svc()->approve($ctx->supervisor, $e);

    expect(\App\Models\Alert::where('department_id', $ctx->dept->id)->pluck('type')->all())
        ->toContain('advertencia', 'critica');

    $count = \App\Models\Alert::count();
    $e2 = svc()->create($ctx->employee, expenseData($ctx, 10));
    svc()->approve($ctx->supervisor, $e2);
    expect(\App\Models\Alert::count())->toBe($count);
});
