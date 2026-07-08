<?php

namespace Database\Seeders;

use App\Models\Docente;
use Illuminate\Database\Seeder;

class DocenteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Docente::factory()->count(16)->create();

        // Ejemplo concreto del escenario de licencia (ej. maternidad):
        // el docente sigue existiendo y puede seguir referenciado por
        // secciones/asignaciones antiguas hasta que el coordinador
        // decida reasignarlas manualmente.
        Docente::factory()->enLicencia()->create();

        // Un par de docentes inactivos (ya no laboran en la institución).
        Docente::factory()->inactivo()->count(2)->create();
    }
}
