<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Orden importante: primero los usuarios (el Administrador es
     * quien crea los periodos), luego los catálogos permanentes
     * (docentes, aulas), luego los periodos, luego lo que depende de
     * varios catálogos a la vez (secciones, asignaciones) y al final
     * lo que depende de una asignación ya existente (sesiones_horario).
     */
    public function run(): void
    {
        // Administrador: gestiona cuentas y abre/cierra periodos.
        User::factory()->administrador()->create([
            'name' => 'Marcel Escobar',
            'email' => 'admin@unicah.edu.hn',
        ]);

        // Coordinadoras: operan el día a día del sistema.
        User::factory()->coordinador()->create([
            'name' => 'Divelyn Coordinadora',
            'email' => 'coordinador@unicah.edu.hn',
        ]);
        User::factory()->coordinador()->count(2)->create();

        // Cuenta de prueba genérica que ya venía en el proyecto.
        User::factory()->coordinador()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->call([
            DocenteSeeder::class,
            AulaSeeder::class,
            PeriodoAcademicoSeeder::class,
            SeccionSeeder::class,
            AsignacionSeeder::class,
            SesionHorarioSeeder::class,
        ]);
    }
}
