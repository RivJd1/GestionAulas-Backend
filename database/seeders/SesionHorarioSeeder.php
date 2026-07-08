<?php

namespace Database\Seeders;

use App\Models\Asignacion;
use App\Models\SesionHorario;
use Illuminate\Database\Seeder;

class SesionHorarioSeeder extends Seeder
{
    /**
     * Franjas de inicio válidas dentro de la ventana 07:00-18:00 del
     * calendario semanal (excluye el bloque de almuerzo 12:00-13:00).
     */
    protected array $horasInicioPosibles = [7, 8, 9, 10, 14, 15, 16];

    /**
     * Patrones de días según cuántas sesiones por semana necesita la
     * sección, para no repetir el mismo día dos veces en una misma
     * asignación.
     */
    protected array $patronesDias = [
        1 => [['lunes'], ['martes'], ['miercoles'], ['jueves'], ['viernes'], ['sabado']],
        2 => [['lunes', 'miercoles'], ['martes', 'jueves'], ['miercoles', 'viernes']],
        3 => [['lunes', 'miercoles', 'viernes'], ['martes', 'jueves', 'sabado']],
    ];

    /**
     * Slots ya ocupados, para no generar dos secciones en la misma
     * aula (o el mismo docente) en el mismo día y hora. Simula, a
     * pequeña escala, la validación de conflictos que en producción
     * vive en el backend (ver conversación sobre el punto 11).
     *
     * @var array<string, true>
     */
    protected array $ocupacionAula = [];

    /**
     * @var array<string, true>
     */
    protected array $ocupacionDocente = [];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $asignaciones = Asignacion::with('seccion')
            ->where('estado', 'asignada')
            ->whereNotNull('id_aula')
            ->whereNotNull('id_docente')
            ->get();

        foreach ($asignaciones as $asignacion) {
            $this->generarBloques($asignacion);
        }
    }

    protected function generarBloques(Asignacion $asignacion): void
    {
        $seccion = $asignacion->seccion;

        if (! $seccion) {
            return;
        }

        $sesionesPorSemana = max(1, min(3, (int) $seccion->sesiones_por_semana));
        $duracionHoras = (float) $seccion->duracion_sesion_horas;

        $patrones = $this->patronesDias[$sesionesPorSemana];
        $dias = fake()->randomElement($patrones);

        // Las secciones con más de una sesión semanal se consideran
        // generadas por la función de replicación automática; las de
        // una sola sesión se consideran arrastradas manualmente.
        $esAutomatico = $sesionesPorSemana > 1;

        foreach ($dias as $dia) {
            $horaInicio = $this->buscarHoraLibre(
                $asignacion->id_aula,
                $asignacion->id_docente,
                $dia,
                $duracionHoras
            );

            if ($horaInicio === null) {
                // No se encontró un espacio libre razonable para este
                // bloque; se omite en vez de forzar un choque.
                continue;
            }

            $this->marcarOcupado($asignacion->id_aula, $asignacion->id_docente, $dia, $horaInicio, $duracionHoras);

            SesionHorario::factory()
                ->dia($dia)
                ->when($esAutomatico, fn ($factory) => $factory->automatico())
                ->create([
                    'id_asignacion' => $asignacion->id,
                    'hora_inicio' => sprintf('%02d:00:00', $horaInicio),
                ]);
        }
    }

    /**
     * Busca una hora de inicio, dentro de las franjas permitidas, que
     * no choque ni con el aula ni con el docente en ese mismo día.
     */
    protected function buscarHoraLibre(int $idAula, int $idDocente, string $dia, float $duracionHoras): ?int
    {
        $candidatas = fake()->shuffleArray($this->horasInicioPosibles);

        foreach ($candidatas as $horaInicio) {
            if ($horaInicio + $duracionHoras > 18) {
                continue;
            }

            if ($this->tieneChoque($idAula, $idDocente, $dia, $horaInicio, $duracionHoras)) {
                continue;
            }

            return $horaInicio;
        }

        return null;
    }

    protected function tieneChoque(int $idAula, int $idDocente, string $dia, int $horaInicio, float $duracionHoras): bool
    {
        $horasCubiertas = $this->horasCubiertas($horaInicio, $duracionHoras);

        foreach ($horasCubiertas as $hora) {
            if (isset($this->ocupacionAula["{$idAula}|{$dia}|{$hora}"])) {
                return true;
            }

            if (isset($this->ocupacionDocente["{$idDocente}|{$dia}|{$hora}"])) {
                return true;
            }
        }

        return false;
    }

    protected function marcarOcupado(int $idAula, int $idDocente, string $dia, int $horaInicio, float $duracionHoras): void
    {
        foreach ($this->horasCubiertas($horaInicio, $duracionHoras) as $hora) {
            $this->ocupacionAula["{$idAula}|{$dia}|{$hora}"] = true;
            $this->ocupacionDocente["{$idDocente}|{$dia}|{$hora}"] = true;
        }
    }

    /**
     * @return array<int, int>
     */
    protected function horasCubiertas(int $horaInicio, float $duracionHoras): array
    {
        $bloques = (int) ceil($duracionHoras);

        return range($horaInicio, $horaInicio + max(1, $bloques) - 1);
    }
}
