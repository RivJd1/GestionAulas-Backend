<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asignacion;
use App\Models\Aula;
use App\Models\Docente;
use App\Models\PeriodoAcademico;
use App\Models\Seccion;
use App\Models\SesionHorario;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    /**
     * GET /api/dashboard
     *
     * Todo lo que necesita la pantalla de inicio, calculado en vivo desde
     * la base de datos: no hay ningún número fijo ni de ejemplo aquí.
     */
    public function index(): JsonResponse
    {
        $periodoActivo = PeriodoAcademico::whereRaw('UPPER(estado) = ?', ['ACTIVO'])->first();

        $asignacionesPeriodo = $periodoActivo
            ? Asignacion::with(['seccion', 'aula', 'docente'])
                ->where('id_periodo', $periodoActivo->id)
                ->get()
            : collect();

        $asignadas = $asignacionesPeriodo->whereNotNull('id_aula');

        // ---- Secciones -----------------------------------------------
        $totalSecciones = Seccion::where('activa', true)->count();
        $seccionesAsignadasIds = $asignadas->pluck('id_seccion')->unique();
        $seccionesAsignadas = $seccionesAsignadasIds->count();
        $seccionesPendientes = max($totalSecciones - $seccionesAsignadas, 0);

        // ---- Aulas ------------------------------------------------------
        $aulas = Aula::all();
        $totalAulas = $aulas->count();
        $aulasMantenimiento = $aulas->where('estado', 'mantenimiento')->count();
        $aulasEnUsoIds = $asignadas->pluck('id_aula')->unique();
        $aulasEnUso = $aulas->whereIn('id', $aulasEnUsoIds)->where('estado', '!=', 'mantenimiento')->count();
        $aulasDisponibles = max($totalAulas - $aulasMantenimiento - $aulasEnUso, 0);

        // ---- Docentes -----------------------------------------------------
        $docentesConClaseIds = $asignacionesPeriodo->pluck('id_docente')->filter()->unique();
        $totalDocentes = Docente::count();
        $docentesActivos = Docente::where('estado', 'activo')->whereIn('id', $docentesConClaseIds)->count();
        $docentesSinAsignar = Docente::where('estado', 'activo')->whereNotIn('id', $docentesConClaseIds)->count();

        // ---- Conflictos reales, derivados de los datos -------------------
        $conflictos = [];

        foreach ($asignacionesPeriodo as $a) {
            $codigoSeccion = $a->seccion->codigo_materia ?? ('SEC-' . $a->id_seccion);
            $nombreDocente = $a->docente->nombre_completo ?? 'Sin asignar';
            $nombreAula = $a->aula->nombre ?? '—';

            if ($a->aula && $a->estudiantes_matriculados > $a->aula->capacidad_maxima) {
                $conflictos[] = [
                    'id' => 'sobrecupo-' . $a->id,
                    'seccion' => $codigoSeccion,
                    'docente' => $nombreDocente,
                    'aula' => $nombreAula,
                    'tipo' => "Sobrecupo de capacidad ({$a->estudiantes_matriculados}/{$a->aula->capacidad_maxima})",
                    'prioridad' => 'ALTA',
                    'estado' => $a->sobrecargo_confirmado ? 'En Proceso' : 'Pendiente',
                    'asignacion_id' => $a->id,
                ];
            }

            if ($a->aula && $a->aula->estado === 'mantenimiento') {
                $conflictos[] = [
                    'id' => 'mantenimiento-' . $a->id,
                    'seccion' => $codigoSeccion,
                    'docente' => $nombreDocente,
                    'aula' => $nombreAula,
                    'tipo' => 'Aula en mantenimiento',
                    'prioridad' => 'ALTA',
                    'estado' => 'Bloqueado',
                    'asignacion_id' => $a->id,
                ];
            }
        }

        // Secciones activas del período sin ningún aula asignada.
        if ($periodoActivo) {
            $seccionesSinAula = Seccion::where('activa', true)
                ->whereNotIn('id', $seccionesAsignadasIds)
                ->get();

            foreach ($seccionesSinAula as $seccion) {
                $conflictos[] = [
                    'id' => 'sin-aula-' . $seccion->id,
                    'seccion' => $seccion->codigo_materia,
                    'docente' => $seccion->docente->nombre_completo ?? 'Sin asignar',
                    'aula' => '—',
                    'tipo' => 'Sin aula asignada',
                    'prioridad' => 'MEDIA',
                    'estado' => 'En Revisión',
                    'asignacion_id' => null,
                ];
            }
        }

        // Choques de horario: mismo docente, mismo día, horas que se cruzan.
        $sesiones = SesionHorario::with(['asignacion.docente', 'asignacion.seccion'])
            ->whereHas('asignacion', function ($query) use ($periodoActivo) {
                if ($periodoActivo) {
                    $query->where('id_periodo', $periodoActivo->id);
                }
            })
            ->get()
            ->filter(fn ($sesion) => $sesion->asignacion && $sesion->asignacion->id_docente);

        $sesionesPorDocenteDia = $sesiones->groupBy(fn ($s) => $s->asignacion->id_docente . '-' . $s->dia);

        foreach ($sesionesPorDocenteDia as $grupo) {
            $lista = $grupo->values();
            for ($i = 0; $i < $lista->count(); $i++) {
                for ($j = $i + 1; $j < $lista->count(); $j++) {
                    $s1 = $lista[$i];
                    $s2 = $lista[$j];

                    $seCruzan = $s1->hora_inicio->format('H:i') < $s2->hora_fin->format('H:i')
                        && $s2->hora_inicio->format('H:i') < $s1->hora_fin->format('H:i');

                    if ($seCruzan) {
                        $conflictos[] = [
                            'id' => 'horario-' . $s1->id . '-' . $s2->id,
                            'seccion' => $s1->asignacion->seccion->codigo_materia ?? '—',
                            'docente' => $s1->asignacion->docente->nombre_completo ?? 'Sin asignar',
                            'aula' => '—',
                            'tipo' => 'Conflicto de horario docente ('
                                . ($s2->asignacion->seccion->codigo_materia ?? '—') . ' el mismo día)',
                            'prioridad' => 'ALTA',
                            'estado' => 'Pendiente',
                            'asignacion_id' => $s1->id_asignacion,
                        ];
                    }
                }
            }
        }

        // ---- Actividad reciente (últimas asignaciones creadas o movidas) --
        $actividadReciente = Asignacion::with(['seccion', 'aula'])
            ->orderByDesc('updated_at')
            ->limit(8)
            ->get()
            ->map(function ($a) {
                $esNueva = $a->created_at && $a->updated_at && $a->created_at->equalTo($a->updated_at);

                $descripcion = $a->aula
                    ? ($a->seccion->codigo_materia ?? 'Sección') . ' → ' . $a->aula->nombre
                        . ($esNueva ? ' asignada' : ' actualizada')
                    : ($a->seccion->codigo_materia ?? 'Sección') . ' quedó sin aula';

                return [
                    'descripcion' => $descripcion,
                    'fecha' => optional($a->updated_at)->toIso8601String(),
                ];
            });

        return response()->json([
            'periodo_activo' => $periodoActivo ? [
                'id' => $periodoActivo->id,
                'nombre' => $periodoActivo->nombre,
            ] : null,
            'secciones' => [
                'total' => $totalSecciones,
                'asignadas' => $seccionesAsignadas,
                'pendientes' => $seccionesPendientes,
            ],
            'aulas' => [
                'total' => $totalAulas,
                'disponibles' => $aulasDisponibles,
                'en_uso' => $aulasEnUso,
                'mantenimiento' => $aulasMantenimiento,
            ],
            'docentes' => [
                'total' => $totalDocentes,
                'activos' => $docentesActivos,
                'sin_asignar' => $docentesSinAsignar,
            ],
            'conflictos' => array_values($conflictos),
            'actividad_reciente' => $actividadReciente,
        ]);
    }
}