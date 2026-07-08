<?php

namespace Database\Factories;

use App\Models\Aula;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Aula>
 */
class AulaFactory extends Factory
{
    protected array $edificios = [
        'Edificio A',
        'Edificio B',
        'Edificio C',
        'Edificio de Ingeniería',
    ];

    /**
     * 'tipo' es texto libre e informativo, nunca participa en
     * ninguna validación del sistema.
     */
    protected array $tipos = [
        'Aula estándar',
        'Laboratorio de Computación',
        'Sala de Cómputo',
        'Auditorio',
        'Sala de conferencias',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $numeroSala = 100;
        $numeroSala++;

        return [
            // El número de sala es único por sí solo con este contador,
            // así que la pareja (nombre, edificio) queda garantizada
            // sin depender de faker->unique().
            'nombre' => 'Aula ' . $numeroSala,
            'edificio' => fake()->randomElement($this->edificios),
            'piso' => (string) fake()->numberBetween(1, 4),
            'tipo' => fake()->randomElement($this->tipos),
            'capacidad_maxima' => fake()->randomElement([20, 25, 30, 35, 40, 45]),
            'descripcion' => fake()->optional(0.4)->sentence(10),
            'estado' => fake()->randomElement(['disponible', 'disponible', 'disponible', 'disponible', 'mantenimiento']),
        ];
    }

    /**
     * Aula disponible para usarse.
     */
    public function disponible(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'disponible',
        ]);
    }

    /**
     * Aula bloqueada visualmente en el Kanban por mantenimiento.
     */
    public function enMantenimiento(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'mantenimiento',
        ]);
    }

    /**
     * Laboratorio de cómputo con mayor capacidad, útil para secciones
     * de tipo laboratorio/práctica.
     */
    public function laboratorio(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo' => 'Laboratorio de Computación',
            'capacidad_maxima' => fake()->randomElement([20, 25, 30]),
        ]);
    }
}
