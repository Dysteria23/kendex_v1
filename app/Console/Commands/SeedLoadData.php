<?php

namespace App\Console\Commands;

use App\Enums\ExpenseStatus;
use App\Enums\Role;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Department;
use App\Support\Dates;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/** Genera usuarios y solicitudes en volumen para la prueba de estrés. Nunca en producción. */
class SeedLoadData extends Command
{
    protected $signature = 'kendex:load-data {--users=200} {--expenses=5000}';
    protected $description = 'Crea usuarios carga1..N@solocar.test y solicitudes masivas para pruebas de carga';

    public function handle(): int
    {
        if (app()->isProduction()) {
            $this->error('No se puede ejecutar en producción.');

            return self::FAILURE;
        }

        $departments = Department::where('active', true)->get();
        $categories = Category::where('active', true)->pluck('id');

        if ($departments->isEmpty() || $categories->isEmpty()) {
            $this->error('Ejecuta primero: php artisan migrate --seed');

            return self::FAILURE;
        }

        $users = (int) $this->option('users');
        $expenses = (int) $this->option('expenses');
        $now = now();
        $period = Dates::period();

        $this->info("Creando {$users} usuarios…");
        $hash = Hash::make('Kendex#2026'); // un solo hash bcrypt para todos (rápido)
        $rows = [];
        for ($i = 1; $i <= $users; $i++) {
            $rows[] = [
                'name' => "Usuario de carga {$i}", 'email' => "carga{$i}@solocar.test", 'password' => $hash,
                'role' => Role::Employee->value, 'department_id' => $departments[$i % $departments->count()]->id,
                'active' => true, 'failed_attempts' => 0, 'created_at' => $now, 'updated_at' => $now,
            ];
        }
        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('users')->insertOrIgnore($chunk);
        }

        $userIds = DB::table('users')->where('email', 'like', 'carga%@solocar.test')->get(['id', 'department_id']);

        // Presupuestos amplios para que el bloqueo por presupuesto no distorsione la prueba.
        foreach ($departments as $d) {
            Budget::updateOrCreate(
                ['department_id' => $d->id, 'category_id' => null, 'year' => $now->year, 'month' => $now->month],
                ['amount_assigned' => 500_000_000]
            );
        }

        $this->info("Creando {$expenses} solicitudes…");
        $statuses = [
            ExpenseStatus::Pending->value => 40, ExpenseStatus::Approved->value => 25, ExpenseStatus::Paid->value => 20,
            ExpenseStatus::Rejected->value => 10, ExpenseStatus::ChangesRequested->value => 5,
        ];
        $pool = collect($statuses)->flatMap(fn ($w, $s) => array_fill(0, $w, $s))->values();

        $batch = [];
        $existing = (int) DB::table('expenses')->where('number', 'like', 'GS-LOAD-%')->count();
        for ($i = 1; $i <= $expenses; $i++) {
            $u = $userIds->random();
            $status = $pool->random();
            $amount = round(mt_rand(10000, 4000000) / 100, 2);
            $itbis = round($amount * 0.18 / 1.18, 2);
            $batch[] = [
                'number' => 'GS-LOAD-' . str_pad((string) ($existing + $i), 7, '0', STR_PAD_LEFT),
                'user_id' => $u->id, 'department_id' => $u->department_id, 'category_id' => $categories->random(),
                'amount' => $amount, 'subtotal' => $amount - $itbis, 'itbis_amount' => $itbis,
                'description' => 'Gasto de carga ' . ($existing + $i) . ' compra de materiales y servicios',
                'expense_date' => now()->subDays(mt_rand(0, 6))->toDateString(),
                'status' => $status, 'approval_stage' => 'supervisor',
                'budget_period' => in_array($status, ['aprobado', 'pagado'], true) ? $period : null,
                'created_at' => now()->subDays(mt_rand(0, 6)), 'updated_at' => $now,
            ];
            if (count($batch) === 500) {
                DB::table('expenses')->insert($batch);
                $batch = [];
            }
        }
        if ($batch) {
            DB::table('expenses')->insert($batch);
        }

        // Alinea comprometido/ejecutado de los presupuestos con los gastos generados.
        foreach ($departments as $d) {
            $sum = fn (string $status) => (float) DB::table('expenses')->where('department_id', $d->id)
                ->where('budget_period', $period)->where('status', $status)->sum('amount');

            Budget::where('department_id', $d->id)->whereNull('category_id')->where('year', $now->year)->where('month', $now->month)
                ->update(['amount_committed' => $sum('aprobado'), 'amount_executed' => $sum('pagado')]);
        }

        $this->info('Listo. Inicia sesión con carga1@solocar.test … carga' . $users . '@solocar.test  (contraseña Kendex#2026)');

        return self::SUCCESS;
    }
}
