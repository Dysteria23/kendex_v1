<?php

use App\Enums\Role;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;

it('el registro de auditoría es inalterable', function () {
    $log = AuditLog::create(['action' => 'test', 'entity' => 'Test']);

    expect(fn () => $log->update(['action' => 'otro']))->toThrow(LogicException::class);
    expect(fn () => $log->delete())->toThrow(LogicException::class);
});

it('bloquea la cuenta tras los intentos fallidos configurados', function () {
    kendex();
    $user = User::factory()->create(['email' => 'lock@example.com']);

    foreach (range(1, 5) as $i) {
        RateLimiter::clear('login:127.0.0.1');
        $this->post('/login', ['email' => 'lock@example.com', 'password' => 'incorrecta']);
    }

    expect($user->fresh()->locked_until)->not->toBeNull();

    RateLimiter::clear('login:127.0.0.1');
    $this->post('/login', ['email' => 'lock@example.com', 'password' => 'password'])->assertSessionHasErrors('email');
    $this->assertGuest();
});

it('limita a 5 intentos fallidos por IP', function () {
    kendex();

    foreach (range(1, 5) as $i) {
        $this->post('/login', ['email' => "nadie{$i}@example.com", 'password' => 'x']);
    }

    $this->post('/login', ['email' => 'nadie@example.com', 'password' => 'x'])
        ->assertSessionHasErrors('email');
    expect(RateLimiter::tooManyAttempts('login:127.0.0.1', 5))->toBeTrue();
});

it('un usuario desactivado no puede iniciar sesión', function () {
    kendex();
    User::factory()->create(['email' => 'off@example.com', 'active' => false]);

    $this->post('/login', ['email' => 'off@example.com', 'password' => 'password']);
    $this->assertGuest();
});

it('un empleado no puede entrar a la administración', function () {
    $ctx = kendex();

    $this->actingAs($ctx->employee)->get('/admin/usuarios')->assertForbidden();
    $this->actingAs($ctx->admin)->get('/admin/usuarios')->assertOk();
});

it('un empleado solo ve sus propias solicitudes', function () {
    $ctx = kendex();
    $other = User::factory()->inDepartment($ctx->dept)->create();

    $mine = svc()->create($ctx->employee, expenseData($ctx));
    $theirs = svc()->create($other, expenseData($ctx));

    $this->actingAs($ctx->employee)->get("/gastos/{$mine->id}")->assertOk();
    $this->actingAs($ctx->employee)->get("/gastos/{$theirs->id}")->assertForbidden();
    $this->actingAs($ctx->supervisor)->get("/gastos/{$theirs->id}")->assertOk();
});

it('iniciar sesión en otro dispositivo revoca la sesión anterior', function () {
    $ctx = kendex();
    $ctx->employee->forceFill(['current_session_id' => 'otra-sesion'])->saveQuietly();

    $this->actingAs($ctx->employee)->get('/panel')->assertRedirect('/login');
});

it('las páginas públicas y de SEO responden', function () {
    foreach (['/', '/privacidad', '/terminos', '/login', '/sitemap.xml', '/robots.txt'] as $url) {
        $this->get($url)->assertOk();
    }

    $this->get('/pagina-que-no-existe')->assertNotFound()->assertSee('No encontramos esa página');
    $this->get('/')->assertSee('<meta name="description"', false)->assertSee('og:image', false);
});
