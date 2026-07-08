<?php

namespace Database\Factories;

use App\Models\Asignacion;
use App\Models\SesionHorario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<SesionHorario>
 */
class SesionHorarioFactory extends Factory
{
    /**
     * Horas de inicio típicas dentro de la ventana 07:00-18:00 que
     * describe el calendario semanal del frontend.
     */
    protected array $horasInicioPosibles = [7, 8, 9, 10, 11, 13, 14, 15, 16];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $horaInicio = fake()->randomElement($this->horasInicioPosibles);

        return [
            'id_asignacion' => Asignacion::factory(),
            'dia' => fake()->randomElement(['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado']),
            'hora_inicio' => sprintf('%02d:00:00', $horaInicio),
            // Se recalcula en el hook de abajo usando la duración real
            // de la sección; este valor es solo un respaldo.
            'hora_fin' => sprintf('%02d:00:00', min($horaInicio + 1, 18)),
            'generado_automaticamente' => false,
        ];
    }

    /**
     * Ajusta hora_fin para que respete la duración real de la sección
     * detrás de la asignación (id_asignacion -> seccion.duracion_sesion_horas),
     * en vez de asumir siempre una hora exacta.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (SesionHorario $sesion) {
            if (! $sesion->id_asignacion || ! $sesion->hora_inicio) {
                return;
            }

            $duracionHoras = Asignacion::with('seccion')
                ->find($sesion->id_asignacion)
                ?->seccion
                ?->duracion_sesion_horas;

            if (! $duracionHoras) {
                return;
            }

            $inicio = Carbon::parse($sesion->hora_inicio);
            $sesion->hora_fin = $inicio->copy()
                ->addMinutes((int) round($duracionHoras * 60))
                ->format('H:i:s');
        });
    }

    /**
     * Bloque creado por la función de replicación automática, en vez
     * de arrastrado manualmente por el coordinador.
     */
    public function automatico(): static
    {
        return $this->state(fn (array $attributes) => [
            'generado_automaticamente' => true,
        ]);
    }

    public function dia(string $dia): static
    {
        return $this->state(fn (array $attributes) => [
            'dia' => $dia,
        ]);
    }
}
