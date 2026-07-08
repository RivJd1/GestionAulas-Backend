<?php

namespace Database\Seeders;

use App\Models\Asignacion;
use App\Models\Aula;
use App\Models\Docente;
use App\Models\PeriodoAcademico;
use App\Models\Seccion;
use Illuminate\Database\Seeder;

class AsignacionSeeder extends Seeder
{
    /**
     * Aulas disponibles cargadas como [id => capacidad_maxima], para
     * poder generar matrículas realistas (por debajo de la capacidad
     * real de la aula que le tocó a cada asignación).
     *
     * @var array<int, int>
     */
    protected array $aulasDisponibles = [];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $periodoActivo = PeriodoAcademico::where('estado', 'activo')->first();
        $periodoCerrado = PeriodoAcademico::where('estado', 'cerrado')->first();

        $this->aulasDisponibles = Aula::where('estado', 'disponible')->pluck('capacidad_maxima', 'id')->all();
        $docentesActivos = Docente::where('estado', 'activo')->pluck('id')->all();
        $secciones = Seccion::where('activa', true)->get();

        if (! $periodoActivo || empty($this->aulasDisponibles) || empty($docentesActivos) || $secciones->isEmpty()) {
            return;
        }

        // Sección usada para demostrar el ejemplo del propio análisis:
        // "28 estudiantes el semestre pasado, 30 este semestre" sobre
        // la misma sección, en dos periodos distintos. Se elige un
        // aula con capacidad suficiente para que ambos números quepan
        // sin generar un sobrecupo accidental que no viene al caso.
        $seccionHistorica = $secciones->first();
        $idAulaHistorica = $this->elegirAulaConCapacidad(31);

        if ($periodoCerrado && $idAulaHistorica) {
            Asignacion::factory()
                ->asignada()
                ->create([
                    'id_seccion' => $seccionHistorica->id,
                    'id_periodo' => $periodoCerrado->id,
                    'id_aula' => $idAulaHistorica,
                    'id_docente' => fake()->randomElement($docentesActivos),
                    'estudiantes_matriculados' => 28,
                ]);
        }

        foreach ($secciones as $index => $seccion) {
            // La sección histórica también recibe su asignación en el
            // periodo activo, con más matrícula que el semestre pasado.
            if ($seccion->id === $seccionHistorica->id) {
                Asignacion::factory()->asignada()->create([
                    'id_seccion' => $seccion->id,
                    'id_periodo' => $periodoActivo->id,
                    'id_aula' => $idAulaHistorica ?? $this->elegirAulaConCapacidad(31),
                    'id_docente' => fake()->randomElement($docentesActivos),
                    'estudiantes_matriculados' => 30,
                ]);

                continue;
            }

            // Distribución de estados dentro del periodo activo:
            // ~20% todavía disponibles en el Kanban (sin aula/docente),
            // ~30% con aula/docente pero horario aún pendiente,
            // ~50% completamente asignadas (recibirán horario luego).
            $dado = $index % 10;

            if ($dado < 2) {
                Asignacion::factory()->pendiente()->create([
                    'id_seccion' => $seccion->id,
                    'id_periodo' => $periodoActivo->id,
                ]);

                continue;
            }

            $idAula = fake()->randomElement(array_keys($this->aulasDisponibles));

            if ($dado < 5) {
                Asignacion::factory()->create([
                    'id_seccion' => $seccion->id,
                    'id_periodo' => $periodoActivo->id,
                    'id_aula' => $idAula,
                    'id_docente' => fake()->randomElement($docentesActivos),
                    'estudiantes_matriculados' => $this->matriculaSeguraPara($idAula),
                ]);

                continue;
            }

            Asignacion::factory()->asignada()->create([
                'id_seccion' => $seccion->id,
                'id_periodo' => $periodoActivo->id,
                'id_aula' => $idAula,
                'id_docente' => fake()->randomElement($docentesActivos),
                'estudiantes_matriculados' => $this->matriculaSeguraPara($idAula),
            ]);
        }

        // Ejemplo explícito de sobrecupo ya confirmado por el
        // coordinador. Se excluye la sección histórica a propósito,
        // para no pisar el 30 que representa "este semestre".
        $conAula = Asignacion::with('aula')
            ->where('id_periodo', $periodoActivo->id)
            ->where('id_seccion', '!=', $seccionHistorica->id)
            ->whereNotNull('id_aula')
            ->first();

        if ($conAula && $conAula->aula) {
            $conAula->update([
                // Por encima de la capacidad real de esa aula específica,
                // nunca un número fijo que podría no superarla.
                'estudiantes_matriculados' => $conAula->aula->capacidad_maxima + 8,
                'sobrecargo_confirmado' => true,
            ]);
        }
    }

    /**
     * Matrícula cómodamente por debajo de la capacidad real de esa
     * aula (entre 60% y 90% de su capacidad_maxima).
     */
    protected function matriculaSeguraPara(int $idAula): int
    {
        $capacidad = $this->aulasDisponibles[$idAula] ?? 30;

        return (int) round($capacidad * fake()->randomFloat(2, 0.6, 0.9));
    }

    /**
     * Devuelve el id de la primera aula disponible con capacidad
     * mínima requerida, o null si ninguna califica.
     */
    protected function elegirAulaConCapacidad(int $minimo): ?int
    {
        foreach ($this->aulasDisponibles as $id => $capacidad) {
            if ($capacidad >= $minimo) {
                return $id;
            }
        }

        return null;
    }
}
