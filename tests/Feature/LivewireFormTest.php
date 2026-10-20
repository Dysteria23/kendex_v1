<?php

use App\Livewire\Expenses\Form;
use App\Livewire\Expenses\Index;
use App\Livewire\Expenses\Show;
use App\Models\Expense;
use App\Models\Project;
use App\Support\Dates;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('crea una solicitud desde el formulario', function () {
    $ctx = kendex();

    fillForm(Livewire::actingAs($ctx->employee)->test(Form::class), $ctx)
        ->call('save')->assertHasNoErrors()->assertRedirect();

    $e = Expense::first();
    expect($e)->not->toBeNull()->and($e->user_id)->toBe($ctx->employee->id);
});

it('valida RNC, NCF y fecha futura', function () {
    $ctx = kendex();

    fillForm(Livewire::actingAs($ctx->employee)->test(Form::class), $ctx, [
        'rnc_provider' => '12345', 'ncf' => 'XYZ', 'expense_date' => now()->addDays(3)->toDateString(),
    ])->call('save')->assertHasErrors(['rnc_provider', 'ncf', 'expense_date']);

    expect(Expense::count())->toBe(0);
});

it('exige NCF cuando se indica el RNC del proveedor', function () {
    $ctx = kendex();

    fillForm(Livewire::actingAs($ctx->employee)->test(Form::class), $ctx, ['rnc_provider' => '101010101'])
        ->call('save')->assertHasErrors(['ncf' => 'required']);
});

it('exige comprobante sobre el umbral y lo guarda al adjuntarlo', function () {
    Storage::fake('local');
    $ctx = kendex();

    $c = fillForm(Livewire::actingAs($ctx->employee)->test(Form::class), $ctx, ['amount' => '2500']);
    $c->call('save')->assertHasErrors('uploads');

    $c->set('upload', UploadedFile::fake()->image('factura.jpg', 800, 600))
        ->call('save')->assertHasNoErrors();

    expect(Expense::first()->receipts())->toHaveCount(1);
});

it('muestra el bloqueo por presupuesto en el formulario', function () {
    $ctx = kendex(400);

    fillForm(Livewire::actingAs($ctx->employee)->test(Form::class), $ctx, ['amount' => '450'])
        ->call('save')->assertHasErrors('budget');
});

it('bloquea solicitudes que superan el tope del proyecto', function () {
    $ctx = kendex();
    $project = Project::create(['code' => 'P-1', 'name' => 'Ampliación de almacén', 'budget_total' => 1000]);

    $e = svc()->create($ctx->employee, expenseData($ctx, 900, ['project_id' => $project->id]));
    svc()->approve($ctx->supervisor, $e);

    expect(fn () => svc()->create($ctx->employee, expenseData($ctx, 300, ['project_id' => $project->id])))
        ->toThrow(\Illuminate\Validation\ValidationException::class);
});

it('el empleado no puede editar la solicitud de otro', function () {
    $ctx = kendex();
    $other = \App\Models\User::factory()->inDepartment($ctx->dept)->create();
    $e = svc()->create($other, expenseData($ctx));

    Livewire::actingAs($ctx->employee)->test(Form::class, ['expense' => $e])->assertForbidden();
});

it('lista y busca solicitudes solo del usuario', function () {
    $ctx = kendex();
    svc()->create($ctx->employee, expenseData($ctx, 100, ['description' => 'Compra de tinta azul']));
    svc()->create($ctx->employee, expenseData($ctx, 100, ['description' => 'Pago de cemento gris']));

    Livewire::actingAs($ctx->employee)->test(Index::class)
        ->assertSee('tinta azul')->assertSee('cemento gris')
        ->set('search', 'tinta')->assertSee('tinta azul')->assertDontSee('cemento gris');
});

it('el supervisor aprueba desde la pantalla de detalle', function () {
    $ctx = kendex();
    $e = svc()->create($ctx->employee, expenseData($ctx));

    Livewire::actingAs($ctx->supervisor)->test(Show::class, ['expense' => $e])
        ->set('comment', 'Todo en orden')->call('approve')->assertHasNoErrors();

    expect($e->fresh()->status->value)->toBe('aprobado');
});

it('rechazar sin motivo muestra el error', function () {
    $ctx = kendex();
    $e = svc()->create($ctx->employee, expenseData($ctx));

    Livewire::actingAs($ctx->supervisor)->test(Show::class, ['expense' => $e])
        ->call('reject')->assertHasErrors('comment');
});

it('Finanzas registra el pago desde la pantalla de detalle', function () {
    $ctx = kendex();
    $e = svc()->create($ctx->employee, expenseData($ctx));
    svc()->approve($ctx->supervisor, $e);

    Livewire::actingAs($ctx->finance)->test(Show::class, ['expense' => $e->fresh()])
        ->set('method', 'transferencia')->call('pay')->assertHasErrors('reference')
        ->set('reference', 'TRX-889')->call('pay')->assertHasNoErrors();

    expect($e->fresh()->status->value)->toBe('pagado');
});
