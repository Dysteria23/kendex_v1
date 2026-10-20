<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SeoController;
use App\Livewire\Admin;
use App\Livewire\Dashboard;
use App\Livewire\Expenses;
use App\Livewire\Profile;
use Illuminate\Support\Facades\Route;

// Páginas públicas
Route::view('/', 'pages.home')->name('home');
Route::view('/privacidad', 'legal.privacy')->name('privacy');
Route::view('/terminos', 'legal.terms')->name('terms');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');

// Invitados
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('honeypot');

    if (config('kendex.registration_enabled')) {
        Route::get('/registro', [RegisterController::class, 'create'])->name('register');
        Route::post('/registro', [RegisterController::class, 'store'])->middleware('honeypot');
    }

    Route::get('/recuperar-contrasena', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('/recuperar-contrasena', [PasswordResetController::class, 'email'])->middleware('honeypot')->name('password.email');
    Route::get('/restablecer-contrasena/{token}', [PasswordResetController::class, 'reset'])->name('password.reset');
    Route::post('/restablecer-contrasena', [PasswordResetController::class, 'update'])->name('password.update');
});

// Aplicación
Route::middleware(['auth', 'session.guard'])->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/panel', Dashboard::class)->name('dashboard');
    Route::get('/perfil', Profile::class)->name('profile');

    Route::get('/gastos', Expenses\Index::class)->name('expenses.index');
    Route::get('/gastos/nuevo', Expenses\Form::class)->name('expenses.create');
    Route::get('/gastos/{expense}/editar', Expenses\Form::class)->name('expenses.edit');
    Route::get('/gastos/{expense}', Expenses\Show::class)->name('expenses.show');
    Route::get('/comprobantes/{expense}/{index}', ReceiptController::class)->name('receipts.show');

    Route::get('/reportes/gastos.pdf', [ReportController::class, 'pdf'])->name('reports.pdf');
    Route::get('/reportes/gastos.xlsx', [ReportController::class, 'xlsx'])->name('reports.xlsx');

    Route::middleware('role:administrador')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/usuarios', Admin\Users::class)->name('users');
        Route::get('/departamentos', Admin\Departments::class)->name('departments');
        Route::get('/categorias', Admin\Categories::class)->name('categories');
        Route::get('/presupuestos', Admin\Budgets::class)->name('budgets');
        Route::get('/proyectos', Admin\Projects::class)->name('projects');
        Route::get('/reglas', Admin\Rules::class)->name('rules');
        Route::get('/auditoria', Admin\AuditTrail::class)->name('audit');
    });
});
