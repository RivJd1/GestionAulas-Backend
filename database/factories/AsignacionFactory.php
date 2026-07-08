<?php

namespace Database\Factories;

use App\Models\Asignacion;
use App\Models\Aula;
use App\Models\Docente;
use App\Models\PeriodoAcademico;
use App\Models\Seccion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Asignacion>
 */
class AsignacionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_seccion' => Seccion::factory(),
            'id_periodo' => PeriodoAcademico::factory(),
            'id_aula' => Aula::factory(),
            'id_docente' => Docente::factory(),
            'estudiantes_matriculados' => fake()->numberBetween(15, 35),
            'sobrecargo_confirmado' => false,
            // Nace pendiente de horario; pasa a 'asignada' cuando ya
            // tiene bloques confirmados en sesiones_horario.
            'estado' => 'activa',
        ];
    }

    /**
     * Todavía sin aula ni docente real fijado (columna "disponibles"
     * del tablero Kanban).
     */
    public function pendiente(): static
    {
        return $this->state(fn (array $attributes) => [
            'id_aula' => null,
            'id_docente' => null,
            'estado' => 'activa',
        ]);
    }

    /**
     * Ya tiene aula y docente, y su horario semanal está completo.
     */
    public function asignada(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'asignada',
        ]);
    }

    /**
     * Matrícula por encima de la capacidad del aula, ya confirmada
     * explícitamente por el coordinador.
     */
    public function conSobrecupo(): static
    {
        return $this->state(fn (array $attributes) => [
            'estudiantes_matriculados' => fake()->numberBetween(36, 50),
            'sobrecargo_confirmado' => true,
        ]);
    }
}
