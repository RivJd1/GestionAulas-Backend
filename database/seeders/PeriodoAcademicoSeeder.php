<?php

namespace Database\Seeders;

use App\Models\PeriodoAcademico;
use App\Models\User;
use Illuminate\Database\Seeder;

class PeriodoAcademicoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $administrador = User::where('rol', 'administrador')->first()
            ?? User::factory()->administrador()->create();

        // Periodos históricos, ya cerrados.
        PeriodoAcademico::factory()
            ->cerrado()
            ->count(2)
            ->create(['id_usuario_creador' => $administrador->id]);

        // El único periodo activo en este momento. La regla de "solo
        // un periodo activo a la vez" la aplica el backend, no esta
        // tabla, así que aquí solo se crea uno a propósito.
        PeriodoAcademico::factory()
            ->activo()
            ->create(['id_usuario_creador' => $administrador->id]);
    }
}
