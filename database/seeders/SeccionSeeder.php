<?php

namespace Database\Seeders;

use App\Models\Docente;
use App\Models\Seccion;
use Illuminate\Database\Seeder;

class SeccionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Solo docentes activos suelen quedar como titulares por
        // defecto de una sección nueva; los de licencia/inactivos se
        // dejan fuera del catálogo elegible aquí a propósito.
        $docentesActivos = Docente::where('estado', 'activo')->pluck('id')->all();

        for ($i = 0; $i < 22; $i++) {
            Seccion::factory()->create([
                // ~20% de las secciones quedan sin titular todavía,
                // tal como permite el esquema.
                'id_docente' => fake()->boolean(80) && count($docentesActivos) > 0
                    ? fake()->randomElement($docentesActivos)
                    : null,
            ]);
        }

        // Un par de secciones explícitamente retiradas del catálogo
        // activo (no borradas, solo desactivadas).
        Seccion::factory()->inactiva()->count(2)->create([
            'id_docente' => count($docentesActivos) > 0 ? fake()->randomElement($docentesActivos) : null,
        ]);
    }
}
