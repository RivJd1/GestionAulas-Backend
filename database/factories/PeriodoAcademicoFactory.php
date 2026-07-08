<?php

namespace Database\Factories;

use App\Models\PeriodoAcademico;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<PeriodoAcademico>
 */
class PeriodoAcademicoFactory extends Factory
{
    /**
     * Bloques del año usados para armar nombres realistas de periodo.
     */
    protected array $bloques = [
        ['Enero', 'Mayo'],
        ['Agosto', 'Diciembre'],
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $anio = 2023;
        static $bloqueIndex = 0;

        [$mesInicioNombre, $mesFinNombre] = $this->bloques[$bloqueIndex % count($this->bloques)];
        $mesInicio = $mesInicioNombre === 'Enero' ? 1 : 8;

        $fechaInicio = Carbon::create($anio, $mesInicio, 1);
        $fechaFin = $fechaInicio->copy()->addMonths(4)->endOfMonth();

        $nombre = "{$mesInicioNombre}-{$mesFinNombre} {$anio}";

        $bloqueIndex++;
        if ($bloqueIndex % count($this->bloques) === 0) {
            $anio++;
        }

        return [
            'nombre' => $nombre,
            'fecha_inicio' => $fechaInicio->toDateString(),
            'fecha_fin' => $fechaFin->toDateString(),
            // Por defecto se crea cerrado; el seeder decide cuál periodo
            // queda como el único activo, ya que esa regla de negocio
            // (solo un periodo activo a la vez) vive en el backend, no
            // en esta tabla.
            'estado' => 'cerrado',
            'id_usuario_creador' => User::factory()->administrador(),
        ];
    }

    /**
     * Periodo actualmente activo.
     */
    public function activo(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'activo',
        ]);
    }

    /**
     * Periodo ya cerrado (histórico).
     */
    public function cerrado(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'cerrado',
        ]);
    }
}
