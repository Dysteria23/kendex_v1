<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Department;
use App\Models\User;
use App\Support\Dates;
use Illuminate\Database\Seeder;

/** Datos de demostración de SOLOCAR (empresa ficticia). Solo para entornos de desarrollo. */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->error('Este seeder crea usuarios con contraseña conocida y no debe ejecutarse en producción.');

            return;
        }

        $password = 'Kendex#2026';

        $departments = collect([
            ['Operaciones y Logística', 180000],
            ['Importaciones', 150000],
            ['Ventas y Distribución', 220000],
            ['Almacén', 90000],
            ['Administración y Finanzas', 120000],
            ['Tecnología', 80000],
        ])->mapWithKeys(fn ($d) => [$d[0] => Department::firstOrCreate(['name' => $d[0]], ['budget_monthly' => $d[1]])]);

        $admin = User::firstOrCreate(['email' => 'admin@solocar.test'], [
            'name' => 'Roberto Alfonso Peralta Valdez', 'password' => $password, 'role' => Role::Admin,
            'department_id' => $departments['Administración y Finanzas']->id, 'position' => 'Director Operativo y Financiero',
        ]);

        User::firstOrCreate(['email' => 'admin2@solocar.test'], [
            'name' => 'Segundo Administrador', 'password' => $password, 'role' => Role::Admin,
            'department_id' => $departments['Administración y Finanzas']->id, 'position' => 'Administrador alterno',
        ]);

        User::firstOrCreate(['email' => 'finanzas@solocar.test'], [
            'name' => 'Analista de Finanzas', 'password' => $password, 'role' => Role::Finance,
            'department_id' => $departments['Administración y Finanzas']->id, 'position' => 'Analista financiero',
        ]);

        foreach ($departments as $name => $department) {
            $slug = str($name)->ascii()->slug()->toString();

            $supervisor = User::firstOrCreate(['email' => "supervisor.$slug@solocar.test"], [
                'name' => "Supervisor de $name", 'password' => $password, 'role' => Role::Supervisor,
                'department_id' => $department->id, 'position' => 'Supervisor',
            ]);
            $department->update(['supervisor_id' => $supervisor->id]);

            User::firstOrCreate(['email' => "empleado.$slug@solocar.test"], [
                'name' => "Empleado de $name", 'password' => $password, 'role' => Role::Employee,
                'department_id' => $department->id, 'position' => 'Colaborador',
            ]);
        }

        foreach (['Combustible', 'Alimentación', 'Transporte', 'Equipos', 'Servicios', 'Materiales de Oficina', 'Otros'] as $name) {
            Category::firstOrCreate(['name' => $name, 'parent_id' => null]);
        }

        $now = Dates::now();
        foreach ($departments as $department) {
            Budget::firstOrCreate(
                ['department_id' => $department->id, 'category_id' => null, 'year' => $now->year, 'month' => $now->month],
                ['amount_assigned' => $department->budget_monthly]
            );
        }

        $this->command?->info("Usuarios de prueba creados. Contraseña: $password (admin@solocar.test, finanzas@solocar.test, ...)");
    }
}
